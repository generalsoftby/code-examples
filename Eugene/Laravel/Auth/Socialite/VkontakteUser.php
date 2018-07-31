<?php

namespace App\Support\Socialite;

use Illuminate\Support\Arr;

class VkontakteUser extends AbstractSocialiteUser
{
    public function collectData($driver)
    {
        $user = $driver->user();
        $userInfo = $user->user;

        $data = [
            'email' => $user->email,
            'name' => Arr::get($userInfo, 'first_name', ''),
            'last_name' => Arr::get($userInfo, 'last_name', ''),
            'token' => $user->id
        ];

        return $data;
    }

}
