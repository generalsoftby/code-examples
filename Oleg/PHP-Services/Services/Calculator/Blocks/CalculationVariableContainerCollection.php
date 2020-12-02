<?php

namespace App\Services\Calculators\Blocks;

use App\Model\Calculator\CalculationVariables\CalculationVariableEntity;
use App\Model\Calculator\CalculationVariables\CalculationVariableEntityCollection;
use App\Model\Calculator\CalculationVariables\EntityWithVariableSettings;

/**
 * Keeps containers of calculation variables.
 * It is used to pass data of calculation variables to the frontend.
 */
class CalculationVariableContainerCollection implements \Countable, \Iterator
{
    /**
     * An array with containers.
     *
     * @var array|CalculationVariableContainer[]
     */
    protected $containers = [];

    /**
     * Initializes an instance of the class.
     *
     * @param array|CalculationVariableContainer[] $containers
     */
    public function __construct(array $containers = [])
    {
        array_walk($containers, [$this, 'add']);
    }

    /**
     * Resets the current position.
     */
    public function rewind(): void
    {
        reset($this->containers);
    }

    /**
     * Returns the current CalculationVariableContainer.
     *
     * @return CalculationVariableContainer|null
     */
    public function current(): ?CalculationVariableContainer
    {
        /** @var CalculationVariableContainer|bool $container */
        $container = current($this->containers);

        return $container !== false ? $container : null;
    }

    /**
     * Returns the current key.
     * The key of the container is a name of the calculation variable.
     *
     * @return string
     */
    public function key(): ?string
    {
        return key($this->containers);
    }

    /**
     * The poiter moves to the next container.
     */
    public function next(): void
    {
        next($this->containers);
    }

    /**
     * Checks whether the current container exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() !== null;
    }

    /**
     * Counts a number of containers.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->containers);
    }

    /**
     * Returns keys of containers of calculation variable containers.
     *
     * @return array|string[]
     */
    public function keys(): array
    {
        return array_keys($this->containers);
    }

    /**
     * Creates an instance of the class from the given calculation variable
     * of containers.
     *
     * @param  CalculationVariableOfContentsCollection $collection
     * @return CalculationVariableContainerCollection
     */
    public static function createFromCalculationVariableOfContentsCollection(
        CalculationVariableOfContentsCollection $collection
    ): self {
        $containers = array_map(function (CalculationVariableOfContents $variable) {
            return new CalculationVariableContainer($variable);
        }, $collection->getCalculationVariablesOfContents());

        return new self($containers);
    }

    /**
     * Creates an instance of the class from the given calculation variable
     * of containers.
     * The alias of createFromCalculationVariableOfContentsCollection().
     *
     * @param  CalculationVariableOfContentsCollection $collection
     * @return self
     */
    public static function createFromContents(CalculationVariableOfContentsCollection $collection): self
    {
        return self::createFromCalculationVariableOfContentsCollection($collection);
    }

    /**
     * Fills the containers with the given calculation variable entity.
     * If an instance of an entity does not exist then it will be initialized.
     *
     * @param  CalculationVariableEntityCollection $collection
     * @return void
     */
    public function fillWithCalculationVariableEntityCollection(CalculationVariableEntityCollection $collection): void
    {
        /** @var CalculationVariableContainer $container */
        foreach ($this->containers as $name => $container) {
            /** @var CalculationVariableEntity|null $instance */
            $instance = $collection->getByNameOrInitializeByType(
                $name,
                $container->getType()
            );

            // Some calculation variable cannot have an implemented entity
            // of calculation variable in the development time.
            if ($instance) {
                $container->setInstanceOfEntity($instance);
            }
        }
    }

    /**
     * Fills the containers with the given calculation variable entity.
     * If an instance of an entity does not exist then it will be initialized.
     * The alias of fillWithCalculationVariableEntityCollection().
     *
     * @param  CalculationVariableEntityCollection $collection
     * @return void
     */
    public function fillWithEntities(CalculationVariableEntityCollection $collection): void
    {
        $this->fillWithCalculationVariableEntityCollection($collection);
    }

    /**
     * Adds the given container to the collection.
     *
     * @param  CalculationVariableContainer $container
     * @return void
     */
    public function add(CalculationVariableContainer $container): void
    {
        $this->containers[$container->getName()] = $container;
    }

    /**
     * Returns a container by the given name.
     *
     * @param  string $name
     * @return CalculationVariableContainer|null
     */
    public function get(string $name): ?CalculationVariableContainer
    {
        return $this->containers[$name] ?? null;
    }

    /**
     * Checks whether the collection has an calculation variable with the given name.
     *
     * @param  string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->containers[$name]);
    }

    /**
     * Filters the containers by the given names.
     * Returns the filtered collection.
     *
     * @param  string ...$name
     * @return CalculationVariableContainerCollection
     */
    public function filterByName(string ...$name): self
    {
        $target = array_filter($this->containers, function (CalculationVariableContainer $container) use ($name) {
            return array_search($container->getName(), $name, true);
        });

        return new self($target);
    }

