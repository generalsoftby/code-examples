<?php

namespace Adticket\Elvis\ContactBundle\Comparator;

use Adticket\Elvis\ContactBundle\Annotation\ComparableArray;

/**
 * Compares two arrays.
 */
class ArrayComparator
{

    /**
     * @var string
     */
    private $type;

    /**
     * ArrayComparator constructor.
     *
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Compares two arrays.
     *
     * @param array|object $array1
     * @param array|object $array2
     *
     * @return bool
     *
     * @throws \InvalidArgumentException when the argument cannot be converted to an array.
     */
    public function isEqual($array1, $array2)
    {
        if (count($array1) !== count($array2)) {
            return false;
        }

        $array1 = $this->toArray($array1);
        $array2 = $this->toArray($array2);

        $diff = [];

        if ($this->getType() === ComparableArray::VALUE_SCALAR) {
            $diff = array_diff($array1, $array2);
        } else {
            $encodedArray1 = json_encode($array1);
            $encodedArray2 = json_encode($array2);

            return $encodedArray1 === $encodedArray2;
        }

        return count($diff) === 0;
    }

    /**
     * @param mixed $arg
     *
     * @return array
     *
     * @throws \InvalidArgumentException when the argument cannot be converted to an array.
     */
    protected function toArray($arg)
    {
        if (is_array($arg)) {
            return $arg;
        }

        if (!is_object($arg)) {
            throw new \InvalidArgumentException(sprintf('Cannot convert %s to an array.', $arg));
        }

        if (!method_exists($arg, 'toArray')) {
            throw new \InvalidArgumentException(sprintf('Cannot convert %s to an array', get_class($arg)));
        }

        return $arg->toArray();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
