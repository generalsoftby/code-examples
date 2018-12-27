<?php

namespace Adticket\Elvis\ContactBundle\Comparator;

/**
 * Interface Comparator
 */
interface ObjectComparatorInterface
{
    /**
     * Returns TRUE if the objects are equal.
     *
     * @param object      $new
     * @param object|null $old
     *
     * @return bool
     */
    public function isEqual($new, $old);

    /**
     * Returns an array containing differences between the objects.
     *
     * @param object      $new
     * @param object|null $old
     *
     * @return array
     */
    public function getDiff($new, $old);
}
