<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages;

class Command
{
    public function __construct(
        public readonly string $name,
        public readonly EnumString $myString,
        public readonly EnumInt $myInt,
    )
    {
    }
}
