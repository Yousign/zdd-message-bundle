<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyCustomMessage;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Other;

class MessageConfig implements ZddMessageConfigInterface
{
    public static array $messagesToAssert = [];

    #[\Override]
    public function getMessageToAssert(): array
    {
        return [] !== self::$messagesToAssert ? self::$messagesToAssert : [
            DummyMessage::class,
            DummyMessageWithNullableNumberProperty::class,
            DummyMessageWithPrivateConstructor::class,
            DummyMessageWithAllManagedTypes::class,
            Other\DummyMessage::class,
            DummyCustomMessage::class,
        ];
    }

    #[\Override]
    public function generateValueForCustomPropertyType(string $type): mixed
    {
        return match ($type) {
            Locale::class => new Locale('fr'),
            'Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status' => Status::DRAFT,
            DummyMessageWithNullableNumberProperty::class => new DummyMessageWithNullableNumberProperty('content'),
            default => null,
        };
    }

    public static function reset(): void
    {
        self::$messagesToAssert = [];
    }

    #[\Override]
    public function generateCustomMessage(string $className): ?object
    {
        if (DummyCustomMessage::class === $className) {
            return new DummyCustomMessage('custom message data');
        }

        return null;
    }
}
