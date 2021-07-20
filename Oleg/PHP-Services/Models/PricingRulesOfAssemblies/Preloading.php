<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

/**
 * The interface to load data.
 */
interface Preloading
{
    /**
     * Loads necessary data.
     *
     * @return void
     */
    public function loadData(): void;
}
