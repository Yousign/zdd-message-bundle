<?php

namespace Youtrust\ZddMessageBundle\Factory;

final class Property
{
    public function __construct(public readonly string $name, public readonly string $type, public readonly bool $isNullable)
    {
    }
}
