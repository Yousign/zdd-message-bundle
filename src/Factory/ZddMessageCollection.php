<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;

/**
 * @internal
 */
final class ZddMessageCollection
{
    /**
     * @var ZddMessage[]
     */
    public readonly array $messages;

    public function __construct(
        ZddMessageConfigInterface $config,
        ZddMessageFactory $messageFactory,
    ) {
        $messages = [];

        foreach ($config->getMessageToAssert() as $name => $message) {
            $messages[] = $messageFactory->create($name, $message);
        }

        $this->messages = $messages;
    }

    public function fingerprintExists(string $fingerprint): bool
    {
        return in_array(
            $fingerprint,
            array_map(static fn (ZddMessage $message) => $message->getFingerprint(), $this->messages),
            true,
        );
    }
}
