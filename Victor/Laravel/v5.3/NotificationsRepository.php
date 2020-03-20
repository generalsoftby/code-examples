<?php

namespace App\Repositories\Notifications;

use App\Models\Attachment;
use App\Models\Buyer;
use App\Models\Company;
use App\Models\HelpdeskTopic;
use App\Models\Language;
use App\Models\Notification;
use App\Models\NotificationDescription;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;
use App\Models\NotificationSendingCondition;
use App\Models\Order;
use App\Models\Showcase;
use App\Repositories\Notifications\Messages\NotificationMessageStatusEnum;
use App\Repositories\Order\OrderStatusesEnum;
use App\Repositories\Payment\PaymentStatusesEnum;
use App\Services\Notification\DataComponents\DataComponentTypesEnum;
use App\Services\Notification\NotificationEventCustomInterface;
use App\Services\Notification\NotificationEventInterface;
use Illuminate\Support\Collection;
use Validator;

class NotificationsRepository
{
    /**
     * @param Showcase $showcase
     * @param null     $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|Notification[]
     */
    public function getShowcaseNotifications(Showcase $showcase, $type = null)
    {
        $notifyQuery = Notification::query();

        $notifyQuery
            ->where('showcase_id', $showcase->id)
            ->where('recipient_user_type', NotificationRecipientUserTypesEnum::CUSTOMERS)
        ;

        if ($type && in_array($type, NotificationTypesEnum::getAll())) {
            $notifyQuery->where('type', $type);
        }

        return $notifyQuery->get();
    }

    /**
     * @param Company $company
     * @param null    $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|Notification[]
     */
    public function getCompanyNotifications(Company $company, $type = null)
    {
        $notifyQuery = Notification::query();

        $notifyQuery
            ->where('company_id', $company->id)
            ->where('recipient_user_type', NotificationRecipientUserTypesEnum::STAFF)
        ;

        if ($type && in_array($type, NotificationTypesEnum::getAll())) {
            $notifyQuery->where('type', $type);
        }

        return $notifyQuery->get();
    }

    /**
     * @param $data
     * @param $recipient_user_type
     * @param $type
     * @param $channel_type
     *
     * @return \Illuminate\Validation\Validator
     */
    public function validateNotificationData($data, $recipient_user_type, $type, $channel_type)
    {
        $rules =
        [
            'title' => 'required',
        ];

        if (false == array_get($data, 'locked', false)) {
            switch ($recipient_user_type) {
                case NotificationRecipientUserTypesEnum::STAFF:
                    $rules['recipient_types'] = 'required_unless:event,company_admin_registration_invitation|array';

                    break;
                case NotificationRecipientUserTypesEnum::CUSTOMERS:
                    $rules['recipient_type'] = 'required|in:' . implode(',', NotificationRecipientTypesEnum::getBuyerTypes());

                    break;
            }

            switch ($type) {
                case NotificationTypesEnum::SYSTEM:
                    $rules['event'] = 'required|in:' . implode(',', NotificationEventsEnum::getAll()); // TODO
                    break;
                case NotificationTypesEnum::CUSTOM:
                    $conditionSpecialStatuses = NotificationConditionSpecialStatusesEnum::getAll();

                    $rules +=
                        [
                            'sending-conditions:order-status' => 'required|in:' . implode(',', OrderStatusesEnum::getAll() + $conditionSpecialStatuses),
                            'sending-conditions:order-payment-status' => 'required|in:' . implode(',', PaymentStatusesEnum::getAll() + $conditionSpecialStatuses),
                            'sending-conditions:topic-status' => 'required|in:' . implode(',', HelpdeskTopic::getStatuses() + $conditionSpecialStatuses),
                            'sending-conditions:production-status' => 'required',
                        ];

                    break;
            }
        }

        switch ($channel_type) {
            case NotificationChannelTypesEnum::SMS:
                $rules['content'] = 'required';

                break;
            case NotificationChannelTypesEnum::EMAIL:
                $rules['email_subject'] = 'required';
                $rules['content'] = 'required';

                if (NotificationTypesEnum::CUSTOM == $type) {
                    $rules['attachment_type'] = 'required|in:' . implode(',', NotificationAttachmentTypeEnum::getAll());
                }

                break;
        }

        return Validator::make($data, $rules);
    }

