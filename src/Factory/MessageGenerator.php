<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Config\CustomMessageGeneratorInterface;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Exceptions\MissingValueForTypeException;

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

        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
