<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Utils;

use Yousign\ZddMessageBundle\Exceptions\MissingValueForTypeException;
use Yousign\ZddMessageBundle\ZddMessage;
use Yousign\ZddMessageBundle\ZddMessageConfigInterface;

final class ZddMessageFactory
{
    private ZddParameterExtractor $parameterExtractor;

    public function __construct(private readonly ZddMessageConfigInterface $config)
    {
        $this->parameterExtractor = new ZddParameterExtractor($this->config);
    }

    /**
     * @param class-string $className
     */
    public function create(string $className): ZddMessage
    {
        $params = $this->parameterExtractor->extractParametersFromClass($className);

        $message = (new \ReflectionClass($className))->newInstanceWithoutConstructor();
        foreach ($params->getParameters() as $property => $value) {
            $this->forcePropertyValue($message, $property, $value);
        }

        return new ZddMessage($className, serialize($message), $params->getNotNullableProperties());
    }

    private function forcePropertyValue(object $object, string $property, mixed $value): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);

        $reflectionProperty->setAccessible(true);
        try {
            $reflectionProperty->setValue($object, $value);
        } catch (\TypeError $e) {
            throw MissingValueForTypeException::missingValue($property, $this->config, $e->getCode(), $e);
        }
    }
}
