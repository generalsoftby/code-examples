<?php

namespace App\Model\Calculator\Settings;

use App\Model\Calculator\CalculatorSetting;
use App\Support\Number;

/**
 * Implements functions of settings for a calculator.
 * Наценка за узкое изделие.
 */
class MarkupForNarrowProduct implements SettingEntity
{
    /**
     * A state of activity.
     *
     * @var bool
     */
    protected $active;

    /**
     * A coefficient.
     *
     * @var float
     */
    protected $coefficient;

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
        $this->active = isset($array['coefficient']) ? ((bool) ($array['active'] ?? false)) : true;
        $this->coefficient = Number::normalizeFloat($array['coefficient'] ?? 0);

        if ($this->coefficient <= 0) {
            $this->coefficient = 1;
        }
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
     * Returns a coefficient.
     *
     * @return float
     */
    public function getCoefficient(): float
    {
        return $this->coefficient;
    }

    /**
     * Applies the coefficient for the given price when the settings are active.
     *
     * @param  float $price
     * @return float
     */
    public function apply(float $price): float
    {
        return $this->active ? $price * $this->coefficient : $price;
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
            'coefficient' => $this->coefficient,
        ];
    }
}
