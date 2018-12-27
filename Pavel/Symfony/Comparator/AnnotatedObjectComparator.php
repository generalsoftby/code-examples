<?php

namespace Adticket\Elvis\ContactBundle\Comparator;

use Adticket\Elvis\ContactBundle\Annotation\Comparable;

use Doctrine\Common\Annotations\Reader;

/**
 * Compares two objects using properties annotated with @Comparable annotation
 */
class AnnotatedObjectComparator implements ObjectComparatorInterface
{
    const GETTER_PREFIXES = [
        'get', 'is', 'has',
    ];

    /**
     * @var Reader
     */
    private $reader;

    /**
     * AnnotatedObjectComparator constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @inheritdoc
     */
    public function isEqual($new, $old)
    {
        $diff = $this->getDiff($new, $old);

        return count($diff) === 0;
    }

    /**
     * @inheritdoc
     */
    public function getDiff($new, $old)
    {
        $diff = [];

        $class = new \ReflectionClass($new);

        $properties = $class->getProperties();

        foreach ($properties as $property) {
            /** @var Comparable $annotation */
            $annotation = $this->reader->getPropertyAnnotation($property, Comparable::class);

            if (!$annotation) {
                continue;
            }

            $comparator = $annotation->getComparator();
            $getter = $this->getGetterName($new, $property);

            if (is_null($old) || !$comparator->isEqual($new->$getter(), $old->$getter())) {
                $diff[$property->getName()] = [
                    'old' => $old ? $old->$getter() : null,
                    'new' => $new->$getter(),
                ];
            }
        }

        return $diff;
    }

    /**
     * @param object              $object
     * @param \ReflectionProperty $property
     *
     * @return string
     *
     * @throws \InvalidArgumentException if the class has no accessible getter for the property.
     */
    protected function getGetterName($object, \ReflectionProperty $property)
    {
        $name = ucfirst($property->getName());

        foreach (self::GETTER_PREFIXES as $prefix) {
            if (method_exists($object, $prefix . $name)) {
                return $prefix . $name;
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Property %s in class %s has no getter', $property->getName(), get_class($object))
        );
    }
}
