<?php

namespace App\Exceptions;

class ValidateException extends \Exception
{
    private $field;

    /**
     * ValidateException constructor.
     * @param string $message
     * @param string $field
     */
    public function __construct($message = "", $field = "")
    {
        parent::__construct($field . ':' . $message);
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }
}
