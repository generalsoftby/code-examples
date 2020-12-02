<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Model\Calculator\CalculationVariable;
use App\Model\Calculator\CalculationVariableCollection;
use App\Model\Calculator\CalculationVariables\CalculationVariableEntity;

class CalculationVariableEntityCollection implements \Iterator, \Countable
{
    /**
     * An array with CalculationVariableEntity.
     *
     * @var array|CalculationVariableEntity[]
     */
    protected $instances;

    /**
     * Types of the calculation variables.
     *
     * @var array|string[]
     */
    private $types;

    /**
     * An instance of CalculationVariable for getting instances
     * without calculation variables.
     *
     * @var CalculationVariable
     */
    private $calculationVariable;

    public function __construct(array $instances = [])
    {
        $this->instances = $this->filterInstances($instances);
        $this->calculationVariable = new CalculationVariable();
        $this->defineTypes();
    }

    /**
     * Returns a collection with CalculationVariableEntity.
     *
     * @param  CalculationVariableCollection $collection
     * @return CalculationVariableEntityCollection
     */
    public static function createFromCalculationVariableCollection(
        CalculationVariableCollection $collection
    ): self {
        $instances = [];

        /** @var CalculationVariable $variable */
        foreach ($collection as $variable) {
            /** @var CalculationVariableEntity|null $settings */
            $settings = $variable->getSettings();

            if ($settings) {
                $instances[$variable->name] = $settings;
            }
        }

        return new self($instances);
    }

    /**
     * Resets the current position.
     */
    public function rewind(): void
    {
        reset($this->instances);
    }

    /**
     * Returns the current CalculationVariableEntity.
     *
     * @return CalculationVariableEntity|null
     */
    public function current(): ?CalculationVariableEntity
    {
        /** @var CalculationVariableEntity|null $instance */
        $instance = current($this->instances);

        return $instance !== false ? $instance : null;
    }

    /**
     * Returns the current key.
     *
     * @return string
     */
    public function key(): string
    {
        return key($this->instances);
    }

    /**
     * The poiter moves to the next entity.
     */
    public function next(): void
    {
        next($this->instances);
    }

    /**
     * Checks if the current entity exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        /** @var CalculationVariableEntity|null $instance */
        $instance = current($this->instances);

        return $instance !== false;
    }

    /**
     * Returns a number of entities.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->instances);
    }

    /**
     * Returns keys of entities of calculation variables.
     *
     * @return array|string[]
     */
    public function keys(): array
    {
        return array_keys($this->instances);
    }

    /**
     * Returns types of the calculation variables.
     *
     * @return array|string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Checks whether the collection has an entity with the given name.
     *
     * @param  string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->instances[$name]);
    }

    /**
     * Returns instances of CalculationVariableEntity.
     *
     * @return array|CalculationVariableEntity[]
     */
    public function getInstances(): array
    {
        return $this->instances;
    }

    /**
     * Filters instances by types.
     * Returns an array with instances of CalculationVariableEntity.
     *
     * @param  array $instances
     * @return array|CalculationVariableEntity[]
     */
    public function filterInstances(array $instances): array
    {
        return array_filter($instances, function ($instance) {
            return $instance instanceof CalculationVariableEntity;
        });
    }

    /**
     * Filters instances by the given types.
     *
     * @param  array|string[] $types
     * @return CalculationVariableEntityCollection
     */
    public function filterByTypes(array $types): self
    {
        return new self(array_filter($this->instances, function ($key) use ($types) {
            return in_array($key, $types);
        }, ARRAY_FILTER_USE_KEY));
    }

    /**
     * Returns a type by the given name of the calculation variable.
     *
     * @param  string $name
     * @return string|null
     */
    public function getTypeByName(string $name): ?string
    {
        $instance = $this->instances[$name] ?? null;

        return $instance
            ? $this->calculationVariable->getTypeByHandlerClassName(get_class($instance))
            : null
        ;
    }

    /**
     * Returns an instance of CalculationVariableEntity by the given name.
     *
     * @param  string $name
     * @return CalculationVariableEntity|null
     */
    public function getByName(string $name): ?CalculationVariableEntity
    {
        return $this->instances[$name] ?? null;
    }

    /**
     * Returns an instance of CalculationVariableEntity by the given name.
     * If the instance does not exist then it will be initialized
     * by the given type.
     *
     * @param  string $type
     * @return CalculationVariableEntity|null
     */
    public function getByNameOrInitializeByType(string $name, string $type): ?CalculationVariableEntity
    {
        return $this->instances[$name] ?? $this->newCalculationVariableEntityByType($type);
    }

    /**
     * Fills a instance with user data.
     *
     * @param  string $type
     * @param  array  $data
     * @return bool
     */
    public function fillInstanceWithUserData(string $type, array $data): bool
    {
        /** @var CalculationVariableEntity|null $instance */
        $instance = $this->instances[$type] ?? null;

        return $instance && $instance->fillWithUserValues($data);
    }

    /**
     * Initializes a CalculationVariableEntity with default data by the given type.
     *
     * @param  string $type
     * @return CalculationVariableEntity|null
     */
    public function newCalculationVariableEntityByType(string $type): ?CalculationVariableEntity
    {
        /** @var string|null $className */
        $className = $this->calculationVariable->getClassNameOfEntityHandlerByType($type);

        return $className
            ? $this->calculationVariable->newInstanceByClassNameOfEntity($className)
            : null
        ;
    }

    /**
     * Defines types of the calculation variables.
     *
     * @return void
     */
    protected function defineTypes(): void
    {
        $this->types = [];

        foreach ($this->instances as $instance) {
            $this->types[] = $this->calculationVariable->getTypeByHandlerClassName(get_class($instance));
        }
    }
}
