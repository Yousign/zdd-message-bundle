<?php

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Exceptions\InvalidTypeException;
use Yousign\ZddMessageBundle\Exceptions\MissingValueForTypeException;

/**
 * @internal
 */
final class ZddPropertyExtractor
{
    public function __construct(private readonly ZddMessageConfigInterface $config)
    {
    }

    /**
     * @param class-string $className
     *
     * @throws InvalidTypeException
     * @throws MissingValueForTypeException
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

            $typeHint = $propertyType->getName();
            $value = $propertyType->allowsNull() ? null : $this->generateFakeValueFromType($typeHint, $property);
            $propertyList->addProperty(new Property($propertyName, $typeHint, $value));
        }

        return $propertyList;
    }

    /**
     * @throws MissingValueForTypeException
     */
    private function generateFakeValueFromType(string $typeHint, \ReflectionProperty $property): mixed
    {
        $value = $this->config->generateValueForCustomPropertyType($typeHint, $property);
        if (null !== $value) {
            return $value;
        }

        return match ($typeHint) {
            'string' => 'Hello World!',
            'int' => 42,
            'float' => 42.42,
            'bool' => true,
            'array' => ['PHP', 'For The Win'],
            default => throw MissingValueForTypeException::missingValue($typeHint, $this->config),
        };
    }
}
