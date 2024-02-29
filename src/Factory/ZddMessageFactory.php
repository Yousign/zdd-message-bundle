<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Serializer\MessageSerializerInterface;

/**
 * @internal
 */
final class ZddMessageFactory
{
    public function __construct(
        private readonly MessageSerializerInterface $serializer,
        private readonly ZddPropertyExtractor $propertyExtractor,
    ) {
    }

    public function create(string $messageName, object $message): ZddMessage
    {
        return new ZddMessage(
            $messageName,
            $message::class,
            $this->serializer->serialize($message),
            $this->propertyExtractor->extractProperties($message),
        );
    }
}
