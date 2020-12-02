<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Model\Calculator\CalculationVariables\VariableSettings\NumberOfProductsVariableSettings;
use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;

/**
 * The class implements handling of a number of products, so as an edition size.
 */
class NumberOfProducts implements EntityWithVariableSettings
{
    protected const STANDARD_TYPE = 'standard';

    /**
     * A label for users.
     *
     * @var string
     */
    protected $label;

    /**
     * A unit of the value.
     *
     * @var string|null
     */
    protected $unit;

    /**
     * A placeholder.
     *
     * @var string|null
     */
    protected $placeholder;

    /**
     * A type of user input.
     *
     * @var string
     */
    protected $type;

    /**
     * A min value.
     *
     * @var int
     */
    protected $minValue;

    /**
     * A max value.
     *
     * @var int|null
     */
    protected $maxValue;

    /**
     * An incremental step.
     *
     * @var int
     */
    protected $step;

    /**
     * A default value.
     *
     * @var int|null
     */
    protected $defaultValue;

    /**
     * A value of the number of products.
     *
     * @var int
     */
    protected $valueOfNumberOfProducts;

    /**
     * An instance of Errors.
     *
     * @var Errors
     */
    protected $errors;

    /**
     * Initializes an instance of the class.
     *
     * @param array|null $values
     */
    public function __construct(array $values = null)
    {
        $this->errors = new Errors;

        $this->fillFromArray($values ?? []);
    }

    /**
     * Fills new settings from the given array. Defines default values.
     *
     * @param array $array
     */
    public function fillFromArray(array $array): void
    {
        $this->label = $array['label'] ?? trans('calculation_variables.number_of_products');
        $this->unit = $array['unit'] ?? null;
        $this->placeholder = $array['placeholder'] ?? null;
        $this->type = $array['type'] ?? self::STANDARD_TYPE;
        $this->minValue = $array['rules']['standard']['min'] ?? 1;
        $this->maxValue = $array['rules']['standard']['max'] ?? null;
        $this->step = $array['rules']['standard']['step'] ?? 1;
        $this->defaultValue = $array['rules']['standard']['default'] ?? null;
    }

    /**
     * Sets an instance of VariableSettings to update settings of the variable.
     *
     * @param  NumberOfProductsVariableSettings $variableSettings
     * @return void
     */
    public function setVariableSettings(VariableSettings $variableSettings): void
    {
        if (!($variableSettings instanceof NumberOfProductsVariableSettings)) {
            throw new IncorrectVariableSettingsException(
                $variableSettings,
                NumberOfProductsVariableSettings::class
            );
        }

        if ($variableSettings->hasLabel()) {
            $this->label = $variableSettings->getLabel();
        }
    }

    /**
     * Returns a type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
     * Returns a unit.
     *
     * @return string|null
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * Returns a placeholder.
     *
     * @return string|null
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * Returns a min value.
     *
     * @return int
     */
    public function getMinValue(): int
    {
        return $this->minValue;
    }

    /**
     * Returns a max value.
     *
     * @return int|null
     */
    public function getMaxValue(): ?int
    {
        return $this->maxValue;
    }

    /**
     * Returns a step.
     *
     * @return int
     */
    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * Returns a default value.
     *
     * @return int|null
     */
    public function getDefaultValue(): ?int
    {
        return $this->defaultValue;
    }

    /**
     * Fills user variables with user values.
     * Returns true whether user variables were filled with user values.
     *
     * @param  array $values
     * @return bool
     */
    public function fillWithUserValues(array $values): bool
    {
        if (! $this->validate($values)) {
            return false;
        }

        $this->valueOfNumberOfProducts = $values['value'];

        return true;
    }

    /**
     * Validates given user data and returns result of the validation.
     *
     * @param  array $data
     * @return bool
     */
    public function validate($data): bool
    {
        $state = true;

        if (!isset($data['value'])) {
            $this->errors->add(
                trans('calculator_errors.number_of_products_is_not_assigned', [
                    'name' => $this->label,
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif (!is_int($data['value'])) {
            $this->errors->add(
                trans('calculator_errors.number_of_products_is_not_integer', [
                    'name' => $this->label,
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif (empty($data['value'])) {
            $this->errors->add(
                trans('calculator_errors.number_of_products_must_not_be_zero', [
                    'name' => $this->label,
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif ($data['value'] < $this->getMinValue()) {
            $this->errors->add(
                trans('calculator_errors.number_of_products_less_than_min_value', [
                    'name' => $this->label,
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif ($this->getMaxValue() !== null && $this->getMaxValue() < $data['value']) {
            $this->errors->add(
                trans('calculator_errors.number_of_products_greater_than_max_value', [
                    'name' => $this->label,
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif ($data['value'] % $this->getStep() !== 0) {
            $this->errors->add(
                trans('calculator_errors.number_of_products_has_incorrect_step', [
                    'name' => $this->label,
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        }

        return $state;
    }

    /**
     * Returns a value of the number of products.
     *
     * @return int|null
     */
    public function getValue(): ?int
    {
        return $this->valueOfNumberOfProducts;
    }

    /**
     * Returns errors of the validation.
     *
     * @return Errors
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }

    /**
     * Returns an array of the instance.
     *
     * @return array
     */
    public function toArray(): array
    {
        $rules = [];

        if ($this->type === self::STANDARD_TYPE) {
            $rules = [
                'min' => $this->getMinValue(),
                'max' => $this->getMaxValue(),
                'step' => $this->getStep(),
                'default' => $this->getDefaultValue(),
            ];
        }

        return [
            'label' => $this->label,
            'unit' => $this->unit,
            'placeholder' => $this->placeholder,
            'type' => $this->type,
            'rules' => [
                $this->type => $rules,
            ],
        ];
    }
}
