<?php

namespace App\Services\Calculators\Blocks;

/**
 * Keeps calculation variables of contents.
 */
class CalculationVariableOfContentsCollection implements \Countable, \Iterator
{
    /**
     * An array with calculation variables of contents.
     *
     * @var array|CalculationVariableOfContents[]
     */
    protected $variables = [];

    /**
     * Initializes an instance with calculation variables of contents.
     *
     * @param array|CalculationVariableOfContents[] $variables
     */
    public function __construct(array $variables = [])
    {
        array_walk($variables, function (CalculationVariableOfContents $variable) {
            $this->add($variable);
        });
    }

    /**
     * Initializes an instance using an array with calculation variables of contents.
     * It uses only names of calculation variables for the initialization.
     *
     * @param  array|array[] $array
     * @return CalculationVariableOfContentsCollection
     */
    public static function createFromArray(array $array): self
    {
        $variables = [];

        foreach ($array as $variable) {
            if (gettype($variable) === 'string') {
                $variable = [
                    'name' => $variable,
                ];
            }

            // Uses the name as the type when it is not defined.
            if (empty($variable['type'])) {
                $variable['type'] = $variable['name'];
            }

            $variables[$variable['name']] = CalculationVariableOfContents::createFromArray($variable);
        }

        return new self($variables);
    }

    /**
     * Counts and returns a number of calculation variables.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->variables);
    }

    /**
     * Rewinds the pointer.
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->variables);
    }

    /**
     * Returns the current calculation variables.
     *
     * @return CalculationVariableOfContents|null
     */
    public function current(): ?CalculationVariableOfContents
    {
        /** @var CalculationVariableOfContents|bool $variable */
        $variable = current($this->variables);

        return $variable !== false ? $variable : null;
    }

    /**
     * Returns a current key of the calculation variable.
     *
     * @return string|null
     */
    public function key(): ?string
    {
        /** @var string|bool $key */
        $key = key($this->variables);

        return $key ? $key : null;
    }

    /**
     * Moves the pointer to the next calculation variable.
     *
     * @return void
     */
    public function next(): void
    {
        next($this->variables);
    }

    /**
     * Checks whether the current calculation variable is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() !== null;
    }

    /**
     * Checks whether the collection has a calculation variable
     * with the given name.
     *
     * @param  string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->variables[$name]);
    }

    /**
     * Adds a calculation variable to the collection.
     * If $replace is true, then same variable will be replaced.
     *
     * @param  CalculationVariableOfContents $variable
     * @param  bool $replace
     * @return void
     */
    public function add(CalculationVariableOfContents $variable, bool $replace = true): void
    {
        if (! $this->has($variable->getName()) || $replace) {
            $this->variables[$variable->getName()] = $variable;
        }
    }

    /**
     * Returns a calculation varibale of contents by the given name.
     *
     * @param  string $name
     * @return CalculationVariableOfContents|null
     */
    public function get(string $name): ?CalculationVariableOfContents
    {
        return $this->variables[$name] ?? null;
    }

    /**
     * Unions the current collection with the given collection.
     *
     * @param  CalculationVariableOfContentsCollection $collection
     * @return void
     */
    public function union(self $collection): void
    {
        $this->variables += $collection->getCalculationVariablesOfContents();
    }

    /**
     * Filters items by the given state of visibility of settings.
     * Returns the new collection.
     *
     * @param  bool $visibleSettings
     * @return CalculationVariableOfContentsCollection
     */
    public function filterByVisibleSettings(bool $visibleSettings = true): self
    {
        /** @var array|CalculationVariableOfContents[] $target */
        $target = array_filter($this->variables, function (CalculationVariableOfContents $variable) use ($visibleSettings) {
            return $variable->areSettingsVisible() === $visibleSettings;
        });

        return new self($target);
    }

    /**
     * Filters items by the given state of imported settings.
     * Returns the new collection.
     *
     * @param  bool $importedSettings
     * @return CalculationVariableOfContentsCollection
     */
    public function filterByImportedSettings(bool $importedSettings = true): self
    {
        /** @var array|CalculationVariableOfContents[] $target */
        $target = array_filter($this->variables, function (CalculationVariableOfContents $variable) use ($importedSettings) {
            return $variable->doesImportSettings() === $importedSettings;
        });

        return new self($target);
    }

    /**
     * Returns names of calculation variables.
     *
     * @return array|string[]
     */
    public function getNames(): array
    {
        return array_keys($this->variables);
    }

    /**
     * Returns an array with CalculationVariableOfContents.
     *
     * @return array|CalculationVariableOfContents[]
     */
    public function getCalculationVariablesOfContents(): array
    {
        return $this->variables;
    }

    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $variablesOfContents = [];

        /** @var CalculationVariableOfContents $variable */
        foreach ($this->variables as $variable) {
            $variablesOfContents[$variable->getName()] = $variable->toArray();
        }

        return $variablesOfContents;
    }
}
