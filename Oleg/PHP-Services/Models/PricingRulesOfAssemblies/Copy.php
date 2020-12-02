<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

use App\Model\Calculator\Calculator;
use App\Model\Calculator\CalculatorAssembly;
use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;
use App\Services\Calculators\EstimateGroup;

/**
 * The class of pricing rules for the 'copy' type.
 */
class Copy implements PricingRule
{
    /**
     * An instance of the Errors.
     *
     * @var Errors
     */
    protected $errors;

    /**
     * A state of throwing oexceptions.
     * If it is true, then system exceptions will be thrown.
     *
     * @var bool
     */
    protected $throwExceptions;

    /**
     * An ID of assembly.
     *
     * @var int|null
     */
    protected $assemblyId;

    /**
     * An ID of calculator.
     *
     * @var int|null
     */
    protected $calculatorId;

    /**
     * An instance of the related CalculatorAssembly.
     *
     * @var CalculatorAssembly
     */
    protected $relatedAssembly;

    /**
     * An instance of a Calculator of the related CalculatorAssembly.
     *
     * @var Calculator
     */
    protected $relatedCalculator;

    /**
     * A copy instance with pricing rule.
     *
     * @var PricingRule
     */
    protected $copyPricingRule;

    /**
     * Initializes an instance of the class using rules for sheets.
     *
     * @param array|null $rules
     */
    public function __construct(array $rules = null)
    {
        $this->errors = new Errors;
        $this->throwExceptions = config('app.debug');

        $this->fillFromArray($rules ?? []);
        $this->loadCopyPricingRule();
    }

    /**
     * Fills an instance with rules for configuring the instance and getting
     * copy another instance.
     *
     * @param  array $rules
     * @return void
     */
    public function fillFromArray(array $rules)
    {
        $this->assemblyId = $rules['copy']['assembly_id'] ?? null;
        $this->calculatorId = $rules['copy']['calculator_id'] ?? null;
    }

    /**
     * Checks whether the copy has an ID of the related assembly.
     *
     * @return bool
     */
    public function hasRelation(): bool
    {
        return isset($this->assemblyId);
    }

    /**
     * Returns an assembly ID of the related assembly.
     *
     * @return int|null
     */
    public function getAssemblyId(): ?int
    {
        return $this->assemblyId;
    }

    /**
     * Returns a title of the related assembly.
     *
     * @return string|null
     */
    public function getAssemblyTitle(): ?string
    {
        return $this->relatedAssembly->title;
    }

    /**
     * Checks whether the copy has an ID of the related calculator.
     *
     * @return bool
     */
    public function hasRelationWithCalculator(): bool
    {
        return isset($this->calculatorId);
    }

    /**
     * Returns a calculator ID of the related assembly.
     *
     * @return int|null
     */
    public function getCalculatorId(): ?int
    {
        return $this->calculatorId;
    }

    /**
     * Returns a calculator type ID of the calculator.
     *
     * @return int|null
     */
    public function getCalculatorTypeId(): ?int
    {
        /** @var Calculator|null $calculator */
        $calculator = $this->loadRelatedCalculator();

        return isset($calculator)
            ? $calculator->type_id
            : null
        ;
    }

    /**
     * Returns a system title of a calculator of the related assembly.
     *
     * @return string|null
     */
    public function getCalculatorTitle(): ?string
    {
        /** @var Calculator|null $calculator */
        $calculator = $this->loadRelatedCalculator();

        return isset($calculator)
            ? $calculator->title_system
            : null
        ;
    }

    /**
     * Returns an instance of the related assembly.
     *
     * @return CalculatorAssembly|null
     */
    public function getRelatedAssembly(): ?CalculatorAssembly
    {
        return $this->relatedAssembly;
    }

    /**
     * Checks whether the copy has a related assembly.
     *
     * @return bool
     */
    public function hasRelatedAssembly(): bool
    {
        return isset($this->relatedAssembly);
    }

    /**
     * Returns an instance of the copy.
     *
     * @return PricingRule|null
     */
    public function getCopyPricingRule(): ?PricingRule
    {
        return $this->copyPricingRule;
    }

    /**
     * Checks whether the copy has an existing instance.
     */
    public function hasCopyPricingRule(): bool
    {
        return isset($this->copyPricingRule);
    }

