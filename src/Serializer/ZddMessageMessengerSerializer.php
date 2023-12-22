<?php

namespace Yousign\ZddMessageBundle\Serializer;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface as MessengerSerializerInterface;

class ZddMessageMessengerSerializer implements SerializerInterface
{
    private MessengerSerializerInterface $serializer;

    public function __construct(MessengerSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize(mixed $data): string
    {
        if (!\is_object($data)) {
            throw new \InvalidArgumentException(sprintf('Object expected, %s provided', \gettype($data)));
        }

        return \json_encode($this->serializer->encode(Envelope::wrap($data)), JSON_THROW_ON_ERROR);
    }

    public function deserialize(string $data): mixed
    {
        $encodedEnvelope = \json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($encodedEnvelope)) {
            throw new \InvalidArgumentException(sprintf('Array expected, %s provided', \gettype($encodedEnvelope)));
        }

        return $this->serializer->decode($encodedEnvelope)->getMessage();
    }
}
