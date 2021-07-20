<?php

namespace App\Services\Calculators\FrontConverter;

use App\Services\Calculators\Estimate;
use App\Services\Calculators\EstimateItem;
use App\Services\Calculators\EstimateSubgroup;
use App\Services\Calculators\EstimateValue;
use App\Services\Calculators\FrontConverter\NormalizerOfEstimateValues as Normalizer;
use App\Services\Calculators\FrontConverter\ReactFE\CalculationResultCollection;
use App\Services\Calculators\FrontConverter\ReactFE\ItemValue;
use App\Services\Calculators\FrontConverter\Traits\ThrowsNoneEstimateException;
use App\Support\Translation;

/**
 * Functions to describe an estimate for the calculation interface (CI) of React.
 */
class ReactCIDescriber
{
    use ThrowsNoneEstimateException;

    /**
     * An instance of Estimate.
     *
     * @var Estimate|null
     */
    protected $estimate;

    /**
     * A debug mode.
     *
     * @var bool
     */
    protected $debug;

    /**
     * An initializes an instance of the class.
     *
     * @param Estimate $estimate
     * @param bool     $debug A state of the debug mode.
     */
    public function __construct(Estimate $estimate = null, bool $debug = false)
    {
        $this->estimate = $estimate;
        $this->debug = $debug;
    }

    /**
     * Sets an instance of Estimate.
     *
     * @param  Estimate $estimate
     * @return void
     */
    public function setEstimate(Estimate $estimate): void
    {
        $this->estimate = $estimate;
    }

    /**
     * Returns an instance of Estimate.
     *
     * @return  Estimate
     */
    public function getEstimate(): ?Estimate
    {
        return $this->estimate;
    }

    /**
     * Sets a state of the debug mode.
     *
     * @param  bool $debug
     * @return void
     */
    public function setDebugMode(bool $debug = true): void
    {
        $this->debug = $debug;
    }

    /**
     * Returns a state of the debug mode.
     *
     * @return bool
     */
    public function getDebugMode(): bool
    {
        return $this->debug;
    }

    /**
     * Describes the current estimate for CI and returns a collection.
     *
     * @return CalculationResultCollection
     *
     * @throws NoneEstimateException
     */
    public function describe(): CalculationResultCollection
    {
        $this->throwNoneEstimateException();

        return $this->describeByEstimate($this->estimate, $this->debug);
    }

    /**
     * Describes the given estimate for CI and returns a collection.
     *
     * @param  Estimate $estimate An estimate of calculation.
     * @param  bool     $debug A state of the debug mode.
     * @return CalculationResultCollection
     */
    public function describeByEstimate(Estimate $estimate, bool $debug = false): CalculationResultCollection
    {
        $collection = new CalculationResultCollection();

        foreach ($estimate as $groupName => $group) {
            $groupTitle = $collection->addGroupTitle($this->getGroupTitleByEstimateGroupName($groupName));
            $groupTitle->bolden();
            $groupTitle->alignCenter();
            $groupTitle->setUnderline();

            if ($group->areAllItemsDebugging()) {
                $groupTitle->setDebug();
            }

            /** @var EstimateItem $estimateItem */
            foreach ($group as $valueCodeName => $estimateItem) {
                /** @var ItemValue|CalculationResultCollection $itemValues */
                $itemValues = $this->getValuesByEstimateItem($valueCodeName, $estimateItem);
                $collection = $collection->merge($itemValues);
            }
        }

        $collection = $collection->merge($this->getResultOfCalculationByEstimate($estimate));
        $collection->setDebugMode($debug);

        return $collection;
    }

    /**
     * Returns values by the given EstimateItem and the given value name.
     *
     * @param  string       $codeName A code name of the value.
     * @param  EstimateItem $estimateItem An EstimateValue of the value.
     * @return ItemValue|CalculationResultCollection
     */
    public function getValuesByEstimateItem(string $codeName, EstimateItem $estimateItem)
    {
        if ($estimateItem->isScalar()) {
            $itemValue = new ItemValue(
                $this->getValueTitleByValueCodeName($codeName),
                Normalizer::normalizeValue($estimateItem->getValue()),
                $estimateItem->isDebugging()
            );

            return $this->boldenValueIfNeccessary($itemValue, $codeName);
        }

        if ($estimateItem->isSubgroup()) {
            return $this->getCollectionOfEstimateSubgroup(
                $codeName,
                $estimateItem->getValue(),
                $estimateItem->isDebugging()
            );
        }

        // EstimateValue is the last possible variant.
        return $this->getCollectionOfEstimateValue(
            $codeName,
            $estimateItem->getValue(),
            $estimateItem->isDebugging()
        );
    }