    /**
     * Checks whether the settings of the pricing rule are configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->isLocalConfigured() && $this->isForeignConfigured();
    }

    /**
     * Checks whether the copy is local configured.
     *
     * @return bool
     */
    public function isLocalConfigured(): bool
    {
        $state = true;

        if (! $this->hasRelation()) {
            $this->errors->add(
                trans('calculator_errors.related_assembly_was_not_assigned'),
                Error::CONFIGURATION_ERROR
            );
            $state = false;
        } elseif (! $this->hasRelatedAssembly()) {
            $this->errors->add(
                trans('calculator_errors.related_assembly_was_not_loaded'),
                Error::CONFIGURATION_ERROR
            );
            $state = false;
        } elseif (! $this->hasCopyPricingRule()) {
            $this->errors->add(
                trans('calculator_errors.related_assembly_is_not_supported', [
                    'calculator' => $this->getCalculatorTitle(),
                    'name' => $this->getAssemblyTitle()
                ]),
                Error::SYSTEM_ERROR
            );
            $state = false;
        }

        return $state;
    }

    /**
     * Checks whether the copy is foreign configured.
     *
     * @return bool
     */
    public function isForeignConfigured(): bool
    {
        if (! isset($this->copyPricingRule)) {
            $this->errors->add(
                trans('calculator_errors.instance_of_pricing_rule_of_assembly_was_not_assigned'),
                Error::SYSTEM_ERROR
            );
            return false;
        } elseif (! $this->copyPricingRule->isConfigured()) {
            $this->errors->add(
                trans('calculator_errors.related_assembly_is_not_configured', [
                    'calculator' => $this->getCalculatorTitle(),
                    'name' => $this->getAssemblyTitle()
                ]),
                Error::CONFIGURATION_ERROR
            );
            return false;
        }

        return $this->copyPricingRule->isConfigured();
    }

    /**
     * Calculates a product price using the values for calculation.
     *
     * @param  ValueKeeper $values
     * @return bool
     */
    public function calculate(ValueKeeper $values): bool
    {
        return $this->isConfigured()
            ? $this->copyPricingRule->calculate($values)
            : false
        ;
    }

    /**
     * Returns an instance of EstimateGroup.
     *
     * @return EstimateGroup|null
     */
    public function getEstimateGroup(): ?EstimateGroup
    {
        // TODO Можно добавить группу или значения для отладки, чтобы указать,
        // что была использована копия
        return isset($this->copyPricingRule)
            ? $this->copyPricingRule->getEstimateGroup()
            : null
        ;
    }

    /**
     * Returns an instance of the Errors.
     * Contains local errors and foreign errors.
     *
     * @return Errors
     */
    public function getErrors(): Errors
    {
        return isset($this->copyPricingRule)
            ? $this->errors->unite($this->copyPricingRule->getErrors())
            : $this->errors
        ;
    }

    /**
     * Returns local errors.
     *
     * @return Errors
     */
    public function getLocalErrors(): Errors
    {
        return $this->errors;
    }

    /**
     * Returns foreign errors.
     *
     * @return Errors|null
     */
    public function getForeignErrors(): ?Errors
    {
        return isset($this->copyPricingRule)
            ? $this->copyPricingRule->getErrors()
            : null
        ;
    }

    /**
     * Loads a related assembly and returns it.
     *
     * @return CalculatorAssembly|null
     */
    protected function loadRelatedAssembly(): ?CalculatorAssembly
    {
        if (! isset($this->relatedAssembly) && $this->hasRelation()) {
            /** @var CalculatorAssembly|null $relatedAssembly */
            $relatedAssembly = CalculatorAssembly::find($this->assemblyId);

            if ($relatedAssembly) {
                $this->relatedAssembly = $relatedAssembly;
            }
        }

        return $this->relatedAssembly;
    }

    /**
     * Loads a copy pricing rule and returns it.
     *
     * @return PricingRule|null
     */
    protected function loadCopyPricingRule(): ?PricingRule
    {
        $relatedAssembly = $this->loadRelatedAssembly();

        if ($relatedAssembly) {
            /** @var PricingRule|null $pricingRule */
            $pricingRule = $relatedAssembly->getPricingRule();

            if ($pricingRule) {
                $this->copyPricingRule = $pricingRule;
            }
        }

        return $this->copyPricingRule;
    }

    /**
     * Loads a related calculator and returns it.
     *
     * @return Calculator|null
     */
    protected function loadRelatedCalculator(): ?Calculator
    {
        if (! isset($this->relatedCalculator) && $this->hasRelationWithCalculator()) {
            /** @var Calculator|null $calculator */
            $calculator = Calculator::find($this->calculatorId);

            if ($calculator) {
                $this->relatedCalculator = $calculator;
            }
        }

        return $this->relatedCalculator;
    }

    /**
     * Returns an instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'assembly_id' => $this->assemblyId,
            'calculator_id' => $this->calculatorId,
        ];
    }
}
