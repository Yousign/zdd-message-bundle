<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;

class NullConfig implements ZddMessageConfigInterface
{
    public function getMessageToAssert(): array
    {
        return [];
    }

    public function getCustomValueForPropertyType(): array
    {
        return [];
    }
}
