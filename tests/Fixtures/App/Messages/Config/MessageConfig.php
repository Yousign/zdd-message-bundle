<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config;

use Safe\DateTimeImmutable;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithSafeDateTimeImmutable;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Other;

class MessageConfig implements ZddMessageConfigInterface
{
    public static array $messagesToAssert = [];

    public function getMessageToAssert(): \Generator
    {
        if ([] !== self::$messagesToAssert) {
            yield from self::$messagesToAssert;

            return;
        }

        yield DummyMessage::class => new DummyMessage(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ac volutpat nisl.',
        );

        yield DummyMessageWithNullableNumberProperty::class => new DummyMessageWithNullableNumberProperty(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ac volutpat nisl.',
        );

        yield DummyMessageWithPrivateConstructor::class => DummyMessageWithPrivateConstructor::create(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ac volutpat nisl.',
        );

        yield DummyMessageWithSafeDateTimeImmutable::class => new DummyMessageWithSafeDateTimeImmutable(
            new DateTimeImmutable('2021-01-01T00:00:00+00:00'),
        );

        yield DummyMessageWithAllManagedTypes::class => new DummyMessageWithAllManagedTypes(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ac volutpat nisl.',
            1,
            true,
            ['key' => 'value'],
            new Locale('fr'),
            Status::DRAFT,
        );

        yield Other\DummyMessage::class => new Other\DummyMessage([
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ac volutpat nisl.',
        ]);
    }

    public static function reset(): void
    {
        self::$messagesToAssert = [];
    }
}
