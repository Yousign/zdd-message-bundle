<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages;

use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status;

final class DummyMessageWithAllManagedTypes
{
    public function __construct(
        private readonly string $content,
        private readonly int $count,
        private readonly bool $enable,
        private readonly array $data,
        private readonly Locale $locale,
        private readonly Status $status,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
