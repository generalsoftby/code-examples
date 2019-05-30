<?php

namespace Adticket\Elvis\ContactBundle\Annotation;

use Adticket\Elvis\ContactBundle\Comparator\ScalarComparator;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("PROPERTY")
 */
class Comparable
{
    /**
     * Comparable constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return ScalarComparator
     */
    public function getComparator()
    {
        return new ScalarComparator();
    }
}
