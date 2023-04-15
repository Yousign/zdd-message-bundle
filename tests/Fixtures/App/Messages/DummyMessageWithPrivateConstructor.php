<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages;

final class DummyMessageWithPrivateConstructor
{
    private string $content;

    private function __construct()
    {
    }

    public static function create(string $content): self
    {
        $self = new self();
        $self->content = $content;

        return $self;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
