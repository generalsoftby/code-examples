<?php

namespace App\Dto\Request\Login;

use App\Dto\Base\ValidateDto;
use App\Annotations\Dto\Request\Password;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LoginInputDto
 * @package App\Dto\Request\Login
 *
 * @property-write string $login
 * @property-write string $password
 */
class LoginInputDto extends ValidateDto
{
    /**
     * @Assert\NotBlank
     */
    protected $login;

    /**
     * @Assert\NotBlank
     * @Password
     */
    protected $password;

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return SecretInputDto
     */
    public function getSecret(): SecretInputDto
    {
        return new SecretInputDto($this->getPassword());
    }

}
