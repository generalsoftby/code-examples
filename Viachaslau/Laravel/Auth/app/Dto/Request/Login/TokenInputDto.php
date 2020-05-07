<?php

namespace App\Dto\Request\Login;

use App\Dto\Base\ValidateDto;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TemporaryTokenInputDto
 * @package App\Dto\Request
 *
 * @property string $token
 */
class TokenInputDto extends ValidateDto
{
    /**
     * @Assert\NotBlank
     */
    protected $token;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

}
