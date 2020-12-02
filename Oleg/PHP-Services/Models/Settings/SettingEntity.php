<?php

namespace App\Model\Calculator\Settings;

use App\Model\Calculator\CalculatorSetting;

/**
 * The interface to implements classes of the CalculatorSetting (the instance).
 */
interface SettingEntity
{
    /**
     * Initialises the instance and its data from an instance of the CalculatorSetting.
     *
     * @param CalculatorSetting $instance
     */
    public function __construct(CalculatorSetting $instance);

    /**
     * Fills settings from the given array. Defines default values.
     *
     * @param array $array
     */
    public function fillFromArray(array $array): void;

    /**
     * Returns an array of the instance.
     *
     * @return array
     */
    public function toArray(): array;
}
