<?php

namespace App\Model\Calculator\Settings;

use App\Model\Calculator\CalculatorSetting;
use App\Model\Calculator\CalculatorSettingCollection;

class SettingEntityCollection implements \Iterator, \Countable
{
    /**
     * An array with SettingsEntity.
     *
     * @var array|SettingEntity[]
     */
    protected $instances;

    /**
     * An instance of CalculatorSetting for getting instances
     * without settings.
     *
     * @var CalculatorSetting
     */
    private $calculatorSetting;

    public function __construct(array $instances = [])
    {
        $this->instances = $instances;
        $this->calculatorSetting = new CalculatorSetting();
    }

    /**
     * Returns a collection with SettingEntity.
     *
     * @param  CalculatorSettingCollection $collection
     * @return SettingEntityCollection
     */
    public static function createFromCalculatorSettingCollection(
        CalculatorSettingCollection $collection
    ): self {
        $instances = [];

        /** @var CalculatorSetting $calculatorSetting */
        foreach ($collection as $calculatorSetting) {
            /** @var SettingEntity|null $settings */
            $settings = $calculatorSetting->getInstance();

            if ($settings) {
                $instances[$calculatorSetting->code_name] = $settings;
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
     * Returns the current SettingEntity.
     *
     * @return SettingEntity|null
     */
    public function current(): ?SettingEntity
    {
        /** @var SettingEntity|null $instance */
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
        return $this->current() !== null;
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
     * Returns keys of entities of settings.
     *
     * @return array|string[]
     */
    public function keys(): array
    {
        return array_keys($this->instances);
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
     * Returns types of settings.
     *
     * @return array|string[]
     */
    public function getTypes(): array
    {
        return $this->keys();
    }

    /**
     * Returns instances of SettingEntity.
     *
     * @return array|SettingEntity[]
     */
    public function getInstances(): array
    {
        return $this->instances;
    }

    /**
     * Filters instances by the given names.
     *
     * @param  array|string[] $names
     * @return SettingEntityCollection
     */
    public function filterByNames(array $names): self
    {
        return new self(array_filter($this->instances, function ($key) use ($names) {
            return in_array($key, $names);
        }, ARRAY_FILTER_USE_KEY));
    }

    /**
     * Returns an instance of SettingEntity by the given name.
     *
     * @param  string $name
     * @return SettingEntity|null
     */
    public function getByName(string $name): ?SettingEntity
    {
        return $this->instances[$name] ?? null;
    }

    /**
     * Returns an instance of SettingEntity by the given name.
     * If the instance does not exist then it will be initialized.
     *
     * @param  string $name
     * @return SettingEntity|null
     */
    public function getByNameOrInitialize(string $name): ?SettingEntity
    {
        return $this->instances[$name] ?? $this->newSettingEntity($name);
    }

    /**
     * Returns an instance by the given instance type.
     *
     * @param  string $instanceType
     * @return SettingEntity|null
     */
    public function firstByInstanceType(string $instanceType): ?SettingEntity
    {
        /** @var SettingEntity $instance */
        foreach ($this as $instance) {
            if ($instance instanceof $instanceType) {
                return $instance;
            }
        }

        return null;
    }

    /**
     * Initializes undefined entities by the given names.
     *
     * @param  array $names
     * @return void
     */
    public function initializeUndefinedEntities(array $names): void
    {
        foreach ($names as $name) {
            if (!$this->has($name)) {
                $instance = $this->newSettingEntity($name);

                if ($instance) {
                    $this->instances[$name] = $instance;
                }
            }
        }
    }

    /**
     * Initializes a SettingEntity with default data by the given name.
     *
     * @param  string $name
     * @return SettingEntity|null
     */
    public function newSettingEntity(string $name): ?SettingEntity
    {
        $this->calculatorSetting->code_name = $name;

        return $this->calculatorSetting->getInstance();
    }
}
