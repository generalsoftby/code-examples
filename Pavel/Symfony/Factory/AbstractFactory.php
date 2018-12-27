<?php

namespace App\Factory;

use Faker\Factory;
use Faker\Generator;

/**
 *
 */
abstract class AbstractFactory
{
    /** @var Generator */
    protected $faker;

    /**
     * @param string $fakerLocale
     */
    public function __construct(string $fakerLocale)
    {
        $this->faker = Factory::create($fakerLocale);
    }

    /**
     * @param int $count
     * @param array $defaults
     *
     * @return array
     */
    public function createMany(int $count, array $defaults = [])
    {
        $result = [];
        for ($i = 0; $i < $count; ++$i) {
            $object = $this->create($defaults);
            $result[] = $object;
        }

        return $result;
    }

    /**
     * @param Generator $faker
     *
     * @return AbstractFactory
     */
    public function setFaker(Generator $faker)
    {
        $this->faker = $faker;

        return $this;
    }

    /**
     * @param $object
     * @param array $defaults
     *
     * @return mixed
     */
    protected function mergeWithDefault($object, array $defaults)
    {
        foreach ($defaults as $key => $value) {
            $setter = 'set' . $key;

            if ($value instanceof \Closure) {
                $value = $value($this->faker, $object);
            }

            $object->$setter($value);
        }

        return $object;
    }

    /**
     * @param array $defaults
     *
     * @return mixed
     */
    protected abstract function create(array $defaults = []);

}
