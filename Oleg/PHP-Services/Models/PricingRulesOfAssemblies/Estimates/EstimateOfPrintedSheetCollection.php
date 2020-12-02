<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates;

/**
 * Contains and handlers estimates of printed sheets.
 */
class EstimateOfPrintedSheetCollection implements \Countable, \Iterator
{
    /**
     * An array with estimates.
     *
     * @var array|EstimateOfPrintedSheet[]
     */
    protected $estimates;

    /**
     * A position of the pointer.
     *
     * @var int
     */
    protected $position;

    /**
     * Initializes an instance of the class using estimates.
     *
     * @param array|EstimateOfPrintedSheet[] $estimates
     */
    function __construct(array $estimates = [])
    {
        $this->position = 0;
        $this->estimates = $estimates;
    }

    /**
     * Returns a number of estimates.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->estimates);
    }

    /**
     * Resets the pointer.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Returns a current EstimateOfPrintedSheet or null.
     *
     * @return EstimateOfPrintedSheet|null
     */
    public function current(): ?EstimateOfPrintedSheet
    {
        return $this->estimates[$this->position] ?? null;
    }

    /**
     * Returns a key of the pointer.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Moves the pointer further.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Checks whether the current estimate exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->estimates[$this->position]);
    }

    /**
     * Adds a new EstimateOfPrintedSheet to the collection.
     *
     * @param EstimateOfPrintedSheet $estimate
     */
    public function push(EstimateOfPrintedSheet $estimate): void
    {
        $this->estimates[] = $estimate;
    }

    /**
     * Returns a minimal price of products.
     *
     * @return float|null
     */
    public function getMinimalPrice(): ?float
    {
        /** @var EstimateOfPrintedSheet|null $estimate **/
        $estimate = $this->getEstimateWithMinimalPrice();

        return $estimate ? $estimate->getPriceOfProducts() : null;
    }

    /**
     * Returns an EstimateOfPrintedSheet with a minimal price.
     *
     * @return EstimateOfPrintedSheet|null
     */
    public function getEstimateWithMinimalPrice(): ?EstimateOfPrintedSheet
    {
        $estimateWithMinimalPrice = null;

        /** @var EstimateOfPrintedSheet $estimate **/
        foreach ($this->estimates as $estimate) {
            if (
                empty($estimateWithMinimalPrice)
                || $estimate->getPriceOfProducts() < $estimateWithMinimalPrice->getPriceOfProducts()
            ) {
                $estimateWithMinimalPrice = $estimate;
            }
        }

        return $estimateWithMinimalPrice;
    }

    /**
     * Returns an EstimateOfPrintedSheetCollection with a minimal price.
     *
     * @return EstimateOfPrintedSheetCollection
     */
    public function getEstimatesWithMinimalPrice(): self
    {
        /** @var float|null $minimalPrice **/
        $minimalPrice = $this->getMinimalPrice();

        if (is_null($minimalPrice)) {
            return new self;
        }

        /** @var array|EstimateOfPrintedSheet[] $estimates **/
        $estimates = array_filter($this->estimates, function (EstimateOfPrintedSheet $estimate) use ($minimalPrice) {
            return $estimate->getPriceOfProducts() === $minimalPrice;
        });

        return new self(array_values($estimates));
    }

    /**
     * Returns a minimal number of material for manufacturing.
     *
     * @return float|null
     */
    public function getMinimalNumberOfMaterial(): ?float
    {
        /** @var EstimateOfPrintedSheet|null $estimate **/
        $estimate = $this->getEstimateWithMinimalNumberOfMaterial();

        return $estimate ? $estimate->getNumberOfMaterialWithWastes() : null;
    }

    /**
     * Returns an EstimateOfPrintedSheet with a minimal number of material.
     *
     * @return EstimateOfPrintedSheet|null
     */
    public function getEstimateWithMinimalNumberOfMaterial(): ?EstimateOfPrintedSheet
    {
        $estimateWithMinimalNumberOfMaterial = null;

        /** @var EstimateOfPrintedSheet $estimate **/
        foreach ($this->estimates as $estimate) {
            if (
                empty($estimateWithMinimalNumberOfMaterial)
                || $estimate->getNumberOfMaterialWithWastes() < $estimateWithMinimalNumberOfMaterial->getNumberOfMaterialWithWastes()
            ) {
                $estimateWithMinimalNumberOfMaterial = $estimate;
            }
        }

        return $estimateWithMinimalNumberOfMaterial;
    }

    /**
     * Returns an EstimateOfPrintedSheetCollection with a minimal number of material.
     *
     * @return EstimateOfPrintedSheetCollection
     */
    public function getEstimatesWithMinimalNumberOfMaterial(): self
    {
        /** @var float|null $minimalNumberOfMaterial **/
        $minimalNumberOfMaterial = $this->getMinimalNumberOfMaterial();

        if (is_null($minimalNumberOfMaterial)) {
            return new self;
        }

        /** @var array|EstimateOfPrintedSheet[] $estimates **/
        $estimates = array_filter($this->estimates, function (EstimateOfPrintedSheet $estimate) use ($minimalNumberOfMaterial) {
            return $estimate->getNumberOfMaterialWithWastes() === $minimalNumberOfMaterial;
        });

        return new self(array_values($estimates));
    }

    /**
     * Returns the highest priority of a material.
     *
     * @return int|null
     */
    public function getHighestPriority(): ?int
    {
        /** @var EstimateOfPrintedSheet|null $estimateWithHighestPriority **/
        $estimateWithHighestPriority = $this->getEstimateWithHighestPriority();

        return $estimateWithHighestPriority ? $estimateWithHighestPriority->getPriorityOfPrintedSheet() : null;
    }

    /**
     * Returns an EstimateOfPrintedSheet with the highest priority material.
     * The priority -1 greater than 1.
     *
     * @return EstimateOfPrintedSheet
     */
    public function getEstimateWithHighestPriority(): ?EstimateOfPrintedSheet
    {
        $estimateWithHighestPriority = null;

        /** @var EstimateOfPrintedSheet $estimate **/
        foreach ($this->estimates as $estimate) {
            if (
                empty($estimateWithHighestPriority)
                || $estimate->getPriorityOfPrintedSheet() < $estimateWithHighestPriority->getPriorityOfPrintedSheet()
            ) {
                $estimateWithHighestPriority = $estimate;
            }
        }

        return $estimateWithHighestPriority;
    }

    /**
     * Returns an EstimateOfPrintedSheetCollection with the highest priority material.
     *
     * @return EstimateOfPrintedSheetCollection
     */
    public function getEstimatesWithHighestPriority(): self
    {
        /** @var int|null $highestPriority **/
        $highestPriority = $this->getHighestPriority();

        if (is_null($highestPriority)) {
            return new self;
        }

        /** @var array|EstimateOfPrintedSheet[] $estimates **/
        $estimates = array_filter($this->estimates, function (EstimateOfPrintedSheet $estimate) use ($highestPriority) {
            return $estimate->getPriorityOfPrintedSheet() === $highestPriority;
        });

        return new self(array_values($estimates));
    }

    /**
     * Returns an optimal EstimateOfPrintedSheet:
     * - a minimal price;
     * - a minimal material;
     * - the highest priority.
     *
     * @return EstimateOfPrintedSheet|null
     */
    public function getOptimalEstimate(): ?EstimateOfPrintedSheet
    {
        return $this
            ->getEstimatesWithMinimalPrice()
            ->getEstimatesWithMinimalNumberOfMaterial()
            ->getEstimatesWithHighestPriority()
            ->current()
        ;
    }
}
