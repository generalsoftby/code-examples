<?php


namespace App\Dto\Request\Login;


class ExternalSystemInputDto
{

    /**
     * @var string
     */
    protected $externalSystem;

    /**
     * ExternalSystemInputDto constructor.
     * @param string $externalSystem
     */
    public function __construct($externalSystem)
    {
        $this->externalSystem = $externalSystem;
    }

    /**
     * @return string
     */
    public function getExternalSystem(): string
    {
        return $this->externalSystem;
    }

}