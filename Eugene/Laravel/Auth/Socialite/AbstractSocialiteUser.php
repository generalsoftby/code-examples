<?php

namespace App\Support\Socialite;

abstract class AbstractSocialiteUser
{
    public abstract function collectData($driver);
}