    /**
     * @param Notification $notification
     * @param array        $data
     * @param Language     $language
     *
     * @return Notification
     */
    public function updateNotification(Notification $notification, array $data, Language $language)
    {
        $isNew = is_null($notification->id);

        $notification->title = array_get($data, 'title');

        $notification->attachment_type =
            (NotificationChannelTypesEnum::EMAIL == $notification->channel_type && NotificationTypesEnum::CUSTOM == $notification->type) ?
            array_get($data, 'attachment_type') :
            NotificationAttachmentTypeEnum::NOT;

        if (NotificationTypesEnum::CUSTOM == $notification->type) {
            $notification->use_delay = array_has($data, 'sending-conditions:use-delay');
            $notification->delay = array_get($data, 'sending-conditions:delay', 0) ?: 0;
        } elseif (false == $notification->locked) {
            $notification->event = array_get($data, 'event');
        }

        return \DB::transaction(function () use ($isNew, &$notification, $data, $language) {
            $notification->save();

            /**
             * Update NotificationDescription.
             */
            $notificationDescription = $notification
                ->descriptionsL10n()
                ->where('language_id', $language->id)
                ->first()
                ;

            if (is_null($notificationDescription)) {
                $notificationDescription = new NotificationDescription();
                $notificationDescription->notification_id = $notification->id;
                $notificationDescription->language_id = $language->id;
            }

            $notificationDescription->email_subject =
                    (NotificationChannelTypesEnum::EMAIL == $notification->channel_type) ?
                        array_get($data, 'email_subject') :
                        null;

            $notificationDescription->content = array_get($data, 'content', '');

            $notificationDescription->save();

            /*
             * Update NotificationRecipient
             */
            if (false == $notification->locked || $isNew) {
                $recipients =
                    (NotificationRecipientUserTypesEnum::STAFF == $notification->recipient_user_type) ?
                    array_get($data, 'recipient_types', []) :
                    [array_get($data, 'recipient_type')];

                $notification->recipients->each(function (NotificationRecipient $recipientItem) {
                    $recipientItem->delete();
                });

                foreach ($recipients as $recipientItem) {
                    $recipient = new NotificationRecipient();
                    $recipient->notification()->associate($notification);
                    $recipient->parseString($recipientItem);
                    $recipient->save();
                }
            }

            /*
             * Update NotificationSendingCondition
             */
            if (false == $notification->locked || $isNew) {
                if (NotificationTypesEnum::CUSTOM == $notification->type) {
                    $sendingCondition = $notification->sendingCondition;

                    if (is_null($sendingCondition)) {
                        $sendingCondition = new NotificationSendingCondition();
                        $sendingCondition->notification()->associate($notification);
                    }

                    $sendingCondition->order_status = array_get($data, 'sending-conditions:order-status', NotificationConditionSpecialStatusesEnum::STATUS_NOT);
                    $sendingCondition->order_payment_status = array_get($data, 'sending-conditions:order-payment-status', NotificationConditionSpecialStatusesEnum::STATUS_NOT);
                    $sendingCondition->topic_status = array_get($data, 'sending-conditions:topic-status', NotificationConditionSpecialStatusesEnum::STATUS_NOT);
                    $sendingCondition->production_status = array_get($data, 'sending-conditions:production-status', NotificationConditionSpecialStatusesEnum::STATUS_NOT);

                    $sendingCondition->save();
                }
            }

            return $notification;
        });
    }

    /**
     * @param Notification $notification
     *
     * @return mixed
     */
    public function deleteNotification(Notification $notification)
    {
        return \DB::transaction(function () use ($notification) {
            $notification->descriptionsL10n->each(function (NotificationDescription $description) {
                $description->delete();
            });

            $notification->recipients->each(function (NotificationRecipient $recipient) {
                $recipient->delete();
            });

            if ($sendingCondition = $notification->sendingCondition()) {
                $sendingCondition->delete();
            }

            $notification->delete();
        });
    }

    /**
     * @param $user
     * @param $destination
     * @param $title
     * @param $content
     * @param Notification    $notification
     * @param null|Collection $attachments
     *
     * @return NotificationMessage
     *
     * @internal param $address
     */
    public function createNotificationMessage($user, $destination, $title, $content, Notification $notification = null, Collection $attachments = null): NotificationMessage
    {
        $notificationMessage = new NotificationMessage();

        if ($notification) {
            $notificationMessage->notification()->associate($notification);
        }

        if (is_null($destination) && $user) {
            $destination = (object)
            [
                'channel_type' => $notification->channel_type,
                'address' => (NotificationChannelTypesEnum::EMAIL == $notification->channel_type) ? $user->email : $user->phone,
            ];
        }

        if ($user) {
            $notificationMessage->user()->associate($user);
        } else {
            $notificationMessage->user_type = Buyer::class;
        }

        $notificationMessage->title = $title;
        $notificationMessage->content = $content;
        $notificationMessage->channel = $destination->channel_type;
        $notificationMessage->address = $destination->address;
        $notificationMessage->status = NotificationMessageStatusEnum::NOT_SENT;
        $notificationMessage->save();

        if ($attachments) {
            $attachments->each(function (Attachment $attachment) use (&$notificationMessage) {
                $notificationMessage->attachments()->attach($attachment->id);
            });
        }

        return $notificationMessage;
    }

