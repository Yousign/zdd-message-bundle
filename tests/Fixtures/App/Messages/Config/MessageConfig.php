<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status;

class MessageConfig implements ZddMessageConfigInterface
{
    public static array $messagesToAssert = [];

    public function getMessageToAssert(): array
    {
        return [] !== self::$messagesToAssert ? self::$messagesToAssert : [
            DummyMessage::class,
            DummyMessageWithNullableNumberProperty::class,
            DummyMessageWithPrivateConstructor::class,
            DummyMessageWithAllManagedTypes::class,
        ];
    }

    public function getCustomValueForPropertyType(): array
    {
        return [
            Locale::class => new Locale('fr'),
            'Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status' => Status::DRAFT,
            DummyMessageWithNullableNumberProperty::class => new DummyMessageWithNullableNumberProperty('content'),
        ];
    }

    public static function reset(): void
    {
        self::$messagesToAssert = [];
    }
}
