<?php

namespace Yousign\ZddMessageBundle\Utils;

final class PropertyList
{
    /**
     * @var array<string, mixed>
     */
    private array $properties = [];

    /**
     * @var array<string, string>
     */
    private array $notNullableProperties = [];

    public function addNullableProperty(string $propertyName): void
    {
        $this->properties[$propertyName] = null;
    }

    public function addProperty(string $propertyName, string $type, mixed $value): void
    {
        $this->properties[$propertyName] = $value;
        $this->notNullableProperties[$propertyName] = $type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return array<string, string>
     */
    public function getNotNullableProperties(): array
    {
        return $this->notNullableProperties;
    }
}