    /**
     * @param NotificationEventInterface $event
     * @param string                     $notificationRecipientUserType
     *
     * @return Collection
     *
     * @throws \Exception
     */
    public function getActiveNotificationsByEvent(NotificationEventInterface $event, string $notificationRecipientUserType): Collection
    {
        $baseQuery = Notification::query()
            ->where('enabled', true)
            ->where('type', $event instanceof NotificationEventCustomInterface ? NotificationTypesEnum::CUSTOM : NotificationTypesEnum::SYSTEM)
        ;

        if ($event instanceof NotificationEventCustomInterface) {
            $baseQuery
                ->whereNull('event')
                ->whereHas('sendingCondition', function ($query) use ($event) {
                    switch ($event->getConditionType()) {
                    case NotificationCustomConditionTypeEnum::CHANGED_ORDER_STATUS:
                        $query->whereIn(
                            'order_status',
                            [
                                NotificationConditionSpecialStatusesEnum::STATUS_ALL,
                                $event->getDataComponentsContainer()->get(DataComponentTypesEnum::ORDER_COMPONENT)->getOrder()->status,
                            ]
                        );

                        break;
                    case NotificationCustomConditionTypeEnum::CHANGED_ORDER_PAYMENT_STATUS:
                        $query->whereIn(
                            'order_payment_status',
                            [
                                NotificationConditionSpecialStatusesEnum::STATUS_ALL,
                                $event->getDataComponentsContainer()->get(DataComponentTypesEnum::ORDER_COMPONENT)->getOrder()->payment_status,
                            ]
                        );

                        break;
                    case NotificationCustomConditionTypeEnum::CHANGED_TOPIC_STATUS:
                        $query->whereIn(
                            'topic_status',
                            [
                                NotificationConditionSpecialStatusesEnum::STATUS_ALL,
                                $event->getDataComponentsContainer()->get(DataComponentTypesEnum::HELPDESK_TOPIC_COMPONENT)->getHelpdeskTopic()->status,
                            ]
                        );

                        break;
                    case NotificationCustomConditionTypeEnum::CHANGED_PRODUCTION_STATUS:
                        //TODO
                        break;
                    default:
                        throw new \Exception('Not supported condition type: ' . $event->getConditionType());
                }
                })
            ;
        } else {
            $baseQuery->where('event', $event->getEvent());
        }

        switch ($notificationRecipientUserType) {
            case NotificationRecipientUserTypesEnum::CUSTOMERS:
                $baseQuery
                    ->where('recipient_user_type', NotificationRecipientUserTypesEnum::CUSTOMERS)
                    ->where('showcase_id', $event->getShowcase()->id)
                ;

                break;
            case NotificationRecipientUserTypesEnum::STAFF:
                $baseQuery
                    ->where('recipient_user_type', NotificationRecipientUserTypesEnum::STAFF)
                    ->where('company_id', $event->getCompany()->id)
                ;

                break;
            default:
                throw new \Exception('Unsupported NotificationRecipientUserType ' . $notificationRecipientUserType);
        }

        return $event->filterInappropriateNotifications($baseQuery->get());
    }

    /**
     * @param Order $order
     *
     * @return Collection
     */
    public function getDestinationsByOrder(Order $order): Collection
    {
        $emailDestinations = collect([
            $order->user->email, $order->delivery_data ? array_get($order->delivery_data, 'email') : null,
            $order->payment_data ? array_get($order->payment_data, 'email') : null,
        ])
            ->filter()
            ->unique()
            ->map(function ($address) {
                return (object)
                [
                    'channel_type' => NotificationChannelTypesEnum::EMAIL,
                    'address' => $address,
                ];
            })
        ;

        $smsDestinations = collect([
            $order->user->phone, $order->delivery_data ? array_get($order->delivery_data, 'phone') : null,
            $order->payment_data ? array_get($order->payment_data, 'phone') : null,
        ])
            ->filter()
            ->unique()
            ->map(function ($address) {
                return (object)
                [
                    'channel_type' => NotificationChannelTypesEnum::SMS,
                    'address' => $address,
                ];
            })
        ;

        return collect()
            ->merge($emailDestinations)
            ->merge($smsDestinations)
        ;
    }
}
