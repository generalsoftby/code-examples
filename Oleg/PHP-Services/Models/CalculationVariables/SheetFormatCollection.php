<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * Contains formats of sheets.
 */
class SheetFormatCollection implements \Countable, \Iterator
{
    /**
     * An array with SheetFormat.
     *
     * @var array|SheetFormat[]
     */
    protected $formats;

    /**
     * A position of the pointer.
     *
     * @var int
     */
    protected $position;

    /**
     * Initializes an instance of the class using sheet formats.
     *
     * @param array|SheetFormat[] $formats
     */
    public function __construct(array $formats = [])
    {
        $this->position = 0;
        $this->setSheetFormats($formats);
    }

    /**
     * Returns a number of formats.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->formats);
    }

    /**
     * Resets the pointer.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Returns a current SheetFormat or null.
     *
     * @return SheetFormat|null
     */
    public function current(): ?SheetFormat
    {
        return $this->formats[$this->position] ?? null;
    }

    /**
     * Returns a key of the pointer.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Moves the pointer further.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Checks whether the current format exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->formats[$this->position]);
    }

    /**
     * Creates a new format and adds its to the collection.
     * Returns the new format.
     *
     * @param  int         $height
     * @param  int         $width
     * @param  string      $name
     * @return SheetFormat
     */
    public function newFormat(int $height, int $width, string $name): SheetFormat
    {
        $format = new SheetFormat($height, $width, $name);

        $this->formats[] = $format;

        return $format;
    }

    /**
     * Sets new sheet formats.
     *
     * @param array|SheetFormat[] $formats
     */
    public function setSheetFormats(array $formats)
    {
        $formats = array_filter($formats, function ($format) {
            return $format instanceof SheetFormat;
        });

        $this->formats = array_values($formats);
    }

    /**
     * Returns an array with SheetFormat.
     *
     * @return array|SheetFormat[]
     */
    public function getSheetFormats(): array
    {
        return $this->formats;
    }

    /**
     * Finds sheet formats.
     *
     * @param  string $index
     * @param  string $name
     * @param  int    $height
     * @param  int    $width
     * @return SheetFormatCollection
     */
    public function find(string $index, string $name, int $height, int $width): self
    {
        if ($index === 'custom') {
            return new self;
        }

        /** @var array|SheetFormat[] $formats **/
        $formats = array_filter($this->formats, function (SheetFormat $format) use ($name, $height, $width) {
            return $name === $format->getName() && $height === $format->getHeight() && $width === $format->getWidth();
        });

        return new self($formats);
    }

    /**
     * Returns data of the instance in the forma of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (SheetFormat $format) {
            return $format->toArray();
        }, $this->formats);
    }
}
