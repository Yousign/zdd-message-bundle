<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Serializer;

interface SerializerInterface
{
    public function serialize(mixed $data): string;

    public function deserialize(string $data): mixed;
}
