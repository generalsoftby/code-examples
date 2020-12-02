<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

/**
 * The interface to implement classes that keeps values for calculation of assemblies.
 */
interface ValueKeeper
{
    /**
     * Returns a number of products (it is the same as an edition size
     * for printed products).
     */
    public function getNumberOfProducts(): int;
}
