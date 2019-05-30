<?php

namespace Adticket\Elvis\ContactBundle\Comparator;

/**
 * Compares two scalar values.
 */
class ScalarComparator
{
    /**
     * Compares two scalar values using equals operator.
     *
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return bool
     */
    public function isEqual($value1, $value2)
    {
        return $value1 === $value2;
    }
}
