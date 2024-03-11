<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages;

use Safe\DateTimeImmutable;

final class DummyMessageWithSafeDateTimeImmutable
{
    private DateTimeImmutable $occuredAt;

    public function __construct(DateTimeImmutable $occuredAt)
    {
        $this->occuredAt = $occuredAt;
    }

    public function getOccuredAt(): DateTimeImmutable
    {
        return $this->occuredAt;
    }
}
