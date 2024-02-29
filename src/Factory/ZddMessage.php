<?php

namespace Yousign\ZddMessageBundle\Factory;

/**
 * @internal
 */
final class ZddMessage implements \JsonSerializable
{
    /**
     * @param Property[] $properties
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $serializedMessage,
        public readonly array $properties,
    ) {
    }

    public function getFingerprint(): string
    {
        $fingerprint = $this->type.'(';

        $childrenCount = count($this->properties);

        foreach ($this->properties as $index => $property) {
            $fingerprint .= $property->getFingerprint();

            if ($index < $childrenCount - 1) {
                $fingerprint .= ',';
            }
        }

        $fingerprint .= ')';

        return $fingerprint;
    }

    public function jsonSerialize(): array // @phpstan-ignore-line
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'serialized_message' => $this->serializedMessage,
            'properties' => $this->properties,
        ];
    }

    public static function fromArray(array $data): self // @phpstan-ignore-line
    {
        return new self(
            $data['name'],
            $data['type'],
            $data['serialized_message'],
            array_map(
                static fn (array $p) => Property::fromArray($p),
                $data['properties'],
            ),
        );
    }
}
