<?php

namespace Yousign\ZddMessageBundle\Assert;

/**
 * @internal
 */
final class ZddMessageAssert
{
    /**
     * @param class-string<object>         $messageFqcn
     * @param array<string|string, string> $notNullableProperties
     */
    public static function assert(
        string $messageFqcn,
        string $serializedMessage,
        array $notNullableProperties
    ): void {
        // ✅ Assert message is unserializable
        $object = unserialize($serializedMessage);

        if (!$object instanceof $messageFqcn) {
            throw new \LogicException(\sprintf('Class mismatch between $messageFqcn: "%s" and $serializedMessage: "%s". Please verify your integration.', $messageFqcn, $serializedMessage));
        }

        $reflection = new \ReflectionClass($messageFqcn);
        $properties = $reflection->getProperties();

        // ✅ Assert property type hint has not changed and new property have a default value
        foreach ($properties as $property) {
            // ✅ Assert error "Typed property Message::$theProperty must not be accessed before initialization".
            $property->getValue($object); // @phpstan-ignore-line :::  Call to method ReflectionProperty::getValue() on a separate line has no effect.

            $method = $property->name;
            if (method_exists($object, $method)) {
                $object->{$method}();
                continue;
            }

            $method = 'get'.ucfirst($method);
            if (method_exists($object, $method)) {
                $object->{$method}();
            }
        }

        // ✅ Assert not nullable property has been removed
        foreach ($properties as $property) {
            if (\array_key_exists($property->getName(), $notNullableProperties)) {
                self::assertProperty($property, $notNullableProperties);
                unset($notNullableProperties[$property->getName()]);
            }
        }

        if (0 !== \count($notNullableProperties)) {
            throw new \LogicException(\sprintf('The properties "%s" in class "%s" seems to have been removed, make it nullable first, deploy it and then remove it 🔥', implode(', ', \array_flip($notNullableProperties)), $messageFqcn));
        }
    }

    /**
     * @param array<string, string> $notNullableProperties
     */
    private static function assertProperty(\ReflectionProperty $reflectionProperty, array $notNullableProperties): void
    {
        if (null === $reflectionProperty->getType()) {
            throw new \LogicException(\sprintf('$reflectionProperty::getType cannot be null'));
        }
        if (!$reflectionProperty->getType() instanceof \ReflectionNamedType) {
            throw new \LogicException(\sprintf('$reflectionProperty::getType must be an instance of ReflectionNamedType'));
        }
        if ($reflectionProperty->getType()->getName() !== $notNullableProperties[$reflectionProperty->getName()]) {
            throw new \LogicException(\sprintf('Property type mismatch between properties from $messageFqcn class and $notNullableProperties. Please verify your integration.'));
        }
    }
}
