<?php

class LoginAction extends AbstractAction
{
    public function execute(): array
    {
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        if (!$username || !$password) {
            return [
                'content' => 'Enter the credentials',
                'status'  => 422,
            ];
        }

        $user = $this->db->getUserByUsername($username);

        if (!$user || md5($password) !== $user['password']) {
            return [
                'content' => 'Wrong credentials',
                'status'  => 422,
            ];
        }

        $this->session->logIn($username);

        return [
            'content' => 'OK',
            'status'  => 200,
        ];
    }
}
