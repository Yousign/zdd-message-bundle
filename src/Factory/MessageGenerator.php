<?php

declare(strict_types=1);

namespace Youtrust\ZddMessageBundle\Factory;

use Youtrust\ZddMessageBundle\Config\CustomMessageGeneratorInterface;
use Youtrust\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Youtrust\ZddMessageBundle\Exceptions\MissingValueForTypeException;

/**
 * @internal
 */
final class MessageGenerator
{
    private readonly ZddPropertyExtractor $propertyExtractor;

    public function __construct(private readonly ZddMessageConfigInterface $config)
    {
        $this->propertyExtractor = new ZddPropertyExtractor();
    }

    /**
     * @param class-string $className
     */
    public function generate(string $className): object
    {
        if ($this->config instanceof CustomMessageGeneratorInterface) {
            $message = $this->config->generateCustomMessage($className);
            if (null !== $message) {
                return $message;
            }
        }

        $message = (new \ReflectionClass($className))->newInstanceWithoutConstructor();
        $propertyList = $this->propertyExtractor->extractPropertiesFromClass($className);

        foreach ($propertyList->getProperties() as $property) {
            $value = $property->isNullable ? null : $this->generateValueForProperty($property);
            $this->forcePropertyValue($message, $property->name, $value);
        }

        return $message;
    }

    /**
     * @throws MissingValueForTypeException
     */
    private function generateValueForProperty(Property $property): mixed
    {
        $value = $this->config->generateValueForCustomPropertyType($property->type);
        if (null !== $value) {
            return $value;
        }

        return match ($property->type) {
            'string' => 'Hello World!',
            'int' => 42,
            'float' => 42.42,
            'bool' => true,
            'array' => ['PHP', 'For The Win'],
            default => throw MissingValueForTypeException::missingValue($property->type, $this->config),
        };
    }

    private function forcePropertyValue(object $object, string $property, mixed $value): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);

        // Readonly properties can only be set on the declaring class in case of inheritance
        if ($reflectionProperty->isReadOnly()) {
            $reflectionProperty = $reflectionProperty->getDeclaringClass()->getProperty($property);
        }

        // No setAccessible() call: it has had no effect since PHP 8.1 and is deprecated in 8.5.
        $reflectionProperty->setValue($object, $value);
    }
}
