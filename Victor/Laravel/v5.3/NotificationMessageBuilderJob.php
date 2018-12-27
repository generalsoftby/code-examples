<?php

namespace App\Jobs;

use App\Classes\DocumentTemplateEnum;
use App\Models\Buyer;
use App\Models\Document;
use App\Models\HelpdeskComment;
use App\Models\Language;
use App\Models\Notification;
use App\Models\NotificationMessage;
use App\Models\Order;
use App\Repositories\Notifications\NotificationAttachmentTypeEnum;
use App\Repositories\Notifications\NotificationChannelTypesEnum;
use App\Repositories\Notifications\NotificationEventsEnum;
use App\Repositories\Notifications\NotificationsRepository;
use App\Repositories\Notifications\NotificationTypesEnum;
use App\Services\Notification\Events\AbstractNotificationEvent;
use App\Services\Notification\DataComponents\DataComponentsContainer;
use App\Services\Notification\DataComponents\DataComponentTypesEnum;
use App\Services\Notification\NotificationEventInterface;
use App\Services\Notification\NotificationService;
use App\Services\Notification\NotificationTemplateEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class NotificationMessageBuilderJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var AbstractNotificationEvent */
    protected $event;

    /** @var Notification */
    protected $notification;

    /** @var NotificationService */
    protected $notificationService;

    /** @var NotificationTemplateEngine */
    protected $notificationTemplateEngine;

    /** @var NotificationsRepository */
    protected $notificationsRepository;

    /**
     * Create a new job instance.
     *
     * @param NotificationEventInterface $event
     * @param Notification $notification
     */
    public function __construct(NotificationEventInterface $event, Notification $notification)
    {
        $this->event = $event;
        $this->notification = $notification;
        $this->notificationService = app(NotificationService::class);
        $this->notificationTemplateEngine = app(NotificationTemplateEngine::class);
        $this->notificationsRepository = app(NotificationsRepository::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notificationRecipients = $this->notificationService->getNotificationRecipients($this->event, $this->notification);

        if($notificationRecipients->count() == 0)
        {
            return;
        }

        $notificationRecipients->each(function ($recipientItem)
        {
            $notificationMessage = $this->getNotificationMessage($recipientItem);

            dispatch(new NotificationMessageSenderJob($notificationMessage));
        });
    }

    /**
     * @param $recipient
     * @return NotificationMessage
     */
    protected function getNotificationMessage($recipient) : NotificationMessage
    {
        $showcase = $this->event->getShowcase();

        /**
         * Default showcase language or English
         */
        $notificationLanguage = $showcase ? $showcase->defaultLanguage : Language::find(2);

        $templateDataSources = $this
            ->notificationTemplateEngine
            ->getNotificationTemplateDataSources($this->event, $this->notification, $recipient->user);

        $subject = $this->notification->channel_type == NotificationChannelTypesEnum::EMAIL ?
            $this->notificationTemplateEngine
                ->renderTemplate
                (
                    $this->notification->descrL10n('email_subject', $notificationLanguage->id),
                    $templateDataSources
                ) :
            null;

        $content = $this->notificationTemplateEngine
            ->renderTemplate
            (
                $this->notification->descrL10n('content', $notificationLanguage->id),
                $templateDataSources
            );

        $attachments = $this->notification->channel_type == NotificationChannelTypesEnum::EMAIL ?
            $this->getAttachedFiles($this->notification, $templateDataSources) :
            null;

        $destination = (object)
        [
            'channel_type' => $recipient->channel_type,
            'address' => $recipient->address,
        ];

        return $this
            ->notificationsRepository
            ->createNotificationMessage($recipient->user, $destination, $subject, $content, $this->notification, $attachments);
    }

    /**
     * @param Notification $notification
     * @param DataComponentsContainer $dataComponentsContainer
     * @return Collection|null
     * @throws \Exception
     */
    protected function getAttachedFiles(Notification $notification, DataComponentsContainer $dataComponentsContainer)
    {
        if ($notification->event === NotificationEventsEnum::MESSAGE_VIA_EMAIL_FROM_HELPDESK) {
            if( !$dataComponentsContainer->has(DataComponentTypesEnum::HELPDESK_COMMENT_COMPONENT))
            {
                return null;
            }

            /** @var HelpdeskComment $comment */
            $comment = $dataComponentsContainer->get(DataComponentTypesEnum::HELPDESK_COMMENT_COMPONENT)->getHelpdeskComment();

            return $comment->attachments;
        }

        if($notification->type != NotificationTypesEnum::CUSTOM)
        {
            return null;
        }

        switch ($notification->attachment_type)
        {
            case NotificationAttachmentTypeEnum::NOT:
                return null;

            case NotificationAttachmentTypeEnum::INVOICE:
                if( !$dataComponentsContainer->has(DataComponentTypesEnum::ORDER_COMPONENT))
                {
                    return null;
                }

                /** @var Order $order */
                $order = $dataComponentsContainer->get(DataComponentTypesEnum::ORDER_COMPONENT)->getOrder();

                $files = collect();

                $order->documents()
                    ->where('template', DocumentTemplateEnum::INVOICE)
                    ->get()
                    ->each(function (Document $document) use (&$files)
                    {
                        $files->push($document->attachment);
                    });

                return $files;

            case NotificationAttachmentTypeEnum::INVOICE_PROFORMA:
                if( !$dataComponentsContainer->has(DataComponentTypesEnum::ORDER_COMPONENT))
                {
                    return null;
                }

                /** @var Order $order */
                $order = $dataComponentsContainer->get(DataComponentTypesEnum::ORDER_COMPONENT)->getOrder();

                $files = collect();

                $order->documents()
                    ->where('template', DocumentTemplateEnum::PROFORMA)
                    ->get()
                    ->each(function (Document $document) use (&$files)
                    {
                        $files->push($document->attachment);
                    });

                return $files;

            default:
                throw new \Exception("Unexpected NotificationAttachmentTypeEnum value " . $notification->attachment_type);
        }
    }

}
