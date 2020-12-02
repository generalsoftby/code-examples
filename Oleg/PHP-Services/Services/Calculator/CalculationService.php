<?php

namespace App\Services\Calculators;

use App\Model\Calculator\OptionCollection;
use App\Repositories\CalculatorOptionRepository;
use App\Services\Calculators\Blocks\UserBlocks;
use App\Services\Calculators\Contracts\CalculationService as CalculationServiceInterface;

/**
 * The service calculates a price of products or a service.
 */
class CalculationService implements CalculationServiceInterface
{
    /**
     * An instance of CalculatorOptionRepository.
     *
     * @var CalculatorOptionRepository
     */
    protected $optionRepository;

    /**
     * An instance of Estimate of the last calculation.
     *
     * @var Estimate|null
     */
    protected $estimate;

    /**
     * A collection of errors.
     *
     * @var Errors
     */
    protected $errors;

    /**
     * Initializes an instance of the class.
     */
    public function __construct(CalculatorOptionRepository $calculatorOptionRepository)
    {
        $this->optionRepository = $calculatorOptionRepository;
        $this->errors = new Errors;
    }

    /**
     * Returns options with their values for the calculation interface
     * by an calculator ID and an array with attribute value IDs.
     *
     * @param  int $calculatorId
     * @param  array|int[] $attributeValueIDs
     * @return OptionInputs|null
     */
    public function getOptionInputs(int $calculatorId, array $attributeValueIDs): ?OptionInputs
    {
        // TODO В случае использования сервиса, нужно передавать ID калькулятора и атрибутов.
        // TODO Использовать сервис/хранилище для получения опций по отдельным критериям: сборки или опции.

        /** @var OptionCollection|null $options */
        $options = $this->optionRepository->getActiveVisibleOptionsWithValuesByCalculatorIdAndAttributeValueIDs($calculatorId, $attributeValueIDs);

        if (! $options) {
            $this->errors->add(trans('calculator_errors.options_not_got_and_assembly_not_found'), Error::CONFIGURATION_ERROR);

            return null;
        }

        return new OptionInputs($options);
    }

    /**
     * Calculates prices by the given user blocks.
     *
     * @param  UserBlocks $blocks
     * @return bool
     */
    public function calculateByBlocks(UserBlocks $userBlocks): bool
    {
        $this->resetCalculationData();
        $userBlocks->validate();

        if (!$userBlocks->getErrorGroups()->hasErrors()) {
            $bookKeeper = $userBlocks->getBookKeeper();

            if ($bookKeeper->calculateByBlocks($userBlocks)) {
                $this->estimate = $bookKeeper->getEstimate();
            } else {
                $this->errors->unite($bookKeeper->getErrors());
            }
        }

        $this->errors->unite($userBlocks->getErrorGroups()->collapse());

        return !$this->errors->hasErrors();
    }

    /**
     * Returns an instance of Estimate of the last calculation.
     *
     * @return Estimate|null
     */
    public function getEstimate(): ?Estimate
    {
        return $this->estimate;
    }

    /**
     * Checks for any errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->errors->count();
    }

    /**
     * Returns a collection of the Errors.
     *
     * @return Errors
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }

    /**
     * Resets calculation data of the last calculation.
     *
     * @return void
     */
    public function resetCalculationData(): void
    {
        $this->estimate = null;
        $this->errors->clean();
    }
}
