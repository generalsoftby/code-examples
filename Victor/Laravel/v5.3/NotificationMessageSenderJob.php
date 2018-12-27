<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\NotificationMessage;
use App\Models\Showcase;
use App\Repositories\Notifications\Messages\NotificationMessageStatusEnum;
use App\Repositories\Notifications\NotificationChannelTypesEnum;
use App\Repositories\Notifications\NotificationRecipientUserTypesEnum;
use App\Services\EmailService;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationMessageSenderJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var NotificationMessage */
    protected $notificationMessage;

    /**
     * Create a new job instance.
     *
     * @param NotificationMessage $notificationMessage
     */
    public function __construct(NotificationMessage $notificationMessage)
    {
        $this->notificationMessage = $notificationMessage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($notification = $this->notificationMessage->notification)
        {
            $configSource = $notification->recipient_user_type == NotificationRecipientUserTypesEnum::CUSTOMERS ?
                $notification->showcase :
                $notification->company;
        } else
        {
            $user = $this->notificationMessage->user;
            $configSource = $user instanceof Admin ?
                $user->company :
                $user->showcase;
        }


        $attachments = $this->notificationMessage->attachments()->get();

        if( ! $this->notificationMessage->address)
        {
            return;
        }

        try
        {
            switch ($this->notificationMessage->channel)
            {
                case NotificationChannelTypesEnum::EMAIL:

                    logger("Попытка отправить письмо",
                        [
                            "email" => $this->notificationMessage->address,
                            "content" => $this->notificationMessage->content,
                            "subject" => $this->notificationMessage->title,
                            "recipient->name" => $this->notificationMessage->user->name ?? '',
                        ]
                    );

                    app(EmailService::class)
                        ->{ $configSource instanceof Showcase ? "setShowcase" : "setCompany" }($configSource)
                        ->send(
                            $this->notificationMessage->address,
                            $this->notificationMessage->content,
                            $this->notificationMessage->title,
                            $this->notificationMessage->user->name ?? '',
                            $attachments
                        );
                    break;

                case NotificationChannelTypesEnum::SMS:
                    logger("Попытка отправить sms",
                        [
                            "phone" => $this->notificationMessage->address,
                            "content" => $this->notificationMessage->content,
                        ]
                    );

                    app(SmsService::class)
                        ->{ $configSource instanceof Showcase ? "setShowcase" : "setCompany" }($configSource)
                        ->send($this->notificationMessage->address, $this->notificationMessage->content);
                    break;

                default:
                    throw new \Exception("Unsupported channel type " . $this->notificationMessage->channel);
            }

            $this->notificationMessage->status = NotificationMessageStatusEnum::SENT;
            $this->notificationMessage->save();

        } catch (\Exception $e)
        {
            \Log::error($e);
        }
    }
}
