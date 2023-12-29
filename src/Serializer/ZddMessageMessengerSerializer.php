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

        return $this->serializer->encode(Envelope::wrap($data))['body'];
    }

    public function deserialize(string $data): object
    {
        return $this->serializer->decode(['body' => $data])->getMessage();
    }
}
