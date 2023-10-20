<?php

namespace Yousign\ZddMessageBundle\Factory;

/**
 * @internal
 */
final class ZddMessage
{
    public function __construct(
        private readonly string $messageFqcn,
        private readonly string $serializedMessage,
        private readonly PropertyList $propertyList,
        private readonly ?object $message = null,
    ) {
    }

    public function message(): ?object
    {
        return $this->message;
    }

    public function serializedMessage(): string
    {
        return $this->serializedMessage;
    }

    public function propertyList(): PropertyList
    {
        return $this->propertyList;
    }

    public function messageFqcn(): string
    {
        return $this->messageFqcn;
    }
}