    /**
     * Returns a collection with values of the given EstimateSubgroup.
     *
     * @param  string $codeName
     * @param  EstimateSubgroup $estimateSubgroup
     * @param  bool   $debug
     * @return CalculationResultCollection
     */
    public function getCollectionOfEstimateSubgroup(
        string $codeName,
        EstimateSubgroup $estimateSubgroup,
        bool $debug
    ): CalculationResultCollection {
        $collection = new CalculationResultCollection();
        $collection->addGroupTitle(
            $this->getGroupTitleByEstimateGroupName($codeName),
            $debug
        );

        return $collection->merge($this->getValuesOfEstimateSubgroup($estimateSubgroup));
    }

    /**
     * Creates and returns a collection with values from the given EstimateGroup.
     *
     * @param  EstimateSubgroup $subgroup
     * @return CalculationResultCollection
     */
    public function getValuesOfEstimateSubgroup(EstimateSubgroup $subgroup): CalculationResultCollection
    {
        $collection = new CalculationResultCollection();

        foreach ($subgroup as $codeName => $item) {
            $value = $collection->addValue(
                $this->getValueTitleByValueCodeName($codeName),
                Normalizer::normalizeValue($item->getValue()),
                $item->isDebugging()
            );
            $value->setLeftIndent();

            $this->boldenValueIfNeccessary($value, $codeName);
        }

        return $collection;
    }

    /**
     * Returns a collection with values by the given EstimateValue.
     *
     * @param  string $codeName
     * @param  EstimateValue $value
     * @param  bool   $debug
     * @return CalculationResultCollection
     */
    public function getCollectionOfEstimateValue(
        string $codeName,
        EstimateValue $value,
        bool $debug
    ): CalculationResultCollection {
        $collection = new CalculationResultCollection();
        $collection->addGroupTitle(
            $this->getGroupTitleByEstimateGroupName($codeName),
            $debug
        );

        return $this->getValuesOfEstimateValue($value, $debug);
    }

    /**
     * Returns values of the given EstimateValue.
     *
     * @param  EstimateValue $estimateValue
     * @param  bool $debug
     * @return CalculationResultCollection
     */
    public function getValuesOfEstimateValue(
        EstimateValue $estimateValue,
        bool $debug = false
    ): CalculationResultCollection {
        $values = $estimateValue->toArray();
        $collection = new CalculationResultCollection();

        foreach ($values as $codeName => $value) {
            $value = $collection->addValue(
                $this->getValueTitleByValueCodeName($codeName),
                Normalizer::normalizeValue($value),
                $debug
            );
            $value->setLeftIndent();
        }

        return $collection;
    }

    /**
     * Returns a result of calculation by the given Estimate.
     *
     * @param  Estimate $estimate
     * @return CalculationResultCollection
     */
    public function getResultOfCalculationByEstimate(Estimate $estimate): CalculationResultCollection
    {
        $collection = new CalculationResultCollection;
        $groupTitle = $collection->addGroupTitle(trans('estimate.result'));
        $groupTitle->bolden();
        $groupTitle->setOverline();
        $groupTitle->setBoldOverline();
        $groupTitle->setUnderline();
        $groupTitle->setBoldUnderline();
        $itemValue = $collection->addValue(
            trans('estimate.price_per_product'),
            Normalizer::normalizeValue($estimate->getPricePerProduct())
        );
        $itemValue->bolden();
        $itemValue = $collection->addValue(trans('estimate.number_of_products'), $estimate->getNumberOfProducts());
        $itemValue->bolden();
        $itemValue = $collection->addValue(
            trans('estimate.total_cost'),
            Normalizer::normalizeValue($estimate->getPrice())
        );
        $itemValue->bolden();

        return $collection;
    }

    /**
     * Boldens the given value if it is neccessary.
     *
     * @param  ItemValue $itemValue
     * @param  string $codeName
     * @return ItemValue
     */
    public function boldenValueIfNeccessary(ItemValue $itemValue, string $codeName): ItemValue
    {
        if (in_array($codeName, ['price', 'price_per_product', 'price_per_unit', 'price_of_options'])) {
            $itemValue->bolden();
        }

        return $itemValue;
    }

    /**
     * Returns a group title by the given estimate group name.
     *
     * @param  string $groupName
     * @return string
     */
    public function getGroupTitleByEstimateGroupName(string $groupName): string
    {
        return Translation::getTranslationOrKey('estimate', $groupName);
    }

    /**
     * Returns a title of a value by the given value name.
     *
     * @param  string $valueName
     * @return string
     */
    public function getValueTitleByValueCodeName(string $valueName): string
    {
        return Translation::getTranslationOrKey('estimate', $valueName);
    }
}
