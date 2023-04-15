<?php

namespace Yousign\ZddMessageBundle\Utils;

class ParameterList
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters = [];

    /**
     * @var array<string, string>
     */
    private array $notNullableProperties = [];

    public function add(string $propertyName, mixed $value = null, string $type = null): void
    {
        $this->parameters[$propertyName] = $value;

        if (null !== $type) {
            $this->notNullableProperties[$propertyName] = $type;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<string, string>
     */
    public function getNotNullableProperties(): array
    {
        return $this->notNullableProperties;
    }
}
