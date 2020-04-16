<?php

abstract class AbstractAction
{
    protected $session;

    protected $db;

    public function __construct(Session $session, DB $db)
    {
        $this->session = $session;
        $this->db = $db;
    }

    abstract public function execute(): array;
}
