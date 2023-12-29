<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Serializer\SerializerInterface;

/**
 * @internal
 */
final class ZddMessageFactory
{
    private ZddPropertyExtractor $propertyExtractor;

    public function __construct(ZddMessageConfigInterface $config, private readonly SerializerInterface $serializer)
    {
        $this->propertyExtractor = new ZddPropertyExtractor($config);
    }

    /**
     * @param class-string $className
     */
    public function create(string $className): ZddMessage
    {
        $propertyList = $this->propertyExtractor->extractPropertiesFromClass($className);

        $message = (new \ReflectionClass($className))->newInstanceWithoutConstructor();
        foreach ($propertyList->getProperties() as $property) {
            $this->forcePropertyValue($message, $property->name, $property->value);
        }

        $serializedMessage = $this->serializer->serialize($message);

        return new ZddMessage($className, $serializedMessage, $propertyList, $message);
    }

    private function forcePropertyValue(object $object, string $property, mixed $value): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);

        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
