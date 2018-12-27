<?php

namespace Adticket\Elvis\ContactBundle\Tests\Service\Dummy;

use Adticket\Elvis\ContactBundle\Annotation\Comparable;

/**
 * Class to use for comparison
 */
class CompareDummy
{
    /**
     * @var int
     *
     * @Comparable()
     */
    private $intValue;

    /**
     * @var string
     *
     * @Comparable()
     */
    private $stringValue;

    /**
     * @var bool
     *
     * @Comparable()
     */
    private $boolValue;

    /**
     * CompareDummy constructor.
     * @param int    $intValue
     * @param string $stringValue
     * @param bool   $boolValue
     */
    public function __construct($intValue, $stringValue, $boolValue)
    {
        $this->intValue = $intValue;
        $this->stringValue = $stringValue;
        $this->boolValue = $boolValue;
    }

    /**
     * @return int
     */
    public function getIntValue()
    {
        return $this->intValue;
    }

    /**
     * @param int $intValue
     *
     * @return CompareDummy
     */
    public function setIntValue($intValue)
    {
        $this->intValue = $intValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * @param string $stringValue
     *
     * @return CompareDummy
     */
    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBoolValue()
    {
        return $this->boolValue;
    }

    /**
     * @param bool $boolValue
     *
     * @return CompareDummy
     */
    public function setBoolValue($boolValue)
    {
        $this->boolValue = $boolValue;

        return $this;
    }
}
