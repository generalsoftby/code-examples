<?php

namespace App\Manager\Chat;

use App\Enums\Messages\MessageTypesEnum;
use App\Manager\Chat\ChatManager;
use App\Manager\Notification\NotificationManager;
use App\Models\Ads;
use App\Models\Attachment;
use App\Models\Crossing\CrossingRequest;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ChatMessageUserNew;
use App\Events\MessageEvent;
use App\Models\User;

class ChatMessageManager
{

     /*
     * Сохраняет сообщение
     */
    public static function saveMessage($collectParams)
    {

        $chat = $collectParams->get('chat');
        if(!$chat) {
            $chat = ChatManager::getChatByUsers($collectParams->get('model'), [$collectParams->get('sender')->id, $collectParams->get('recipient')->id]);
            if (!$chat) {
                $chat = ChatManager::createChat($collectParams->get('model'), [$collectParams->get('sender')->id, $collectParams->get('recipient')->id]);
            }
        }

        $message = new ChatMessage();
        $message->chat_id = $chat->id;
        $message->sender()->associate($collectParams->get('sender'));
        $message->message = $collectParams->get('message');

        $message->save();

        $attachIds = $collectParams->get('attachIds', []);
        foreach($attachIds as $id){
            Attachment::where('id', $id)
                ->update([
                    'model_id' => $message->id,
                    'model_type' => $message->getMorphClass()
                ]);
        }

        $chat->touch();

        //для всех пользователей чата установить сообщение как "новое"
        foreach($chat->users as $user){
            if($user->id != $message->sender_id) {
                $chatMessageUserNew = new ChatMessageUserNew();
                $chatMessageUserNew->user_id = $user->id;
                $chatMessageUserNew->message_id = $message->id;
                $chatMessageUserNew->save();

                event(new MessageEvent($message, $user, true));

                $notification = NotificationManager::generateMessageNotification($message);
                if($notification) {
                    $user->notify($notification);
                }
            }
        }

        return $message;
    }

    /*
    * Проверяет новое ли сообщение для пользователя
    */
    public static function isNewMessage(User $user, ChatMessage $message)
    {
        $chatMessageUserNew = ChatMessageUserNew::where('user_id', $user->id)
            ->where('message_id', $message->id)->first();

        return $chatMessageUserNew ? true : false;
    }

    /*
    * Делает сообщение прочитанным
    */
    public static function readMessageUser(ChatMessageUserNew $chatMessageUserNew)
    {
        $message = $chatMessageUserNew->message;
        $user = $chatMessageUserNew->user;

        $chatMessageUserNew->delete();

        event(new MessageEvent($message, $user, false));

        return true;
    }

    // сопоставляет сущности
    public static function getModelType($model)
    {
        switch (get_class($model)) {
            case Ads::class:
                return MessageTypesEnum::ADS;
                break;
            case CrossingRequest::class:
                return MessageTypesEnum::CROSSING;
                break;
            default:
                return MessageTypesEnum::ADS;
                break;
        }
    }

}
