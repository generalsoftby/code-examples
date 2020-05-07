<?php

namespace App\Exceptions\Repositories;

class NotFoundException extends \Exception
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
