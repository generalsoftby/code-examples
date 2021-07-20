<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * Keeps states of selected sides.
 */
class FourSides
{
    /**
     * Types of sides.
     */
    public const TOP_SIDE = 'top';
    public const RIGHT_SIDE = 'right';
    public const BOTTOM_SIDE = 'bottom';
    public const LEFT_SIDE = 'left';

    /**
     * A state of the top side.
     *
     * @var bool
     */
    protected $top = false;

    /**
     * A state of the right side.
     *
     * @var bool
     */
    protected $right = false;

    /**
     * A state of the bottom side.
     *
     * @var bool
     */
    protected $bottom = false;

    /**
     * A state of the left side.
     *
     * @var bool
     */
    protected $left = false;

    /**
     * Checks whether the given array has selected sides.
     * Keys are names of sides, values are states of selection.
     *
     * @param  array $sides
     * @return bool
     */
    public function hasSelectedSides(array $sides): bool
    {
        foreach ($sides as $side => $state) {
            if ($this->isAllowed($side) && $state) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the given array has sides and their states.
     * Keys are names of sides, values are states of selection.
     *
     * @param  array $sides
     * @return bool
     */
    public function hasSides(array $sides): bool
    {
        foreach ($sides as $side => $state) {
            if ($this->isAllowed($side)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the given name of a side is allowed.
     *
     * @param  string $name
     * @return bool
     */
    public function isAllowed(string $name): bool
    {
        return in_array($name, $this->getAllowedSides());
    }

    /**
     * Returns names of allowed sides.
     *
     * @return array
     */
    public function getAllowedSides(): array
    {
        return [
            self::LEFT_SIDE,
            self::RIGHT_SIDE,
            self::TOP_SIDE,
            self::BOTTOM_SIDE,
        ];
    }

    /**
     * Sets states of the given sides with their states.
     * Keys are names of sides, values are states of these sides.
     *
     * @param  array $sides
     * @return void
     */
    public function setSides(array $sides): void
    {
        if (!$this->hasSelectedSides($sides)) {
            return;
        }

        foreach ($sides as $side => $state) {
            $this->setStateOfSide($side, $state);
        }
    }

    /**
     * Sets the given state of sides and sets false for undefined sides.
     *
     * @param  array $sides
     * @return void
     */
    public function setSidesAndFalseForUndefined(array $sides): void
    {
        $this->setStateOfSide(self::LEFT_SIDE, $sides['left'] ?? false);
        $this->setStateOfSide(self::RIGHT_SIDE, $sides['right'] ?? false);
        $this->setStateOfSide(self::TOP_SIDE, $sides['top'] ?? false);
        $this->setStateOfSide(self::BOTTOM_SIDE, $sides['bottom'] ?? false);
    }

    /**
     * Sets the given state to the given side.
     *
     * @param  string $side
     * @param  bool   $state
     * @return void
     */
    public function setStateOfSide(string $side, bool $state): void
    {
        switch ($side) {
            case self::LEFT_SIDE:
                $this->left = $state;
                break;
            case self::RIGHT_SIDE:
                $this->right = $state;
                break;
            case self::TOP_SIDE:
                $this->top = $state;
                break;
            case self::BOTTOM_SIDE:
                $this->bottom = $state;
                break;
        }
    }

    /**
     * Checks if the top side is selected.
     *
     * @return bool
     */
    public function isTopSideSelected(): bool
    {
        return $this->top;
    }

    /**
     * Checks if the right side is selected.
     *
     * @return bool
     */
    public function isRightSideSelected(): bool
    {
        return $this->right;
    }

    /**
     * Checks if the bottom side is selected.
     *
     * @return bool
     */
    public function isBottomSideSelected(): bool
    {
        return $this->bottom;
    }

    /**
     * Checks if the left side is selected.
     *
     * @return bool
     */
    public function isLeftSideSelected(): bool
    {
        return $this->left;
    }

    /**
     * Returns states of sides.
     * Keys are names of sides, values are states of these sides.
     *
     * @return array
     */
    public function getStates(): array
    {
        return [
            self::LEFT_SIDE => $this->left,
            self::RIGHT_SIDE => $this->right,
            self::TOP_SIDE => $this->top,
            self::BOTTOM_SIDE => $this->bottom,
        ];
    }

    /**
     * Returns names of selected sides.
     *
     * @return array|string[]
     */
    public function getNamesOfSelectedSides(): array
    {
        $selectedSides = array_filter($this->getStates());

        return array_keys($selectedSides);
    }

    /**
     * Counts selected sides.
     *
     * @return int
     */
    public function countSelectedSides(): int
    {
        $amount = 0;
        $amount += $this->left ? 1 : 0;
        $amount += $this->right ? 1 : 0;
        $amount += $this->top ? 1 : 0;
        $amount += $this->bottom ? 1 : 0;

        return $amount;
    }
}
