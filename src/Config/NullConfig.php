<?php

namespace Yousign\ZddMessageBundle\Config;

class NullConfig implements ZddMessageConfigInterface
{
    public function getMessageToAssert(): array
    {
        return [];
    }

    public function generateValueForCustomPropertyType(string $type): mixed
    {
        return null;
    }
}
