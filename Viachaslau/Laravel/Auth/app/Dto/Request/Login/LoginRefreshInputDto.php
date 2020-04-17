<?php

namespace App\Dto\Request\Login;

use App\Dto\Base\ValidateDto;
use App\Annotations\Dto\Request\Password;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LoginRefreshInputDto
 * @package App\Dto\Request\Login
 *
 * @property string $password
 * @property string $token
 */
class LoginRefreshInputDto extends ValidateDto
{
    /**
     * @Assert\NotBlank
     */
    protected $token;

    /**
     * @Assert\NotBlank
     * @Password
     */
    protected $password;

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
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
