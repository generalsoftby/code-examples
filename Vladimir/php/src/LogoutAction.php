<?php

class LogoutAction extends AbstractAction
{
    public function execute(): array
    {
        $this->session->destroy();

        return [
            'content' => 'OK',
            'status'  => 200,
        ];
    }
}
