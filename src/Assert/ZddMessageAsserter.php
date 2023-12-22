<?php

namespace Yousign\ZddMessageBundle\Assert;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Yousign\ZddMessageBundle\Factory\Property;
use Yousign\ZddMessageBundle\Factory\PropertyList;

/**
 * @internal
 */
final class ZddMessageAsserter
{
    private ?SerializerInterface $messengerSerializer;

    public function __construct(SerializerInterface $messengerSerialize = null)
    {
        $this->messengerSerializer = $messengerSerialize;
    }

    /**
     * @param class-string<object> $messageFqcn
     */
    public function assert(
        string $messageFqcn,
        string $serializedMessage,
        PropertyList $propertyList
    ): void {
        // ✅ Assert message is unserializable
        if ($this->messengerSerializer) {
            $encodedEnvelope = \json_decode($serializedMessage, true, 512, JSON_THROW_ON_ERROR);
            if (!\is_array($encodedEnvelope)) {
                throw new \LogicException(\sprintf('Unable to decode serialized message to an array : %s', $serializedMessage));
            }
            $decodedEnvelope = $this->messengerSerializer->decode($encodedEnvelope);
            $objectBefore = $decodedEnvelope->getMessage();
        } else {
            $objectBefore = unserialize($serializedMessage);
        }

        if (!$objectBefore instanceof $messageFqcn) {
            throw new \LogicException(\sprintf('Class mismatch between $messageFqcn: "%s" and $serializedMessage: "%s". Please verify your integration.', $messageFqcn, $serializedMessage));
        }

        $reflection = new \ReflectionClass($messageFqcn);
        $reflectionProperties = $reflection->getProperties();

        // ✅ Assert property type hint has not changed and new property have a default value
        foreach ($reflectionProperties as $reflectionProperty) {
            // ✅ Assert error "Typed property Message::$theProperty must not be accessed before initialization".
            $reflectionProperty->getValue($objectBefore); // @phpstan-ignore-line :::  Call to method ReflectionProperty::getValue() on a separate line has no effect.

            // ✅ Assert property
            if ($propertyList->has($reflectionProperty->getName())) {
                self::assertProperty($reflectionProperty, $propertyList->get($reflectionProperty->getName()), $messageFqcn);
                $propertyList->remove($reflectionProperty->getName());
            }
        }

        if (0 !== $propertyList->count()) {
            throw new \LogicException(\sprintf('⚠️ The properties "%s" in class "%s" seems to have been removed', implode(', ', $propertyList->getPropertiesName()), $messageFqcn));
        }
    }

    private static function assertProperty(\ReflectionProperty $reflectionProperty, Property $property, string $messageFqcn): void
    {
        if (null === $reflectionProperty->getType()) {
            throw new \LogicException(\sprintf('$reflectionProperty::getType cannot be null'));
        }
        if (!$reflectionProperty->getType() instanceof \ReflectionNamedType) {
            throw new \LogicException(\sprintf('$reflectionProperty::getType must be an instance of ReflectionNamedType'));
        }
        if ($reflectionProperty->getType()->getName() !== $property->type) {
            throw new \LogicException(\sprintf('Error for property "%s" in class "%s", the type mismatch between the old and the new version of class. Please verify your integration.', $reflectionProperty->getName(), $messageFqcn));
        }
    }
}
