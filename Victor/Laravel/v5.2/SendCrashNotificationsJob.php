<?php

namespace App\Jobs;

use App\Classes\Enums\Mailing\MailingMessageChannelsEnum;
use App\Jobs\Job;
use App\Models\Crash;
use App\Models\MailingMessage;
use App\Models\Request;
use App\Models\User;
use App\Models\Worker;
use App\Repositories\MailingRepository;
use App\Services\SmsService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationSubscriptionTypesEnum;

class SendCrashNotificationsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var Crash */
    protected $crash;

    /** @var MailingRepository */
    protected $mailingRepository;

    /** @var SmsService */
    protected $smsService;

    /**
     * Create a new job instance.
     *
     * @param Crash $crash
     */
    public function __construct(Crash $crash)
    {
        $this->crash = $crash;
        $this->mailingRepository = app(MailingRepository::class);
        $this->smsService = app(SmsService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->crash->notified_at = \Carbon\Carbon::now();
        $this->crash->save();

        $crashedRequests = $this->crash->requests;

        $workers = collect();
        $operators = collect();

        $crashedRequests->each(function (Request $request) use(&$workers, &$operators)
        {
            $subscribedWorkers =  $request
                ->workers()
                ->whereHas('subscriptions', function ($query)
                {
                    $query->where('subscription_type', NotificationSubscriptionTypesEnum::CRASH_NOTIFICATIONS);
                })
                ->whereNotNull('phone')
                ->get();

            $workers = $workers->merge($subscribedWorkers);

            if(env('SEND_CRASH_NOTIFICATION_FOR_OPERATORS', false))
            {
                /** @var User $requestOperator */
                $requestOperator = $request->operator;
                if(
                    $requestOperator &&
                    $requestOperator->isSubscribedTo(NotificationSubscriptionTypesEnum::CRASH_NOTIFICATIONS) &&
                    !is_null($requestOperator->phone)
                )
                {
                    $operators->push($requestOperator);
                }
            }
        });

        $recipients = collect()
            ->merge($workers)
            ->merge($operators)
            ->unique(function ($recipient)
            {
                return sprintf("%s:%d", class_basename($recipient), $recipient->id);
            });

        $messages = collect();

        $messageContent = trans('mailings.crash.message',
            [
                'street' => title_case($this->crash->addr_street),
                'home' => $this->crash->addr_home,
            ]);

        $recipients
            ->each(function ($recipient) use(&$messages, $messageContent)
            {
                $message = $this->mailingRepository
                    ->createMailingMessage(
                        $messageContent,
                        $recipient,
                        MailingMessageChannelsEnum::SMS,
                        $recipient->phone,
                        null,
                        null,
                        true
                    );

                $messages->push($message);
            });

        $messages->each(function (MailingMessage $message)
        {
            $this->smsService->sendMailingMessage($message);
        });
    }

}
