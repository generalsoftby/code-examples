<?php

class NotFoundAction extends AbstractAction
{
    public function execute(): array
    {
        return [
            'content' => 'Not found',
            'status'  => 404,
        ];
    }
}
