<?php

namespace Yousign\ZddMessageBundle\Serializer;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface as MessengerSerializerInterface;

class ZddMessageMessengerSerializer implements SerializerInterface
{
    public function __construct(private readonly MessengerSerializerInterface $serializer)
    {
    }

    public function serialize(object $data): string
    {
        $encodedEnvelope = $this->serializer->encode(Envelope::wrap($data));

        return \json_encode($encodedEnvelope, \JSON_THROW_ON_ERROR);
    }

    public function deserialize(string $data): object
    {
        $decoded = \json_decode($data, true, 512, \JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new \InvalidArgumentException(sprintf('Array expected, %s provided', \gettype($decoded)));
        }

        /** @var array{body: string, headers?: array<string, string>} $encodedEnvelope */
        $encodedEnvelope = $decoded;

        try {
            $message = $this->serializer->decode($encodedEnvelope)->getMessage();

            // Since Symfony 8, PhpSerializer no longer throws when a message cannot be rebuilt:
            // MessageDecodingFailedException::wrap() returns an envelope carrying the failure as
            // its message. Earlier versions throw, which the catch below still handles.
            if ($message instanceof MessageDecodingFailedException) {
                throw new UnableToDeserializeException(previous: $message);
            }

            return $message;
        } catch (MessageDecodingFailedException $e) {
            throw new UnableToDeserializeException(previous: $e);
        }
    }
}
