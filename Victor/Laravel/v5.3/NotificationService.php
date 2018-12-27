<?php

namespace App\Services\Notification;

use App\Jobs\NotificationMessageBuilderJob;
use App\Models\Admin;
use App\Models\HelpdeskComment;
use App\Models\HelpdeskTopic;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Repositories\Notifications\NotificationChannelTypesEnum;
use App\Repositories\Notifications\NotificationEventsEnum;
use App\Repositories\Notifications\NotificationRecipientTypesEnum;
use App\Repositories\Notifications\NotificationRecipientUserTypesEnum;
use App\Repositories\Notifications\NotificationsRepository;
use App\Services\Notification\DataComponents\Components\HelpdeskCommentDataComponent;
use App\Services\Notification\DataComponents\DataComponentTypesEnum;
use App\Services\Notification\Events\AbstractNotificationEvent;
use Illuminate\Support\Collection;

class NotificationService
{
    /** @var NotificationsRepository */
    protected $notificationsRepository;

    /**
     * NotificationService constructor.
     * @param NotificationsRepository $notificationsRepository
     */
    public function __construct(NotificationsRepository $notificationsRepository)
    {
        $this->notificationsRepository = $notificationsRepository;
    }

    /**
     * @param NotificationEventInterface $event
     */
    public function emitEvent(NotificationEventInterface $event)
    {
        if(config('app.seeder_running'))
        {
            return;
        }

        $customerNotifications = $event->isAcceptableFor(NotificationRecipientUserTypesEnum::CUSTOMERS) ?
            $this
                ->notificationsRepository
                ->getActiveNotificationsByEvent($event, NotificationRecipientUserTypesEnum::CUSTOMERS) :
            collect();

        $staffNotifications = $event->isAcceptableFor(NotificationRecipientUserTypesEnum::STAFF) ?
            $this
                ->notificationsRepository
                ->getActiveNotificationsByEvent($event, NotificationRecipientUserTypesEnum::STAFF) :
            collect();

        $systemNotifications = collect()
            ->merge($customerNotifications)
            ->merge($staffNotifications);

        $systemNotifications->each(function (Notification $notification) use($event)
        {
            $job = new NotificationMessageBuilderJob($event, $notification);

            if($event instanceof NotificationEventCustomInterface && $notification->use_delay)
            {
                $job->delay(\Carbon\Carbon::now()->addMinutes($notification->delay));
            }

            dispatch($job);
        });
    }

