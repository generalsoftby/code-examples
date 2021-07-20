<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * Keeps states of selected angles.
 */
class FourAngles
{
    /**
     * Types of angles.
     */
    public const LEFT_TOP_ANGLE = 'leftTop';
    public const RIGHT_TOP_ANGLE = 'rightTop';
    public const LEFT_BOTTOM_ANGLE = 'leftBottom';
    public const RIGHT_BOTTOM_ANGLE = 'rightBottom';

    /**
     * A state of the left top angle.
     *
     * @var bool
     */
    protected $leftTop = false;

    /**
     * A state of the right top angle.
     *
     * @var bool
     */
    protected $rightTop = false;

    /**
     * A state of the left bottom angle.
     *
     * @var bool
     */
    protected $leftBottom = false;

    /**
     * A state of the right bottom angle.
     *
     * @var bool
     */
    protected $rightBottom = false;

    /**
     * Checks whether the given array has selected angles.
     * Keys are names of angles, values are states of selection.
     *
     * @param  array $angles
     * @return bool
     */
    public function hasSelectedAngles(array $angles): bool
    {
        foreach ($angles as $angle => $state) {
            if ($this->isAllowed($angle) && $state) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the given array has angles and their states.
     * Keys are names of angles, values are states of selection.
     *
     * @param  array $angles
     * @return bool
     */
    public function hasAngles(array $angles): bool
    {
        foreach ($angles as $angle => $state) {
            if ($this->isAllowed($angle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the given name of a angle is allowed.
     *
     * @param  string $name
     * @return bool
     */
    public function isAllowed(string $name): bool
    {
        return in_array($name, $this->getAllowedAngles());
    }

    /**
     * Returns names of allowed angles.
     *
     * @return array
     */
    public function getAllowedAngles(): array
    {
        return [
            self::LEFT_TOP_ANGLE,
            self::RIGHT_TOP_ANGLE,
            self::LEFT_BOTTOM_ANGLE,
            self::RIGHT_BOTTOM_ANGLE,
        ];
    }

    /**
     * Sets states of the given angles with their states.
     * Keys are names of angles, values are states of these angles.
     *
     * @param  array $angles
     * @return void
     */
    public function setAngles(array $angles): void
    {
        if (!$this->hasSelectedAngles($angles)) {
            return;
        }

        foreach ($angles as $angle => $state) {
            $this->setStateOfAngle($angle, $state);
        }
    }

    /**
     * Sets the given state of angles and sets false for undefined angles.
     *
     * @param  array $angles
     * @return void
     */
    public function setAnglesAndFalseForUndefined(array $angles): void
    {
        $this->setStateOfAngle(self::LEFT_TOP_ANGLE, $angles['leftTop'] ?? false);
        $this->setStateOfAngle(self::RIGHT_TOP_ANGLE, $angles['rightTop'] ?? false);
        $this->setStateOfAngle(self::LEFT_BOTTOM_ANGLE, $angles['leftBottom'] ?? false);
        $this->setStateOfAngle(self::RIGHT_BOTTOM_ANGLE, $angles['rightBottom'] ?? false);
    }

    /**
     * Sets the given state to the given angle.
     *
     * @param  string $angle
     * @param  bool   $state
     * @return void
     */
    public function setStateOfAngle(string $angle, bool $state): void
    {
        switch ($angle) {
            case self::LEFT_TOP_ANGLE:
                $this->leftTop = $state;
                break;
            case self::RIGHT_TOP_ANGLE:
                $this->rightTop = $state;
                break;
            case self::LEFT_BOTTOM_ANGLE:
                $this->leftBottom = $state;
                break;
            case self::RIGHT_BOTTOM_ANGLE:
                $this->rightBottom = $state;
                break;
        }
    }

    /**
     * Checks whether the left top angle is selected.
     *
     * @return bool
     */
    public function isLeftTopAngleSelected(): bool
    {
        return $this->leftTop;
    }

    /**
     * Checks whether the right top angle is selected.
     *
     * @return bool
     */
    public function isRightTopAngleSelected(): bool
    {
        return $this->rightTop;
    }

    /**
     * Checks whether the left bottom angle is selected.
     *
     * @return bool
     */
    public function isLeftBottomAngleSelected(): bool
    {
        return $this->leftBottom;
    }

    /**
     * Checks whether the right bottom angle is selected.
     *
     * @return bool
     */
    public function isRightBottomAngleSelected(): bool
    {
        return $this->rightBottom;
    }

    /**
     * Returns states of angles.
     * Keys are names of angles, values are states of these angles.
     *
     * @return array
     */
    public function getStates(): array
    {
        return [
            self::LEFT_TOP_ANGLE => $this->leftTop,
            self::RIGHT_TOP_ANGLE => $this->rightTop,
            self::LEFT_BOTTOM_ANGLE => $this->leftBottom,
            self::RIGHT_BOTTOM_ANGLE => $this->rightBottom,
        ];
    }

    /**
     * Returns names of selected angles.
     *
     * @return array|string[]
     */
    public function getNamesOfSelectedAngles(): array
    {
        $selectedAngles = array_filter($this->getStates());

        return array_keys($selectedAngles);
    }

    /**
     * Counts selected angles.
     *
     * @return int
     */
    public function countSelectedAngles(): int
    {
        $amount = 0;
        $amount += $this->leftTop ? 1 : 0;
        $amount += $this->rightTop ? 1 : 0;
        $amount += $this->leftBottom ? 1 : 0;
        $amount += $this->rightBottom ? 1 : 0;

        return $amount;
    }
}
