<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages;

class DummyMessageWithReadonlyProperty
{
    public readonly string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }
}
