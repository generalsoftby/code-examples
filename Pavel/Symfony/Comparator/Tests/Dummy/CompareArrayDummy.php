<?php

namespace Adticket\Elvis\ContactBundle\Tests\Service\Dummy;

use Adticket\Elvis\ContactBundle\Annotation\ComparableArray;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class to use for comparison
 */
class CompareArrayDummy
{
    /**
     * @var array|ArrayCollection
     *
     * @ComparableArray()
     */
    private $arrayValue;

    /**
     * @param array|ArrayCollection $arrayValue
     */
    public function __construct($arrayValue)
    {
        $this->arrayValue = $arrayValue;
    }

    /**
     * @return array|ArrayCollection
     */
    public function getArrayValue()
    {
        return $this->arrayValue;
    }

    /**
     * @param array|ArrayCollection $arrayValue
     *
     * @return CompareArrayDummy
     */
    public function setArrayValue($arrayValue)
    {
        $this->arrayValue = $arrayValue;

        return $this;
    }
}
