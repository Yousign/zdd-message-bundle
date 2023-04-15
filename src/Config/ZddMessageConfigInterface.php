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
     * The implementation of getCustomValueForPropertyType should be like this:
     *
     * public function getCustomValueForPropertyType(): array;
     * {
     *    return [
     *        'Namespace\MyObject' => new MyObject("Hi!"),
     *    ];
     * }
     *
     * @see MessageConfig in ZddMessageFakerTest.php for a concret examples
     *
     * @return array<string, mixed>
     */
    public function getCustomValueForPropertyType(): array;
}
