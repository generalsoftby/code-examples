<?php

namespace App\Services\Calculators\FrontConverter\ReactFE;

/**
 * Keeps data of a group title.
 */
class GroupTitle extends AbstractResultItem
{
    /**
     * The type of the item.
     *
     * @var string
     */
    protected $type = 'group_title';

    /**
     * The state of debug.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * A title of a group.
     *
     * @var string|null
     */
    protected $title;

    /**
     * The state of a bold title.
     *
     * @var bool
     */
    protected $bold = false;

    /**
     * The state of alignment.
     *
     * @var bool
     */
    protected $alignCenter = false;

    /**
     * The state of the underline.
     *
     * @var bool
     */
    protected $underline = false;

    /**
     * The state of a bold underline.
     *
     * @var bool
     */
    protected $boldUnderline = false;

    /**
     * The state of the overline.
     *
     * @var bool
     */
    protected $overline = false;

    /**
     * The state of a bold overline.
     *
     * @var bool
     */
    protected $boldOverline = false;

    /**
     * The state of an italic title.
     *
     * @var bool
     */
    protected $italic = false;

    /**
     * Initializes an instance of the class.
     *
     * @param string $title
     * @param bool   $bold
     * @param bool   $debug
     */
    public function __construct(string $title = '', bool $bold = true, bool $debug = false)
    {
        $this->title = $title;
        $this->bold = $bold;
        $this->debug = $debug;
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
     * Boldens the title.
     *
     * @param  bool $state
     * @return void
     */
    public function bolden(bool $state = true): void
    {
        $this->bold = $state;
    }

    /**
     * Aligns the title by left side.
     *
     * @return void
     */
    public function alignLeft(): void
    {
        $this->alignCenter = false;
    }

    /**
     * Aligns the title by center.
     *
     * @return void
     */
    public function alignCenter(): void
    {
        $this->alignCenter = true;
    }

    /**
     * Sets a state of the underline.
     *
     * @param  bool $state
     * @return void
     */
    public function setUnderline(bool $state = true): void
    {
        $this->underline = $state;
    }

    /**
     * Returns a state of the underline.
     *
     * @return bool
     */
    public function getUnderline(): bool
    {
        return $this->underline;
    }

    /**
     * Sets a state of the bold underline.
     *
     * @param  bool $state
     * @return void
     */
    public function setBoldUnderline(bool $state = true): void
    {
        $this->boldUnderline = $state;
    }

    /**
     * Returns a state of the bold underline.
     *
     * @return bool
     */
    public function getBoldUnderline(): bool
    {
        return $this->boldUnderline;
    }

    /**
     * Sets a state of the overline.
     *
     * @param  bool $state
     * @return void
     */
    public function setOverline(bool $state = true): void
    {
        $this->overline = $state;
    }

    /**
     * Sets a state of the bold overline.
     *
     * @param  bool $state
     * @return void
     */
    public function setBoldOverline(bool $state = true): void
    {
        $this->boldOverline = $state;
    }

    /**
     * Returns a state of the bold overline.
     *
     * @return bool
     */
    public function getBoldOverline(): bool
    {
        return $this->boldOverline;
    }

    /**
     * Sets an italic title.
     *
     * @param  bool $state
     * @return void
     */
    public function setItalic(bool $state = true): void
    {
        $this->italic = $state;
    }

    /**
     * Returns a state of the italic title
     *
     * @return bool
     */
    public function getItalic(): bool
    {
        return $this->italic;
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
            'bold' => $this->bold,
            'underline' => $this->underline,
            'boldUnderline' => $this->boldUnderline,
            'overline' => $this->overline,
            'boldOverline' => $this->boldOverline,
            'alignCenter' => $this->alignCenter,
            'italic' => $this->italic,
            // NOTE: They are not used by the new calculation interface.
            'label' => $this->title,
            'value' => '',
            'options' => [
                'as_title' => true,
                'as_line' => false,
            ],
        ];
    }
}
