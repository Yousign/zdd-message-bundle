<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages;

final class DummyMessageWithNullableNumberProperty
{
    private string $content;
    private ?int $number = null;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }
}
