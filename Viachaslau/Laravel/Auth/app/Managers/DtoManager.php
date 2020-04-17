<?php


namespace App\Managers\Core;


use App\Annotations\Dto;
use Doctrine\Common\Annotations\AnnotationReader;
use Illuminate\Support\Arr;
use Symfony\Component\Validator\Validation;

class DtoManager extends AbstractManager
{
    private $validator;

    public function __construct()
    {
        $reader = new AnnotationReader();
        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping($reader)->getValidator();
    }

    public function generateByRequest($class, array $data)
    {
        $object = new $class();
        $reflectionObject = new \ReflectionObject($object);
        $reader = new AnnotationReader();

        foreach ($reflectionObject->getProperties(\ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PUBLIC) as $property) {
            /** @var Dto\Request\AbstractPropertyAnnotation $annotation */
            $annotation = $reader->getPropertyAnnotation($property, Dto\Request\AbstractPropertyAnnotation::class);
            $propertyName = $property->getName();
            $propertyValue = Arr::get($data, $propertyName);

            if ($annotation) {
                $propertyValue = $annotation->generateObject($propertyValue);
            }

            $object->__set($propertyName, $propertyValue);
        }

        return $object;
    }

    public function convertPropertyValuesToDto(object $dto, array $dtoClassesByProperty)
    {
        $reflectionObject = new \ReflectionObject($dto);
        foreach ($dtoClassesByProperty as $propertyName => $dtoClass) {
            $property = $reflectionObject->getProperty($propertyName);
            $property->setAccessible(true);
            $propertyValue = $property->getValue($dto);
            if (is_array($propertyValue)){
                $result = [];
                foreach ($propertyValue as $item){
                    $result[] = $dtoClass::convertToDto($item);
                }
                $propertyValue = $result;
            }else{
                $propertyValue = $dtoClass::convertToDto($propertyValue);
            }
            $property->setValue($dto, $propertyValue);
        }
    }

    /**
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    public function getValidator()
    {
        return $this->validator;
    }
}
