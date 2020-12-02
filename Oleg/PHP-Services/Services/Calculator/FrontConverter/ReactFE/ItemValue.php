<?php

namespace App\Services\Calculators\FrontConverter\ReactFE;

/**
 * Keeps data of an item.
 */
class ItemValue extends AbstractResultItem
{
    /**
     * The type of the item.
     *
     * @var string
     */
    protected $type = 'item_value';

    /**
     * A title of the value.
     *
     * @var string|null
     */
    protected $title;

    /**
     * The style of title.
     *
     * @var bool
     */
    protected $boldTitle = false;

    /**
     * A value of the item.
     *
     * @var string|null
     */
    protected $value;

    /**
     * The style of value.
     *
     * @var bool
     */
    protected $boldValue = false;

    /**
     * The state of the left indent.
     *
     * @var bool
     */
    protected $leftIndent = false;

    /**
     * Initializes an instance of the class.
     *
     * @param string $title
     * @param string $value
     * @param bool   $debug
     */
    public function __construct(string $title = '', string $value = '', bool $debug = false)
    {
        $this->title = $title;
        $this->value = $value;
        $this->debug = $debug;
    }

    /**
     * Sets a title and a value.
     *
     * @param  string $title
     * @param  string $value
     * @return void
     */
    public function set(string $title, string $value): void
    {
        $this->title = $title;
        $this->value = $value;
    }

    /**
     * Sets a title.
     *
     * @param  string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Returns a title.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets a state of the bold title.
     *
     * @param  bool $state
     * @return void
     */
    public function setBoldTitle(bool $state = true): void
    {
        $this->boldTitle = $state;
    }

    /**
     * Sets a value.
     *
     * @param  string $value
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * Returns a value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Sets a state of the bold value.
     *
     * @param  bool $state
     * @return void
     */
    public function setBoldValue(bool $state = true): void
    {
        $this->boldValue = $state;
    }

    /**
     * Sets a state of the left indent.
     *
     * @param  bool $state
     * @return void
     */
    public function setLeftIndent(bool $state = true): void
    {
        $this->leftIndent = $state;
    }

    /**
     * Returns a state of the left indent.
     *
     * @return bool
     */
    public function getLeftIndent(): bool
    {
        return $this->leftIndent;
    }

    /**
     * Boldens values.
     *
     * @return void
     */
    public function bolden(): void
    {
        $this->boldTitle = true;
        $this->boldValue = true;
    }

    /**
     * Converts data to the array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'debug' => $this->isDebugging(),
            'title' => $this->title,
            'boldTitle' => $this->boldTitle,
            'value' => $this->value,
            'boldValue' => $this->boldValue,
            'leftIndent' => $this->leftIndent,
            // NOTE: They are not used by the new calculation interface.
            'label' => $this->title,
            'options' => [
                'as_line' => false,
                'as_title' => false,
            ],
        ];
    }
}
