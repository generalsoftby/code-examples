<?php


namespace App\Managers\Core;


abstract class AbstractManager
{
    private static $managers = [];

    public function __construct()
    {

    }

    /**
     * @return static
     */
    public static final function getManager()
    {
        if (empty(self::$managers[static::class])){
            self::$managers[static::class] = app(static::class);
        }

        return self::$managers[static::class];
    }

    public static final function clearAllManagers(){
        self::$managers = [];
    }

}