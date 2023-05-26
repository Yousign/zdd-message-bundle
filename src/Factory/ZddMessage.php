<?php

namespace Yousign\ZddMessageBundle\Factory;

/**
 * @internal
 */
final class ZddMessage
{
    /**
     * @param array<string, string> $notNullableProperties
     */
    public function __construct(
        private readonly string $messageFqcn,
        private readonly string $serializedMessage,
        private readonly array $notNullableProperties,
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

    /**
     * @return array<string, string>
     */
    public function notNullableProperties(): array
    {
        return $this->notNullableProperties;
    }

    public function messageFqcn(): string
    {
        return $this->messageFqcn;
    }
}
