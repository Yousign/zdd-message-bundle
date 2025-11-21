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
    private ZddMessageConfigInterface $config;
    private ZddPropertyExtractor $propertyExtractor;

    public function __construct(ZddMessageConfigInterface $config, private readonly SerializerInterface $serializer)
    {
        $this->config = $config;
        $this->propertyExtractor = new ZddPropertyExtractor($config);
    }

    /**
     * @param class-string $className
     */
    public function create(string $className): ZddMessage
    {
        try {
            $propertyList = $this->propertyExtractor->extractPropertiesFromClass($className);

            $message = $this->config->generateCustomMessage($className);
            if (null === $message) {
                $message = (new \ReflectionClass($className))->newInstanceWithoutConstructor();
                foreach ($propertyList->getProperties() as $property) {
                    $this->forcePropertyValue($message, $property->name, $property->value);
                }
            }

            $serializedMessage = $this->serializer->serialize($message);
        } catch (\Throwable $e) {
            throw new \LogicException('Unable to create ZddMessage for class "'.$className.'"', previous: $e);
        }

        return new ZddMessage($className, $serializedMessage, $propertyList, $message);
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
