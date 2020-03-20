<?php

namespace App\Support\Socialite;

use Illuminate\Support\Arr;

class GoogleUser extends AbstractSocialiteUser
{
    public function collectData($driver)
    {
        $user = $driver->user();

        $userInfo = $user->user;
        $nameInfo = Arr::get($userInfo, 'name', []);

        $data = [
            'email' => $user->email,
            'name' => Arr::get($nameInfo, 'givenName', ''),
            'last_name' => Arr::get($nameInfo, 'familyName', ''),
            'token' => $user->id
        ];

        return $data;
    }
}
