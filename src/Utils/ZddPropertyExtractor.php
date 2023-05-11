<?php

namespace Yousign\ZddMessageBundle\Utils;

use Yousign\ZddMessageBundle\Exceptions\InvalidTypeException;
use Yousign\ZddMessageBundle\Exceptions\MissingValueForTypeException;
use Yousign\ZddMessageBundle\ZddMessageConfigInterface;

final class ZddPropertyExtractor
{
    private const SCALAR_VALUES = [
        'string' => 'Hello World!',
        'int' => 42,
        'float' => 42.42,
        'bool' => true,
        'array' => ['PHP', 'For The Win'],
    ];

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

            if ($propertyType->allowsNull()) {
                $propertyList->addNullableProperty($propertyName);

                continue;
            }

            if (!$propertyType instanceof \ReflectionNamedType) {
                throw InvalidTypeException::typeNotSupported();
            }

            $typeHint = $propertyType->getName();
            $propertyList->addProperty($propertyName, $typeHint, $this->generateFakeValueFromType($typeHint));
        }

        return $propertyList;
    }

    /**
     * @throws MissingValueForTypeException
     */
    private function generateFakeValueFromType(string $typeHint): mixed
    {
        $values = $this->config->getCustomValueForPropertyType() + self::SCALAR_VALUES;
        if (array_key_exists($typeHint, $values)) {
            return $values[$typeHint];
        }

        throw MissingValueForTypeException::missingValue($typeHint, $this->config);
    }
}
