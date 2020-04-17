<?php

namespace App\Dto\Base;

use App\Exceptions\ValidateException;
use App\Managers\Core\DtoManager;
use App\Managers\ValidatorManager;

class ValidateDto
{
    /**
     * @param $name
     * @param $value
     * @throws ValidateException
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
        $errors = DtoManager::getManager()->getValidator()->validateProperty($this, $name);

        if ($errors->count()) {
            throw new ValidateException($errors->get(0)->getMessage(), $name);
        }
    }
}
