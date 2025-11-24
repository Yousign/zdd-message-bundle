<?php

namespace Yousign\ZddMessageBundle\Config;

interface ZddMessageConfigInterface
{
    /**
     * The list of FQCN message to assert.
     *
     * @example getMessageToAssert(): array
     *   {
     *    return [
     *             'App\Message\MyMessage',
     *             'App\Message\MyOtherMessage'
     *           ];
     *   }
     *
     * @return array<class-string>
     */
    public function getMessageToAssert(): array;

    /**
     * Provide a fake value for each custom property type used in your messages.
     * You can also override the fake value used for scalar types.
     *
     * @example
     * Suppose you have message which contains an object as property type:
     *
     * class MyMessage
     * {
     *     private MyObject $object;
     *     // ...
     * }
     *
     * class MyObject
     * {
     *     private string $content;
     *     // ...
     * }
     *
     * The implementation of generateValueForCustomPropertyType should be like this:
     *
     * public function generateValueForCustomPropertyType(string $type): array;
     * {
     *    return match($type) {
     *        'Namespace\MyObject' => new MyObject("Hi!"),
     *        default => null,
     *    };
     * }
     *
     * @see MessageConfig in ZddMessageFakerTest.php for a concret examples
     */
    public function generateValueForCustomPropertyType(string $type): mixed;

    /**
     * If you need full control over how a specific message instance is created,
     * use this method to return a fully instantiated message object.
     * This is useful when the default instantiation (using reflection and property injection)
     * is not sufficient or when your message requires specific constructor logic.
     *
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return ?T The fully instantiated message object, or null if the default instantiation should be used
     */
    public function generateCustomMessage(string $className): ?object;
}
