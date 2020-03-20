<?php

namespace App\Support\Socialite;

use Illuminate\Support\Arr;

class FacebookUser extends AbstractSocialiteUser
{
    public function collectData($driver)
    {
        $driver->fields([
            'name',
            'first_name',
            'last_name',
            'email',
            'gender',
            'verified'
        ]);

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
