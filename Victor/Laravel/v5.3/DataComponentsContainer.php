<?php

namespace App\Services\Notification\DataComponents;

use App\Services\Notification\DataComponents\Components\AbstractEventDataComponent;

class DataComponentsContainer
{
    /** @var AbstractEventDataComponent [] */
    protected $dataComponents = [];

    /**
     * @param $componentType
     * @return bool
     */
    public function has($componentType)
    {
        return isset($this->dataComponents[$componentType]);
    }

    /**
     * @param AbstractEventDataComponent $eventDataComponent
     */
    public function add(AbstractEventDataComponent $eventDataComponent)
    {
        $this->dataComponents[$eventDataComponent->getComponentType()] = $eventDataComponent;
    }

    /**
     * @param array $eventDataComponents
     * @internal param AbstractEventDataComponent $eventDataComponent
     */
    public function addMany(array $eventDataComponents)
    {
        foreach ($eventDataComponents as $eventDataComponentItem)
        {
            $this->add($eventDataComponentItem);
        }
    }

    /**
     * @param $componentType
     * @return mixed|null
     */
    public function get($componentType)
    {
        return in_array($componentType, DataComponentTypesEnum::getAll()) ?
            array_get($this->dataComponents, $componentType, null) :
            null;
    }

    /**
     * @return AbstractEventDataComponent []
     */
    public function getAll(): array
    {
        return $this->dataComponents;
    }

}