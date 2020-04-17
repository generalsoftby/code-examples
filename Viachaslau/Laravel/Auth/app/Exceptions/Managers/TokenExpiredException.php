<?php

namespace App\Exceptions\Managers;

class TokenExpiredException extends \Exception
{
    /**
     * NotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}
