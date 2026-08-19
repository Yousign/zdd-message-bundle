<?php

namespace Youtrust\ZddMessageBundle\Tests\Fixtures\App\Messages\Other;

final class DummyMessage
{
    public function __construct(
        public readonly array $contents,
    ) {
    }
}
