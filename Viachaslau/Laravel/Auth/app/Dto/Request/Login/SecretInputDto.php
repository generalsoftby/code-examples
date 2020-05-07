<?php


namespace App\Dto\Request\Login;


class SecretInputDto
{

    /**
     * @var string
     */
    protected $password;

    /**
     * SecretInputDto constructor.
     * @param $password
     */
    public function __construct($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

}