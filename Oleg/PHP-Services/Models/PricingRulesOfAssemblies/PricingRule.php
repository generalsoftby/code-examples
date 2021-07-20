<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

use App\Services\Calculators\Errors;
use App\Services\Calculators\EstimateGroup;
use Dios\System\Multicasting\Interfaces\SimpleArrayEntity;

/**
 * The interface for pricing rules of assemblies.
 */
interface PricingRule extends SimpleArrayEntity
{
    /**
     * Checks whether the settings of the pricing rule are configured.
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Calculates a product price using the values for calculation.
     *
     * @param  ValueKeeper $values
     * @return bool
     */
    public function calculate(ValueKeeper $values): bool;

    /**
     * Returns an instance of EstimateGroup.
     *
     * @return EstimateGroup|null
     */
    public function getEstimateGroup(): ?EstimateGroup;

    /**
     * Returns an instance of the Errors.
     *
     * @return Errors
     */
    public function getErrors(): Errors;

    // TODO Функция для получения списка возможных переменных для формулы?
    // Похоже известно будет после разработки класса переменных формул или изменения
    // Интерфейса опций, чтобы определять допустимые переменные формул.
    // В конечном итоге разрешенными переменными будет пересечение переменных разных типов.
}
