<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Assert;

use Yousign\ZddMessageBundle\Factory\Property;
use Yousign\ZddMessageBundle\Factory\ZddMessage;
use Yousign\ZddMessageBundle\Serializer\MessageSerializerInterface;
use Yousign\ZddMessageBundle\Serializer\UnableToDeserializeException;

/**
 * @internal
 */
final class ZddMessageAsserter
{
    public function __construct(
        private readonly MessageSerializerInterface $messageSerializer,
    ) {
    }

    /**
     * @throws UnableToDeserializeException
     */
    public function assert(
        object $messageInstance,
        ZddMessage $message,
    ): void {
        // ✅ Assert message is unserializable
        /** @var object $objectBefore */
        $objectBefore = $this->messageSerializer->deserialize($message->serializedMessage);

        if ($objectBefore::class !== $messageInstance::class) {
            throw new \LogicException(sprintf('Class mismatch between $messageFqcn: "%s" and $serializedMessage: "%s". Please verify your integration.', $messageInstance::class, $objectBefore::class));
        }

        $properties = $message->properties;

        $this->assertProperties($objectBefore, $properties);

        if ([] !== $properties) {
            throw new \LogicException(sprintf('⚠️ The properties "%s" in class "%s" seems to have been removed', implode(', ', Property::getPropertyNames($properties)), $objectBefore::class));
        }
    }

    /**
     * @param Property[] $properties
     */
    private function assertProperties(object $object, array &$properties): void
    {
        $reflectionClass = new \ReflectionClass($object);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $value = $reflectionProperty->getValue($object);

            if (null === $reflectionProperty->getType()) { // Same check as in ZddPropertyExtractor
                continue;
            }

            // ✅ Assert property
            foreach ($properties as $key => $p) {
                if ($p->name === $reflectionProperty->getName()) {
                    $propertyIndex = $key;
                    $property = $p;
                }
            }

            if (!isset($property, $propertyIndex)) {
                throw new \LogicException(sprintf('Unable to find %s property in ZddMessage properties', $reflectionProperty->getName()));
            }

            if (!$reflectionProperty->getType() instanceof \ReflectionNamedType) {
                throw new \LogicException('$reflectionProperty::getType must be an instance of ReflectionNamedType');
            }
            if ($reflectionProperty->getType()->getName() !== $property->type) {
                throw new \LogicException(sprintf('Error for property "%s" in class "%s", the type mismatch between the old and the new version of class. Please verify your integration.', $reflectionProperty->getName(), $object::class));
            }

            $childrenProperties = $property->children;
            if (!$reflectionProperty->getType()->isBuiltin()) {
                if (!is_object($value)) {
                    // ERROR ?
                    continue;
                }
                $this->assertProperties($value, $childrenProperties);
            }

            if ([] === $childrenProperties) {
                unset($properties[$propertyIndex]);
            }
        }
    }
}
