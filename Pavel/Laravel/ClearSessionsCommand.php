<?php

namespace App\Commands;

use Carbon\Carbon;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Session\SessionManager;

use Symfony\Component\Finder\Finder;

/**
 * Forced removal of old sessions.
 */
class ClearSessionsCommand extends Command implements SelfHandling {

    protected $name = 'session:clear';

    protected $sessionManager;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SessionManager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $session = $this->manager->driver();

        $handler = $session->getHandler();

        $time = array_get($this->manager->getSessionConfig(), 'lifetime') * 60;

        $handler->gc($time);
    }

}
