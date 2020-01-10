<?php

namespace App\Manager\Chat;

use App\Models\User;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatMessageUserNew;
use Illuminate\Database\Eloquent\Builder;

class ChatManager
{

    /*
    * Создает чат
    */
    public static function createChat($model, array $userIds = [])
    {
        $chat = new Chat();
        $chat->model_type = get_class($model);
        $chat->model_id = $model->id;
        $chat->save();

        $chat->users()->sync($userIds);

        return $chat;
    }

    /*
    * Возвращает чаты пользователя по сущности
    */
    public static function getChatsUser($model, User $user)
    {
        $chats = Chat::query()
            ->where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->whereHas('users', function (Builder $query) use ($user) {
                $query->where('id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return $chats;
    }

    /*
    * Возвращает чат в котором состоят пользователи
    */
    public static function getChatByUsers($model, array $userIds = [])
    {
        $chat = null;
        $chats = Chat::query()
            ->whereHas('users', function (Builder $query) use ($userIds) {
                $query->whereIn('id', $userIds);
            })
            ->where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->get();

        foreach($chats as $ch){
            $chUserIds = $ch->users->pluck('id')->toArray();
            if(count($chUserIds) == count($userIds) && count($userIds) == count(array_intersect($chUserIds ,$userIds)) ){
                $chat = $ch;
                break;
            }
        }

        return $chat;
    }


    /*
    * Новых сообщений у пользователя (если пришел чат, то только по чату)
    */
    public static function getCountNewMessage(User $user, $chat = null)
    {
        $query = ChatMessageUserNew::query()
            ->where('user_id', $user->id);
        if($chat){
            $query->whereIn('message_id', $chat->messages->pluck('id')->toArray());
        }
        $count = $query->count();

        return $count;
    }

    /*
    * Удаляет чат
    */
    public static function deleteChat(Chat $chat)
    {
        $chat->delete();

        return true;
    }

    /*
    * Заблокирован ли пользователь в чате
    */
    public static function isBlockUserChat(Chat $chat, User $user)
    {
        $userBlock = $chat->blockUsers()->where('id', $user->id)->first();

        return $userBlock ? true : false;
    }


}
