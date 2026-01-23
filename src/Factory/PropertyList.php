<?php

namespace Yousign\ZddMessageBundle\Factory;

/**
 * @internal
 */
final class PropertyList
{
    /**
     * @var Property[]
     */
    private array $properties = [];

    /**
     * @param Property[] $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property) {
            $this->properties[$property->name] = $property;
        }
    }

    public function addProperty(Property $property): void
    {
        $this->properties[$property->name] = $property;
    }

    public static function fromJson(string $data): self
    {
        /** @var array<
         *     array{name?: string, type?: string, isNullable?: bool}
         * > $decodedProperties
         */
        $decodedProperties = \json_decode($data, true);
        $properties = [];
        foreach ($decodedProperties as $decodedProperty) {
            $name = $decodedProperty['name'] ?? null;
            $type = $decodedProperty['type'] ?? null;
            $isNullable = $decodedProperty['isNullable'] ?? null;

            if (null === $name || null === $type || null === $isNullable) {
                throw new \LogicException(sprintf('Missing keys name and/or type and/or isNullable in decoded properties from data: "%s"', $data));
            }
            $properties[] = new Property($name, $type, $isNullable);
        }

        return new self($properties);
    }

    /**
     * @return array<string>
     */
    public function getPropertiesName(): array
    {
        return array_keys($this->properties);
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->properties);
    }

    public function get(string $name): Property
    {
        $property = $this->properties[$name] ?? null;

        if (null === $property) {
            throw new \LogicException(sprintf('No property "%s" found in the properties list', $name));
        }

        return $property;
    }

    public function remove(string $name): void
    {
        unset($this->properties[$name]);
    }

    public function count(): int
    {
        return count($this->properties);
    }

    public function toJson(): string
    {
        $data = [];
        foreach ($this->properties as $property) {
            $data[] = [
                'name' => $property->name,
                'type' => $property->type,
                'isNullable' => $property->isNullable,
            ];
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
