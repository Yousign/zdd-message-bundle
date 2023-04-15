<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input;

// Used for trigger a not zdd compliant error when a new not nullable property is added
class FooNotCompliantMessage
{
    private string $foo;
    private string $createdAt;
}
