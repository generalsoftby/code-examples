<?php

namespace Adticket\Elvis\ContactBundle\Tests\Service;

use Adticket\Elvis\ContactBundle\Comparator\AnnotatedObjectComparator;
use Adticket\Elvis\ContactBundle\Tests\Service\Dummy\CompareArrayDummy;
use Adticket\Elvis\ContactBundle\Tests\Service\Dummy\CompareDummy;
use Adticket\Elvis\CoreBundle\Tests\TestCase;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @covers \Adticket\Elvis\ContactBundle\Comparator\AnnotatedObjectComparator
 */
class AnnotatedObjectComparatorTest extends TestCase
{

    /**
     *
     */
    public function testEqual()
    {
        $comparator = new AnnotatedObjectComparator(new AnnotationReader());

        $obj1 = new CompareDummy(42, '42', true);
        $obj2 = new CompareDummy(42, '42', true);

        $result = $comparator->isEqual($obj1, $obj2);

        $this->assertTrue($result);
    }

    /**
     *
     */
    public function testNotEqual()
    {
        $comparator = new AnnotatedObjectComparator(new AnnotationReader());

        $obj1 = new CompareDummy(42, '42', true);
        $obj2 = new CompareDummy(43, '42', true);

        $result = $comparator->isEqual($obj1, $obj2);

        $this->assertFalse($result);
    }

    /**
     *
     */
    public function testSecondIsNull()
    {
        $comparator = new AnnotatedObjectComparator(new AnnotationReader());

        $obj1 = new CompareDummy(42, '42', true);
        $obj2 = null;

        $result = $comparator->isEqual($obj1, $obj2);

        $this->assertFalse($result);
    }

    /**
     *
     */
    public function testDiff()
    {
        $comparator = new AnnotatedObjectComparator(new AnnotationReader());

        $obj1 = new CompareDummy(42, '42', true);
        $obj2 = new CompareDummy(43, '43', true);

        $result = $comparator->getDiff($obj1, $obj2);

        $expected = [
            'intValue' =>
            [
                'old' => 43,
                'new' => 42,
            ],
            'stringValue' =>
            [
                'old' => '43',
                'new' => '42',
            ],
        ];

        $this->assertArraySubset($result, $expected);
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @dataProvider getEqualArrays
     */
    public function testEqualArrays($array1, $array2)
    {
        $comparator = new AnnotatedObjectComparator(new AnnotationReader());

        $obj1 = new CompareArrayDummy($array1);
        $obj2 = new CompareArrayDummy($array2);

        $result = $comparator->isEqual($obj1, $obj2);

        $this->assertTrue($result);
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @dataProvider getNotEqualArrays
     */
    public function testNotEqualArrays($array1, $array2)
    {
        $comparator = new AnnotatedObjectComparator(new AnnotationReader());

        $obj1 = new CompareArrayDummy($array1);
        $obj2 = new CompareArrayDummy($array2);

        $result = $comparator->isEqual($obj1, $obj2);

        $this->assertFalse($result);
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @dataProvider getEqualArrays
     */
    public function testEqualArrayCollections($array1, $array2)
    {
        $comparator = new AnnotatedObjectComparator(new AnnotationReader());

        $obj1 = new CompareArrayDummy(new ArrayCollection($array1));
        $obj2 = new CompareArrayDummy(new ArrayCollection($array2));

        $result = $comparator->isEqual($obj1, $obj2);

        $this->assertTrue($result);
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @dataProvider getNotEqualArrays
     */
    public function testNotEqualArrayCollections($array1, $array2)
    {
        $comparator = new AnnotatedObjectComparator(new AnnotationReader());

        $obj1 = new CompareArrayDummy(new ArrayCollection($array1));
        $obj2 = new CompareArrayDummy(new ArrayCollection($array2));

        $result = $comparator->isEqual($obj1, $obj2);

        $this->assertFalse($result);
    }

    /**
     * @return array
     */
    public function getEqualArrays()
    {
        return [
            [[], []],
            [[1, 2, 3], [1, 2, 3]],
            [[1, 2, 3], [3, 2, 1]],
            [[2, 1, 3], [3, 1, 2]],
        ];
    }

    /**
     * @return array
     */
    public function getNotEqualArrays()
    {
        return [
            [[],           [1]],
            [[1],          []],
            [[1, 2, 3],    [1, 2, 3, 4]],
            [[1, 2, 3, 4], [1, 2, 3]],
            [[1, 2, 3],    [4, 2, 1]],
            [[2, 1, 4],    [3, 1, 2]],
        ];
    }
}
