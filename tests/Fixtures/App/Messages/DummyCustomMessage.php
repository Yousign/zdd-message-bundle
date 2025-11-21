<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages;

final class DummyCustomMessage
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
