<?php

namespace App\Model\Calculator\BookKeepers;

use App\Model\Calculator\Calculator;
use App\Services\Calculators\Contracts\BookKeeperMapping as BookKeeperMappingInterface;
use Illuminate\Contracts\Container\Container;

/**
 * Implements the mapping to return instances of BookKeeper by calculator types.
 */
class BookKeeperMapping implements BookKeeperMappingInterface
{
    /**
     * An instance of Container.
     *
     * @var Container
     */
    protected $container;

    /**
     * The BookKeepers for calculators by their types of calculators.
     * Keys are types of calculators, values are classes of BookKeepers.
     *
     * @var array|string[]
     */
    protected $calculatorTypeMapping = [
        12 => \App\Model\Calculator\BookKeepers\Sheet::class,
        13 => \App\Model\Calculator\BookKeepers\RunningMeter::class,
        14 => \App\Model\Calculator\BookKeepers\MultipleProduct::class,
        15 => \App\Model\Calculator\BookKeepers\Area::class,
    ];

    /**
     * Initializes an instance of the class.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a class name of a BookKeeper for a calculator by its ID.
     *
     * @param  int    $type
     * @return string|null
     */
    public function getClassNameByType(int $type): ?string
    {
        return $this->calculatorTypeMapping[$type] ?? null;
    }

    /**
     * Checks whether the mapping has a class name with the given calculator type.
     *
     * @param  string $type
     * @return bool
     */
    public function hasClassName(string $type): bool
    {
        return isset($this->calculatorTypeMapping[$type]);
    }

    /**
     * Returns an array with class names of implemented BookKeepers.
     *
     * @return array|string[]
     */
    public function getClassNames(): array
    {
        return $this->calculatorTypeMapping;
    }

    /**
     * Creates a new instance of BookKeeper by the given type and calculator.
     *
     * @param  int $type
     * @param  Calculator $calculator
     * @return BookKeeper|null
     */
    public function newBookKeeper(int $type, Calculator $calculator): ?BookKeeper
    {
        $className = $this->getClassNameByType($calculator->type_id);

        return $className ? $this->container->make($className, compact('calculator')) : null;
    }
}