    /**
     * Filters the containers by having entity.
     * Returns containers which have entity.
     *
     * @param  bool $hasEntity
     * @return self
     */
    public function filterByHavingEntity(bool $hasEntity = true): self
    {
        $target = array_filter(
            $this->containers,
            function (CalculationVariableContainer $container) use ($hasEntity) {
                return $container->hasInstanceOfEntity() === $hasEntity;
            }
        );

        return new self($target);
    }

    /**
     * Filters the containers by imported settings of calculation variables.
     * Returns containers where settings must be imported from other calculation
     * variables.
     *
     * @param  bool $importSettings
     * @return CalculationVariableContainerCollection
     */
    public function filterByImportSettings(bool $importSettings = true): self
    {
        $target = array_filter(
            $this->containers,
            function (CalculationVariableContainer $container) use ($importSettings) {
                return $container->doesImportSettings() === $importSettings;
            }
        );

        return new self($target);
    }

    /**
     * Filters the containers by variable settings.
     * Returns containers where calculation variables have variable settings.
     *
     * @param  bool $hasVariableSettings
     * @return CalculationVariableContainerCollection
     */
    public function filterByVariableSettings(bool $hasVariableSettings = true): self
    {
        $target = array_filter(
            $this->containers,
            function (CalculationVariableContainer $container) use ($hasVariableSettings) {
                return $container->hasVariableSettings() === $hasVariableSettings;
            }
        );

        return new self($target);
    }

    /**
     * Filters the containers by visibility of settings of calculation variables.
     * Returns containers which have visible settings.
     *
     * @param  bool $visible
     * @return CalculationVariableContainerCollection
     */
    public function filterByVisibleSettings(bool $visible = true): self
    {
        $target = array_filter(
            $this->containers,
            function (CalculationVariableContainer $container) use ($visible) {
                return $container->areSettingsVisible() === $visible;
            }
        );

        return new self($target);
    }

    /**
     * Filters the containers by keeping inself settings.
     * Returns containers where calculation variables keeps themself settings.
     *
     * @param  bool $keep
     * @return CalculationVariableContainerCollection
     */
    public function filterByKeepingItselfSettings(bool $keep = true): self
    {
        $target = array_filter(
            $this->containers,
            function (CalculationVariableContainer $container) use ($keep) {
                return $container->doesKeepItselfSettings() === $keep;
            }
        );

        return new self($target);
    }

    /**
     * Imports settings of calculation variables from one variables to other.
     * Returns a number of importations.
     *
     * @return int
     */
    public function importSettings(): int
    {
        $importations = 0;
        $imported = $this->filterByHavingEntity()->filterByImportSettings();

        /** @var CalculationVariableContainer $container */
        foreach ($imported as $container) {
            $target = $this->get($container->getName());
            $source = $this->get($container->getInstanceOfContents()->getSourceOfImport());

            if ($target && $source) {
                $instanceOfTargetEntity = $target->getInstanceOfEntity();
                $sourceOfTargetEntity = $source->getInstanceOfEntity();
                $instanceOfTargetEntity->fillFromArray($sourceOfTargetEntity->toArray());
                $importations++;
            }
        }

        return $importations;
    }

    /**
     * Applies variables settings from the variables of contents to
     * other calculation variables.
     * Returns a number of applied settings.
     *
     * @return int
     */
    public function applyVariableSettings(): int
    {
        $applied = 0;
        $containers = $this->filterByHavingEntity()->filterByVariableSettings();

        /** @var CalculationVariableContainer $container */
        foreach ($containers as $container) {
            if ($container->hasVariableSettings()) {
                $target = $this->get($container->getName());
                $instanceOfTargetEntity = $target ? $target->getInstanceOfEntity() : null;

                if (
                    $instanceOfTargetEntity
                    && $instanceOfTargetEntity instanceof EntityWithVariableSettings
                ) {
                    $instanceOfTargetEntity->setVariableSettings(
                        $container->getInstanceOfContents()->getVariableSettings()
                    );
                    $applied++;
                }
            }
        }

        return $applied;
    }

    /**
     * Returns names of the containers.
     *
     * @return array|string[]
     */
    public function getNames(): array
    {
        return $this->keys();
    }

    /**
     * Returns an array with the current containers.
     *
     * @return array|CalculationVariableContainer[]
     */
    public function getCalculationVariableContainers(): array
    {
        return $this->containers;
    }

    /**
     * Returns calculation variable of contents from the collection.
     *
     * @return CalculationVariableOfContentsCollection
     */
    public function getCalculationVariableOfContentsCollection(): CalculationVariableOfContentsCollection
    {
        $instances = array_map(function (CalculationVariableContainer $container) {
            return $container->getInstanceOfContents();
        }, $this->containers);

        return new CalculationVariableOfContentsCollection($instances);
    }

    /**
     * Returns calculation variable entities from the collection.
     *
     * @return CalculationVariableEntityCollection
     */
    public function getCalculationVariableEntityCollection(): CalculationVariableEntityCollection
    {
        $instances = array_map(function (CalculationVariableContainer $container) {
            return $container->getInstanceOfEntity();
        }, $this->containers);

        return new CalculationVariableEntityCollection($instances);
    }

    /**
     * Returns an array of the collection.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (CalculationVariableContainer $container) {
            return $container->toArray();
        }, $this->containers);
    }
}
