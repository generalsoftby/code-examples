<?php

namespace App\Services\Calculators\Blocks\ComponentConfigurations;

use App\Services\Calculators\Blocks\ComponentConfiguration;

/**
 * Contains a configuration for a PrintFormats component.
 */
class PrintFormatsConfiguration implements ComponentConfiguration
{
    const ALWAYS_UPDATE = 'always';

    const UPDATE_UNIT_FIRST_MANUAL_CHANGE = 'first_manual_change';

    /**
     * A default label.
     *
     * @var string
     */
    protected $label = 'Формат печати';

    /**
     * A rule of updating of the component.
     *
     * @var string
     */
    protected $updateUntil = self::ALWAYS_UPDATE;

    /**
     * Initializes an instance.
     *
     * @param string $label
     * @param string $updateUntil
     */
    public function __construct(string $label = 'Формат печати', $updateUntil = self::ALWAYS_UPDATE)
    {
        $this->label = $label;
        $this->updateUntil = $updateUntil;
    }

    /**
     * Sets a lable.
     *
     * @param  string $label
     * @return void
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * Returns a label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets a rule of updating.
     *
     * @param  string $rule
     * @return void
     */
    public function setRuleOfUpdating(string $rule): void
    {
        $this->updateUntil = $rule;
    }

    /**
     * Returns a rule of updating.
     *
     * @return string
     */
    public function getRuleOfUpdating(): string
    {
        return $this->updateUntil;
    }

    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'updateUntil' => $this->updateUntil,
        ];
    }
}