    /**
     * @param AbstractNotificationEvent $event
     * @param Notification $notification
     * @return \Illuminate\Support\Collection
     */
    public function getNotificationRecipients(AbstractNotificationEvent $event, Notification $notification)
    {
        $recipientTypes = $notification->recipients;

        $recipients = collect();

        if ($event->getEvent() === NotificationEventsEnum::MESSAGE_VIA_EMAIL_FROM_HELPDESK) {
            if ($notification->channel_type !== NotificationChannelTypesEnum::EMAIL) {
                return collect();
            }

            /** @var HelpdeskCommentDataComponent $commentComponent */
            $commentComponent = $event->getDataComponentsContainer()->get(DataComponentTypesEnum::HELPDESK_COMMENT_COMPONENT);
            $comment = $commentComponent->getHelpdeskComment();

            if (!$comment->topic->email_user_id) {
                return collect();
            }

            $recipient = (object)[
                'user' => $comment->topic->owner,
                'channel_type' => $notification->channel_type,
                'address' => $comment->topic->email_user_id,
            ];

            $recipients->push($recipient);

            return empty($recipient->address) ? collect() : $recipients;
        }

        if($event->getEvent() == NotificationEventsEnum::COMPANY_ADMIN_REGISTRATION_INVITATION)
        {
            $admin = $event->getDataComponentsContainer()->get(DataComponentTypesEnum::ADMIN_COMPONENT)->getAdmin();

            $recipient = (object)
            [
                'user' => $admin,
                'channel_type' => $notification->channel_type,
                'address' => $notification->channel_type == NotificationChannelTypesEnum::EMAIL ?
                    $admin->email : $admin->phone
            ];

            $recipients->push($recipient);

            return empty($recipient->address) ? collect() : $recipients;
        }

        $recipientTypes->each(function (NotificationRecipient $notificationRecipient) use(&$recipients, $event, $notification)
        {
            switch ($notificationRecipient->type)
            {
                case NotificationRecipientTypesEnum::BUYER_ORDER_RECIPIENT:
                    if(
                        $notification->recipient_user_type == NotificationRecipientUserTypesEnum::CUSTOMERS &&
                        $event->getDataComponentsContainer()->has(DataComponentTypesEnum::ORDER_COMPONENT)
                    )
                    {
                        $order = $event
                            ->getDataComponentsContainer()
                            ->get(DataComponentTypesEnum::ORDER_COMPONENT)
                            ->getOrder();

                        $recipient = (object)
                        [
                            'user' => $order->user,
                            'channel_type' => $notification->channel_type,
                            'address' => $notification->channel_type == NotificationChannelTypesEnum::EMAIL ?
                                array_get($order->delivery_data, 'email') : array_get($order->delivery_data, 'phone')
                        ];

                        $recipients->push($recipient);
                    }
                    break;

                case NotificationRecipientTypesEnum::BUYER_USER:
                    if(
                        $notification->recipient_user_type == NotificationRecipientUserTypesEnum::CUSTOMERS &&
                        $event->getDataComponentsContainer()->has(DataComponentTypesEnum::BUYER_COMPONENT)
                    )
                    {
                        $buyer = $event
                            ->getDataComponentsContainer()
                            ->get(DataComponentTypesEnum::BUYER_COMPONENT)
                            ->getBuyer();

                        $recipient = (object)
                        [
                            'user' => $buyer,
                            'channel_type' => $notification->channel_type,
                            'address' => $notification->channel_type == NotificationChannelTypesEnum::EMAIL ?
                                $buyer->email : $buyer->phone
                        ];

                        $recipients->push($recipient);
                    }
                    break;

                case NotificationRecipientTypesEnum::ADMIN_FOR_ALL:
                    $company = $event->getCompany();

                    /** @var Collection $allCompanyAdmins */
                    $allCompanyAdmins = $company
                        ->admins()
                        ->get()
                        ->map(function (Admin $admin) use ($notification)
                        {
                            return (object)
                            [
                                'user' => $admin,
                                'channel_type' => $notification->channel_type,
                                'address' => $notification->channel_type == NotificationChannelTypesEnum::EMAIL ?
                                    $admin->email : $admin->phone
                            ];
                        });

                    $recipients = $recipients->merge($allCompanyAdmins);
                    break;

                case NotificationRecipientTypesEnum::ADMIN_ROLE:

                    $company = $event->getCompany();

                    $adminsWithRole = $company
                        ->admins()
                        ->where('role_id', $notificationRecipient->mixed_id)
                        ->get()
                        ->map(function (Admin $admin) use ($notification)
                        {
                            return (object)
                            [
                                'user' => $admin,
                                'channel_type' => $notification->channel_type,
                                'address' => $notification->channel_type == NotificationChannelTypesEnum::EMAIL ?
                                    $admin->email : $admin->phone
                            ];
                        });

                    $recipients = $recipients->merge($adminsWithRole);
                    break;

                case NotificationRecipientTypesEnum::ADMIN_SHOWCASE_EVENT:
                    if ($event->getEvent() === NotificationEventsEnum::REPLY_TO_TICKET) {
                        /** @var HelpdeskComment|null $comment */
                        $comment = $event->getDataComponentsContainer()->has(DataComponentTypesEnum::HELPDESK_COMMENT_COMPONENT) ?
                            $event->getDataComponentsContainer()->get(DataComponentTypesEnum::HELPDESK_COMMENT_COMPONENT)->getHelpdeskComment() :
                            null;

                        if ($comment) {
                            $adminsQuery = $comment->topic->followers()->getQuery();
                            if ($comment->user_type == Admin::class) {
                                $adminsQuery->where('admin_id', '!=', $comment->user_id);
                            }

                            $recipients = $recipients->merge($adminsQuery->get()->map(function (Admin $admin) use ($notification) {
                                return (object)[
                                    'user' => $admin,
                                    'channel_type' => $notification->channel_type,
                                    'address' => $notification->channel_type == NotificationChannelTypesEnum::EMAIL ?
                                        $admin->email : $admin->phone
                                ];
                            }));
                        }
                    } else if ($showcase = $event->getShowcase()) {
                        /** @var Collection $allCompanyAdmins */
                        $allShowcase = $showcase
                            ->admins()
                            ->get()
                            ->map(function (Admin $admin) use ($notification) {
                                return (object)[
                                    'user' => $admin,
                                    'channel_type' => $notification->channel_type,
                                    'address' => $notification->channel_type == NotificationChannelTypesEnum::EMAIL ?
                                        $admin->email : $admin->phone
                                ];
                            });

                        $recipients = $recipients->merge($allShowcase);
                    }
                    break;
            }

        });

        $uniqueRecipients = $recipients
            ->reject(function ($recipientItem)
            {
                return empty($recipientItem->address);
            })
            ->unique(function ($recipientItem)
            {
                return sprintf(
                    "%s:%d:%s:%s",
                    get_class($recipientItem->user),
                    $recipientItem->user->id,
                    $recipientItem->channel_type,
                    $recipientItem->address
                );
            });

        return $uniqueRecipients;
    }

}
