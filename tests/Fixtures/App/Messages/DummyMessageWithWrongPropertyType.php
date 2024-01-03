<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages;

final class DummyMessageWithWrongPropertyType
{
    private int $content;

    public function __construct(int $content)
    {
        $this->content = $content;
    }

    public function getContent(): int
    {
        return $this->content;
    }
}
