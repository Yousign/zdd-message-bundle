<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Factory;

final class Property implements \JsonSerializable
{
    /**
     * @param self[] $children
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly array $children,
    ) {
    }

    public function getFingerprint(): string
    {
        $fingerprint = $this->name.':'.$this->type;

        if ([] !== $this->children) {
            $fingerprint .= '(';

            $childrenCount = count($this->children);

            foreach ($this->children as $index => $property) {
                $fingerprint .= $property->getFingerprint();

                if ($index < $childrenCount - 1) {
                    $fingerprint .= ',';
                }
            }

            $fingerprint .= ')';
        }

        return $fingerprint;
    }

    /**
     * @param self[] $properties
     *
     * @return string[]
     */
    public static function getPropertyNames(array $properties): array
    {
        return array_map(
            static fn (self $property) => $property->name,
            $properties,
        );
    }

    public function jsonSerialize(): array // @phpstan-ignore-line
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'children' => $this->children,
        ];
    }

    public static function fromArray(array $data): self // @phpstan-ignore-line
    {
        return new self(
            $data['name'],
            $data['type'],
            array_map(static fn (array $p) => Property::fromArray($p), $data['children']),
        );
    }
}
