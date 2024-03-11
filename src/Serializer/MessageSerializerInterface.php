<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Serializer;

interface MessageSerializerInterface
{
    public function serialize(object $data): string;

    /**
     * @throws UnableToDeserializeException
     */
    public function deserialize(string $data): object;
}
