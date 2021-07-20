<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\PrintedSheets\CustomEditionSize;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\PrintedSheets\PricingRuleOfPrintedSheets;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\PrintedSheets\Wholesale;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfSheet;

/**
 * The class of pricing rules for the 'printed sheets' type.
 */
class PrintedSheets extends AbstractPrintedSheets
{
    /**
     * A number of sheets for fitting.
     *
     * @var int
     */
    protected $numberOfSheetsForFitting;

    /**
     * Fills the instance from an array with rules.
     *
     * @param  array  $rules
     * @return void
     */
    public function fillFromArray(array $rules): void
    {
        parent::fillFromArray($rules);
        $this->numberOfSheetsForFitting = (int) ($rules['number_of_sheets_for_fitting'] ?? 0);
    }

    /**
     * Initializes a new rules by the given type with the given rules.
     *
     * @param  string $type
     * @param  array  $rules
     * @return PricingRuleOfPrintedSheets
     *
     * @throws UnsupportedPricingRuleException
     */
    public function newPricingRuleFromArray(string $type, array $rules): PricingRule
    {
        switch ($type) {
            case 'custom_edition_size':
                return new CustomEditionSize($rules);
            case 'wholesale':
                return new Wholesale($rules);
        }

        parent::newPricingRuleFromArray($type, $rules);
    }

    /**
     * Sets a number of sheets for fitting.
     *
     * @param  int $value
     * @return void
     */
    public function setNumberOfSheetsForFitting(int $value): void
    {
        $this->numberOfSheetsForFitting = $value;
    }

    /**
     * Returns a number of sheets for fitting.
     *
     * @return int
     */
    public function getNumberOfSheetsForFitting(): int
    {
        return $this->numberOfSheetsForFitting;
    }

    /**
     * Returns allowed types of ValueKeeper.
     *
     * @return array|string[]
     */
    public function getAllowedTypesOfValueKeeper(): array
    {
        return [ValuesOfSheet::class];
    }

    /**
     * Calculates a product price by input values.
     *
     * @param  ValueKeeper|ValuesOfSheet $values Values for calculation.
     * @return bool
     *
     * @throws InvalidValueKeeperException If $values is an unsupported class.
     */
    public function calculate(ValueKeeper $valueKeeper): bool
    {
        $this->resetCalculation();

        if (! ($valueKeeper instanceof ValuesOfSheet)) {
            return $this->invalidValueKeeper($valueKeeper);
        }

        $valueKeeper->setNumberOfSheetsForFitting($this->getNumberOfSheetsForFitting());

        return parent::calculate($valueKeeper);
    }

    /**
     * Returns an array with rules for sheets.
     *
     * @return array
     */
    public function toArray(): array
    {
        return parent::toArray() + [
            'number_of_sheets_for_fitting' => $this->numberOfSheetsForFitting,
        ];
    }
}
