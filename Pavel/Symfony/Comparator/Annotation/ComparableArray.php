<?php

namespace Adticket\Elvis\ContactBundle\Annotation;

use Adticket\Elvis\ContactBundle\Comparator\ArrayComparator;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class ComparableArray extends Comparable
{

    const VALUE_SCALAR = 'scalar';

    const VALUE_ARRAY = 'array';

    /**
     * @var string
     */
    private $type;

    /**
     * ComparableArray constructor.
     *
     * @param string $args
     */
    public function __construct($args)
    {
        $this->type = isset($args['value']) ? $args['value'] : self::VALUE_SCALAR;
    }

    /**
     * @return ArrayComparator
     */
    public function getComparator()
    {
        return new ArrayComparator($this->type);
    }
}
