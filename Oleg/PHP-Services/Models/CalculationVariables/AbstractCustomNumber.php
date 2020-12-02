<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Model\Calculator\CalculationVariables\IntervalCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\CalculationVariables\PricingRules\CustomNumber;
use App\Services\Calculators\Errors;

/**
 * Keeps an instance CustonNumber of intervals and contains base methods to work
 * with intervals and a product to get a price.
 */
abstract class AbstractCustomNumber implements CalculationVariableEntity
{
    /**
     * A state of activity.
     *
     * @var bool
     */
    protected $active;

    /**
     * Errors container.
     *
     * @var Errors
     */
    protected $errors;

    /**
     * Number of products.
     *
     * @var int|null
     */
    protected $numberOfProducts;

    /**
     * An appropriate interval.
     *
     * @var Interval|null
     */
    protected $appropriateInterval;

    /**
     * An instance of CustomNumber.
     *
     * @var CustomNumber
     */
    protected $customNumber;

    /**
     * Initializes an instance of the class.
     *
     * @param array|null $values
     */
    public function __construct(array $values = null)
    {
        $this->errors = new Errors();
        $this->fillFromArray($values ?? []);
    }

    /**
     * Fills the instance from an array.
     *
     * @param  array $array
     * @return void
     */
    public function fillFromArray(array $array)
    {
        $this->customNumber = new CustomNumber($array);
        $this->active = empty($array)
            ? true
            : (bool) ($array['active'] ?? false)
        ;
    }

    /**
     * Returns true whether user variables were filled with user values.
     *
     * @param  array $values
     * @return bool
     */
    abstract public function fillWithUserValues(array $values): bool;

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
     * Checkes if variable is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Returns CustomNumber instance.
     *
     * @return PricingRules\CustomNumber
     */
    public function getCustomNumber(): CustomNumber
    {
        return $this->customNumber;
    }

    /**
     * Returns a collection with intervals.
     *
     * @return IntervalCollection
     */
    public function getIntervals(): IntervalCollection
    {
        return $this->getCustomNumber()->getIntervals();
    }

    /**
     * Returns an extra charge of the interval.
     *
     * @return Cost
     */
    public function getExtraCharge(): Cost
    {
        return $this->getCustomNumber()->getExtraCharge();
    }

    /**
     * Returns an minimal price of the interval.
     *
     * @return Cost
     */
    public function getMinPrice(): Cost
    {
        return $this->getCustomNumber()->getMinPrice();
    }

    /**
     * Returns an appropriate interval.
     *
     * @return Interval|null
     */
    public function getAppropriateInterval(): ?Interval
    {
        return $this->appropriateInterval;
    }

    /**
     * Returns a cost of the appropriate interval.
     *
     * @return Cost|null
     */
    public function getCostOfAppropriateInterval(): ?Cost
    {
        return $this->appropriateInterval
            ? $this->appropriateInterval->getCost()
            : null
        ;
    }

    /**
     * Returns a price of the appropriate interval.
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->appropriateInterval
            ? $this->appropriateInterval->getCost()->getValueByCurrency()
            : null
        ;
    }

    /**
     * Searches an appropriate interval by the given value of the interval.
     *
     * @param  int $value
     * @return null|Interval
     */
    public function findInterval(int $value): ?Interval
    {
        return $this->getIntervals()->getIntervalByValue($value);
    }

    /**
     * Sets a number of products.
     *
     * @param  int $value
     * @return void
     */
    public function setNumberOfProducts(int $value): void
    {
        $this->numberOfProducts = $value;
        $this->defineInterval();
    }

    /**
     * Defines properties and finds interval.
     *
     * @return void
     */
    abstract protected function defineInterval(): void;

    /**
     * Returns data of the current instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'active' => $this->active
        ] + $this->customNumber->toArray();
    }
}
