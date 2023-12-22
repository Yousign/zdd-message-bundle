<?php

namespace Yousign\ZddMessageBundle\Serializer;

class ZddMessagePhpSerializer implements SerializerInterface
{
    public function serialize(mixed $data): string
    {
        return serialize($data);
    }

    public function deserialize(string $data): mixed
    {
        return \unserialize($data);
    }
}
