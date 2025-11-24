<?php

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Exceptions\InvalidTypeException;

/**
 * @internal
 */
final class ZddPropertyExtractor
{
    /**
     * @param class-string $className
     *
     * @throws InvalidTypeException
     * @throws \ReflectionException
     */
    public function extractPropertiesFromClass(string $className): PropertyList
    {
        $reflectionClass = new \ReflectionClass($className);

        $propertyList = new PropertyList();

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $propertyType = $property->getType();

            if (null === $propertyType) {
                throw InvalidTypeException::typeMissing($propertyName, $className);
            }

            if (!$propertyType instanceof \ReflectionNamedType) {
                throw InvalidTypeException::typeNotSupported();
            }

            $propertyList->addProperty(
                new Property(
                    $propertyName,
                    $propertyType->getName(),
                    $propertyType->allowsNull(),
                ),
            );
        }

        return $propertyList;
    }
}
