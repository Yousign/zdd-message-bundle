<?php

namespace Yousign\ZddMessageBundle\Config;

interface ZddMessageConfigInterface
{
    /**
     * The method should generate the list of message instances to assert.
     *
     * @example getMessageToAssert(): \Generator
     * {
     *     yield App\Message\MyMessage::class => new App\Message\MyMessage(),
     *     yield App\Message\MyOtherMessage::class => new App\Message\MyOtherMessage(),
     * }
     *
     * @return \Generator<string, object>
     */
    public function getMessageToAssert(): \Generator;
}
