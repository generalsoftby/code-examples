<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

/**
 * Throws the exception when an invalid ValueKeeper is used and debugging is enabled.
 */
trait ThrowingExceptionOfInvalidValueKeeper
{
    /**
     * Throws the exception when the given ValueKeeper is invalid.
     *
     * @param  ValueKeeper    $keeper
     * @param  array|string[] $validClasses
     * @return void
     *
     * @throws InvalidValueKeeperException
     */
    protected function throwInvalidValueKeeper(ValueKeeper $keeper, array $validClasses = [])
    {
        if (isset($this->throwExceptions) && $this->throwExceptions) {
            throw new InvalidValueKeeperException($keeper, $validClasses);
        }
    }
}
