<?php

namespace Yousign\ZddMessageBundle\Utils;

use Yousign\ZddMessageBundle\Exceptions\InvalidTypeException;
use Yousign\ZddMessageBundle\ZddMessageConfigInterface;

class ZddParameterExtractor
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
     */
    public function extractParametersFromClass(string $className): ParameterList
    {
        $reflectionClass = new \ReflectionClass($className);

        $expectedProperties = $reflectionClass->getProperties();
        $params = new ParameterList();

        foreach ($expectedProperties as $expectedProperty) {
            $propertyName = $expectedProperty->getName();
            $propertyType = $expectedProperty->getType();

            if (null === $propertyType) {
                throw InvalidTypeException::typeMissing($propertyName, $className);
            }

            if ($propertyType->allowsNull()) {
                $params->add($propertyName);

                continue;
            }
            if (!$propertyType instanceof \ReflectionNamedType) {
                throw InvalidTypeException::typeNotSupported();
            }

            $typeHint = $propertyType->getName();
            $params->add($propertyName, $this->doGetValue($typeHint), $typeHint);
        }

        return $params;
    }

    private function doGetValue(string $typeHint): mixed
    {
        if (array_key_exists($typeHint, self::SCALAR_VALUES)) {
            return self::SCALAR_VALUES[$typeHint];
        }

        return $this->config->getValue($typeHint);
    }
}
