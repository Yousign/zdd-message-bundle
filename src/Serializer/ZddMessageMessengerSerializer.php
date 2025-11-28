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
        /** @var array{body: string, headers?: array<string, string>} $dataArray */
        $dataArray = \json_decode($data, true, 512, \JSON_THROW_ON_ERROR);
        if (!\is_array($dataArray)) {
            throw new \InvalidArgumentException(sprintf('Array expected, %s provided', \gettype($data)));
        }

        try {
            $envelope = $this->serializer->decode($dataArray);
            if (!$envelope instanceof Envelope) {
                throw new \InvalidArgumentException(sprintf('%s expected, %s provided', Envelope::class, \gettype($data)));
            }

            return $envelope->getMessage();
        } catch (MessageDecodingFailedException $e) {
            throw new UnableToDeserializeException(previous: $e);
        }
    }
}
