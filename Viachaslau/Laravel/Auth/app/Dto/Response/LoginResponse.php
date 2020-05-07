<?php

namespace App\Dto\Response;

class LoginResponse extends AbstractResponse
{

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $middleName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string|null
     */
    protected $redirectToRoute;

    /**
     * LoginResponse constructor.
     * @param string $token
     * @param string $firstName
     * @param string $middleName
     * @param string $lastName
     * @param null|string $redirectToRoute
     */
    public function __construct(string $token, string $firstName, string $middleName, string $lastName, $redirectToRoute = null)
    {
        $this->token = $token;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->redirectToRoute = $redirectToRoute;
    }

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
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return null|string
     */
    public function getRedirectToRoute(): ?string
    {
        return $this->redirectToRoute;
    }

    /**
     * @param null|string $redirectToRoute
     */
    public function setRedirectToRoute($redirectToRoute)
    {
        $this->redirectToRoute = $redirectToRoute;
    }

}
