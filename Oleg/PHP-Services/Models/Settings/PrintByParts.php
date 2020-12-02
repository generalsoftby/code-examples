<?php

namespace App\Model\Calculator\Settings;

use App\Model\Calculator\CalculationVariables\PrintFormats;
use App\Model\Calculator\CalculatorSetting;
use App\Model\Calculator\PricingRulesOfAssemblies\WidePrintedSheets;

/**
 * Implements functions of settings for a calculator.
 * Печать изделия по частям.
 */
class PrintByParts implements SettingEntity
{
    /**
     * A state of activity.
     *
     * @var bool
     */
    protected $active;

    /**
     * Initialises the instance and its data from an instance of the CalculatorSetting.
     *
     * @param CalculatorSetting $instance
     */
    public function __construct(CalculatorSetting $instance)
    {
        $this->fillFromArray($instance->settings ?? []);
    }

    /**
     * Fills new settings from the given array. Defines default values.
     *
     * @param array $array
     */
    public function fillFromArray(array $array): void
    {
        $this->active = empty($array) ? true : ((bool) ($array['active'] ?? false));
    }

    /**
     * Checks whether the settings are active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Filters sheets by a size of the given print format when a state of
     * the 'print by parts' is inactive.
     *
     * @param  WidePrintedSheets $pricingRule
     * @param  PrintFormats      $printFormats
     * @return void
     */
    public function filterSheets($pricingRule, $printFormats): void
    {
        if (!$this->isActive()) {
            $pricingRule->filterByProductSize($printFormats->getHeight(), $printFormats->getWidth());
        }
    }

    /**
     * Returns an array of the instance.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'active' => $this->active,
        ];
    }
}
