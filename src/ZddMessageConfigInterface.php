<?php

namespace Yousign\ZddMessageBundle;

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
     * The value that should be set to each Message parameter.
     *
     * @example
     * Suppose you have messages 2 messages like this:
     * class MyMessage
     * {
     *   private string $content;
     *   private int $number;
     *   private array $data
     *   private MyOtherMessage $otherMessage;
     * }
     *
     * class MyOtherMessage
     * {
     *   private float $total;
     * }
     *
     * The getValue should be like this:
     *
     * getValue(string $typeHint): array
     *   {
     *     return match($typeHint) [
     *       'string' => 'Up to you',
     *        'int' => 42,
     *        'float' => 4.2,
     *        'array' => ['PHP', 'For The Win'],
     *        'App\Message\MyOtherMessage' => new MyOtherMessage(['PHP', 'For The Win']),
     *    ];
     *   }
     *
     * @see MessageConfig in ZddMessageFakerTest.php for a concret examples
     */
    public function getValue(string $typeHint): mixed;
}
