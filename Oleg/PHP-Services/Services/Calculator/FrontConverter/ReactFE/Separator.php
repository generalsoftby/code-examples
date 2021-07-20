<?php

namespace App\Services\Calculators\FrontConverter\ReactFE;

/**
 * It is used to set a separator.
 */
class Separator extends AbstractResultItem
{
    /**
     * The type of the item.
     *
     * @var string
     */
    protected $type = 'separator';

    /**
     * The state of a bold separator.
     *
     * @var bool
     */
    protected $bold = false;

    /**
     * Initializes an instance of the class.
     *
     * @param bool $debug
     */
    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * Boldens the separator.
     *
     * @return void
     */
    public function bolden(): void
    {
        $this->bold = true;
    }

    /**
     * Returns a state of the bold.
     *
     * @return bool
     */
    public function getStateOfBold(): bool
    {
        return $this->bold;
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
            'bold' => $this->bold,
            // NOTE: They are not used by the new calculation interface.
            'label' => '',
            'value' => '',
            'options' => [
                'as_line' => false,
                'as_title' => true,
            ],
        ];
    }
}
