<?php

namespace App\Services\Calculators\Blocks;

/**
 * Keeps and handles blocks for visualization.
 */
class BlockCollection implements \Countable, \Iterator
{
    /**
     * An array with blocks.
     *
     * @var array|Block[]
     */
    protected $blocks = [];

    /**
     * Initializes an instances with blocks.
     *
     * @param array|Block[] $blocks
     */
    public function __construct(array $blocks = [])
    {
        array_walk($blocks, function (Block $block) {
            $this->add($block);
        });
    }

    /**
     * Initializes an instance using an array.
     *
     * @param  array|array[] $array
     * @return BlockCollection
     */
    public static function createFromArray(array $array): self
    {
        $blocks = array_map(function (array $block) {
            return Block::createFromArray($block);
        }, $array);

        return new self($blocks);
    }

    /**
     * Counts and returns a number of blocks.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->blocks);
    }

    /**
     * Rewinds the pointer.
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->blocks);
    }

    /**
     * Returns the current block.
     *
     * @return Block
     */
    public function current(): Block
    {
        return current($this->blocks);
    }

    /**
     * Returns a current key of the block.
     *
     * @return string|null
     */
    public function key(): ?string
    {
        /** @var string|bool $key */
        $key = key($this->blocks);

        return $key ? $key : null;
    }

    /**
     * Moves the pointer to the next block.
     *
     * @return void
     */
    public function next(): void
    {
        next($this->blocks);
    }

    /**
     * Returns keys of blocks.
     *
     * @return array|string[]
     */
    public function keys(): array
    {
        return array_keys($this->blocks);
    }

    /**
     * Checks whether the current block is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        /** @var Block|bool $block */
        $block = current($this->blocks);

        return $block !== false;
    }

    /**
     * Checks whether a block exists.
     *
     * @param  string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->blocks[$name]);
    }

    /**
     * Adds a new block to the collection.
     * If $replace is true, then same block will be replaced.
     *
     * @param  Block $block
     * @param  bool $replace
     * @return void
     */
    public function add(Block $block, bool $replace = true): void
    {
        if (! $this->exists($block->getName()) || $replace) {
            $this->blocks[$block->getName()] = $block;
        }

        if (!$this->areCalculationVariablesOfContentsAreUnique()) {
            throw new DuplicatedNamesOfCalculationVariablesException($block->getName());
        }
    }

    /**
     * Returns a block by the given name.
     *
     * @param  string $name
     * @return Block|null
     */
    public function get(string $name): ?Block
    {
        return $this->blocks[$name] ?? null;
    }

    /**
     * Returns an array with blocks.
     *
     * @return array|Block[]
     */
    public function getBlocks(): array
    {
        return array_values($this->blocks);
    }

    /**
     * Returns required blocks.
     *
     * @return BlockCollection
     */
    public function getRequiredBlocks(): self
    {
        $blocks = array_filter($this->blocks, function (Block $block) {
            return $block->isRequired();
        });

        return new self($blocks);
    }

    /**
     * Returns keys of required blocks.
     *
     * @return array|string[]
     */
    public function getKeysOfRequiredBlocks(): array
    {
        return $this->getRequiredBlocks()->keys();
    }

    /**
     * Counts required blocks.
     *
     * @return int
     */
    public function countRequiredBlocks(): int
    {
        $number = 0;

        foreach ($this as $block) {
            if ($block->isRequired()) {
                $number++;
            }
        }

        return $number;
    }

    /**
     * Returns names of attribute groups.
     *
     * @return array|string[]
     */
    public function getNamesOfAttributeGroups(): array
    {
        $names = array_map(function (Block $block) {
            return $block->getNameOfAttributeGroup();
        }, $this->blocks);

        return array_values(array_unique(array_filter($names)));
    }

    /**
     * Returns CalculationVariableOfContentsCollection of the groups.
     *
     * @return CalculationVariableOfContentsCollection
     */
    public function getCalculationVariablesOfContentsOfGroups(): CalculationVariableOfContentsCollection
    {
        $collection = new CalculationVariableOfContentsCollection;

        array_walk($this->blocks, function (Block $block) use ($collection) {
            $collection->union($block->getCalculationVariablesOfContents());
        });

        return $collection;
    }

    /**
     * Checks whether names of the calculation variables of contents are unique.
     *
     * @return bool
     */
    public function areCalculationVariablesOfContentsAreUnique(): bool
    {
        $names = [];

        /** @var Block $block */
        foreach ($this->blocks as $block) {
            array_push($names, ...$block->getCalculationVariablesOfContents()->getNames());
        }

        $uniqueNames = array_unique($names);

        return count($names) === count($uniqueNames);
    }

    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (Block $block) {
            return $block->toArray();
        }, $this->getBlocks());
    }
}
