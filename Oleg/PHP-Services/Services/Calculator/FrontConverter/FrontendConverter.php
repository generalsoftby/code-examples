<?php

namespace App\Services\Calculators\FrontConverter;

use App\Services\Calculators\Errors;
use App\Services\Calculators\Estimate;
use App\Services\Calculators\FrontConverter\Contracts\ErrorsConverter;
use App\Services\Calculators\FrontConverter\Contracts\EstimateConverter;
use App\Services\Calculators\FrontConverter\Contracts\FrontendConverter as FrontendConverterInterface;

/**
 * The class is used to convert data of Estimate and Errors for the frontend.
 */
class FrontendConverter implements FrontendConverterInterface
{
    /**
     * An instance of EstimateConverter.
     *
     * @var EstimateConverter|null
     */
    protected $estimateConverter;

    /**
     * An instance of ErrorsConverter.
     *
     * @var ErrorsConverter|null
     */
    protected $errorsConverter;

    /**
     * Sets an EstimateConverter.
     *
     * @param  EstimateConverter|null $estimateConverter
     * @return void
     */
    public function setEstimateConverter(?EstimateConverter $estimateConverter): void
    {
        $this->estimateConverter = $estimateConverter;
    }

    /**
     * Returns an EstimateConverter.
     *
     * @return EstimateConverter|null
     */
    public function getEstimateConverter(): ?EstimateConverter
    {
        return $this->estimateConverter;
    }

    /**
     * Sets an Estimate to the current EstimateConverter.
     *
     * @param  Estimate $estimate
     * @return void
     */
    public function setEstimate(Estimate $estimate): void
    {
        if ($this->estimateConverter) {
            $this->estimateConverter->setEstimate($estimate);
        }
    }

    /**
     * Returns an Estimate from the current EstimateConverter.
     *
     * @return Estimate|null
     */
    public function getEstimate(): ?Estimate
    {
        return $this->estimateConverter
            ? $this->estimateConverter->getEstimate()
            : null
        ;
    }

    /**
     * Sets an ErrorsConverter.
     *
     * @param  ErrorsConverter $errorsConverter
     * @return void
     */
    public function setErrorsConverter(ErrorsConverter $errorsConverter): void
    {
        $this->errorsConverter = $errorsConverter;
    }

    /**
     * Returns an ErrorsConverter.
     *
     * @return ErrorsConverter
     */
    public function getErrorsConverter(): ErrorsConverter
    {
        return $this->errorsConverter;
    }

    /**
     * Sets errors to the current ErrorsConverter.
     *
     * @param  Errors $errors
     * @return void
     */
    public function setErrors(Errors $errors): void
    {
        $this->errorsConverter->setInstanceOfErrors($errors);
    }

    /**
     * Returns errors from the current ErrorsConverter.
     *
     * @return Errors|null
     */
    public function getErrors(): ?Errors
    {
        return $this->errorsConverter->getInstanceOfErrors();
    }

    /**
     * Converts data to an array of the format of the specification:
     * an array with strings.
     *
     * @return array|string[]
     */
    public function toArrayOfSpecification(): array
    {
        return $this->getCalculationDataOfSpecification();
    }

    /**
     * Converts data to the form of the calculator interface of React frontend.
     *
     * @return array
     */
    public function toArrayOfReactFE(): array
    {
        $errors = $this->getArrayOfReactFE();
        $calculationData = $this->getCalculationDataOfReactFE();
        $estimate = $this->getEstimate();

        // Defines prices when an estimate exists.
        if ($estimate) {
            $price = round($estimate->getPrice(), 2);
            $pricePerProduct = round($estimate->getPricePerProduct(), 2);
            $numberOfProducts = $estimate->getNumberOfProducts();
        } else {
            $price = $pricePerProduct = $numberOfProducts = 0;
        }

        return [
            'errors' => $errors,
            'debug_error' => [],
            'price' => [
                [
                    'label' => 'Цена', // NOTE: it is not used
                    'for_one' => $pricePerProduct,
                    'total' => $price,
                    'calculator_label' => 'Calculator name', // NOTE: it is not used
                    'computation_info' => $calculationData,
                ],
            ],
            // Adds a number of products because it is used to call a discount
            // of the order.
            'number_of_products' => $numberOfProducts,
        ];
    }

    /**
     * Returns an array of React frotnend with errors.
     *
     * @return array
     */
    protected function getArrayOfReactFE(): array
    {
        return $this->errorsConverter
            ? $this->errorsConverter->toArrayOfReactFE()
            : []
        ;
    }

    /**
     * Returns an array of React frontend with a result of a calculation.
     *
     * @return array
     */
    protected function getCalculationDataOfReactFE(): array
    {
        return $this->estimateConverter
            ? $this->estimateConverter->toArrayOfReactFE()
            : []
        ;
    }

    /**
     * Returns an array of a specification with a result of a calculation.
     *
     * @return array
     */
    protected function getCalculationDataOfSpecification(): array
    {
        return $this->estimateConverter
            ? $this->estimateConverter->toArrayOfSpecification()
            : []
        ;
    }
}
