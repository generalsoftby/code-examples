<?php

namespace App\Dto\Request;

use App\Dto\Base\ValidateDto;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package App\Dto\Request
 *
 * @property string $id
 */
class EntityIdInputDto extends ValidateDto
{
    /**
     * @Assert\NotBlank
     */
    protected $id;

    /**
     * EntityIdInputDto constructor.
     * @param string|null $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

}
