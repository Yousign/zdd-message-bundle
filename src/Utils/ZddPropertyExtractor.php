<?php

namespace Yousign\ZddMessageBundle\Utils;

use Yousign\ZddMessageBundle\Exceptions\InvalidTypeException;
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

    private function generateFakeValueFromType(string $typeHint): mixed
    {
        if (array_key_exists($typeHint, self::SCALAR_VALUES)) {
            return self::SCALAR_VALUES[$typeHint];
        }

        return $this->config->getValue($typeHint);
    }
}
