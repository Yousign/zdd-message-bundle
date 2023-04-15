<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;

/**
 * @internal
 */
final class ZddMessageFactory
{
    private ZddPropertyExtractor $propertyExtractor;

    public function __construct(private readonly ZddMessageConfigInterface $config)
    {
        $this->propertyExtractor = new ZddPropertyExtractor($this->config);
    }

    /**
     * @param class-string $className
     */
    public function create(string $className): ZddMessage
    {
        $propertyList = $this->propertyExtractor->extractPropertiesFromClass($className);

        $message = (new \ReflectionClass($className))->newInstanceWithoutConstructor();
        foreach ($propertyList->getProperties() as $property => $value) {
            $this->forcePropertyValue($message, $property, $value);
        }

        return new ZddMessage($className, serialize($message), $propertyList->getNotNullableProperties());
    }

    private function forcePropertyValue(object $object, string $property, mixed $value): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);

        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
