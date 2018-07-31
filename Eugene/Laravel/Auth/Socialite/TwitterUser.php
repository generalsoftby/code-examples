<?php

namespace App\Support\Socialite;

class TwitterUser extends AbstractSocialiteUser
{

    public function collectData($driver)
    {
        $user = $driver->user();

        $data = [
            'email' => $user->email,
            'token' => $user->id
        ];

        return $data;
    }
}