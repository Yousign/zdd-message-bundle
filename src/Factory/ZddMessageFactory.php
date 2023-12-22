<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Factory;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;

/**
 * @internal
 */
final class ZddMessageFactory
{
    private ZddPropertyExtractor $propertyExtractor;
    private ?SerializerInterface $messengerSerializer;

    public function __construct(private readonly ZddMessageConfigInterface $config, SerializerInterface $messengerSerializer = null)
    {
        $this->propertyExtractor = new ZddPropertyExtractor($this->config);
        $this->messengerSerializer = $messengerSerializer;
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

        $serializedMessage = null;
        if($this->messengerSerializer){
            $encodedEnvelope = $this->messengerSerializer->encode(Envelope::wrap($message));
            $serializedMessage = \json_encode($encodedEnvelope);
        }

        return new ZddMessage($className, $serializedMessage ?? serialize($message), $propertyList, $message);
    }

    private function forcePropertyValue(object $object, string $property, mixed $value): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);

        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
