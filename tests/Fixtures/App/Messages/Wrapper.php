<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages;

class Wrapper
{
    public function __construct(
        public readonly Command $command,
    )
    {
    }
}
