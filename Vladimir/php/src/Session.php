<?php


class Session
{
    private $username;

    public function __construct()
    {
        if (!session_start()) {
            throw new \RuntimeException('Session cannot be start');
        }

        $this->username = $_SESSION['username'] ?? null;
    }

    public function isLoggedIn(): bool
    {
        return $this->username !== null;
    }

    public function logIn(string $username): void
    {
        $this->username = $username;
        $_SESSION['username'] = $username;
    }

    public function destroy(): void
    {
        session_unset();
        session_destroy();
    }
}
