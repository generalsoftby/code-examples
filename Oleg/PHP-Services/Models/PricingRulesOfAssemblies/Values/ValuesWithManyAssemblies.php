<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Values;

use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;

/**
 * It is used with ValueKeeper whose blocks have more that one assembly.
 */
interface ValuesWithManyAssemblies extends ValueKeeper
{
    /**
     * Sets the given type of a block.
     *
     * @return void
     */
    public function setTypeOfBlock(string $type): void;

    /**
     * Returns the current type of a block.
     *
     * @return string|null
     */
    public function getTypeOfBlock(): ?string;

    /**
     * Checks whether the block use an assembly.
     *
     * @param  string $blockName
     * @return bool
     */
    public function doesUserBlockUseAssembly(string $blockName): bool;
}
