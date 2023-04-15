<?php

namespace Yousign\ZddMessageBundle\Tests\Unit;

use App\WithoutValue\WithoutValue;
use App\WithoutValue\WithoutValueConfig;
use PHPUnit\Framework\TestCase;
use Yousign\ZddMessageBundle\Exceptions\MissingValueForTypeException;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config\MessageConfig;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor;
use Yousign\ZddMessageBundle\Utils\ZddMessageFactory;

class ZddMessageFakerTest extends TestCase
{
    private readonly ZddMessageFactory $zddMessageFaker;

    public function setUp(): void
    {
        $this->zddMessageFaker = new ZddMessageFactory(new MessageConfig());
    }

    public function testItGeneratesSerializedMessageWithExpectedNotNullableProperties(): void
    {
        $zddMessage = $this->zddMessageFaker->create(DummyMessageWithNullableNumberProperty::class);
        self::assertSame(<<<TXT
            O:91:"Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty":2:{s:100:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty\x00content";s:12:"Hello World!";s:99:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty\x00number";N;}
            TXT, $zddMessage->serializedMessage());
        self::assertSame(['content' => 'string'], $zddMessage->notNullableProperties());
    }

    public function testItGeneratesSerializedMessageForDummyMessageWithPrivateConstructor(): void
    {
        $zddMessage = $this->zddMessageFaker->create(DummyMessageWithPrivateConstructor::class);
        self::assertSame(<<<TXT
            O:87:"Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor":1:{s:96:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor\x00content";s:12:"Hello World!";}
            TXT, $zddMessage->serializedMessage());
        self::assertSame(['content' => 'string'], $zddMessage->notNullableProperties());
    }

    public function testItGeneratesSerializedMessageForDummyMessageContainingAllManagedTypesWithoutError(): void
    {
        $zddMessage = $this->zddMessageFaker->create(DummyMessageWithAllManagedTypes::class);

        self::assertSame(<<<TXT
            O:84:"Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes":6:{s:93:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes\x00content";s:12:"Hello World!";s:91:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes\x00count";i:42;s:92:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes\x00enable";b:1;s:90:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes\x00data";a:2:{i:0;s:3:"PHP";i:1;s:11:"For The Win";}s:92:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes\x00locale";O:65:"Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale":1:{s:73:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale\x00locale";s:2:"fr";}s:92:"\x00Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes\x00status";E:71:"Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status:DRAFT";}
            TXT, $zddMessage->serializedMessage());
        self::assertSame([
            'content' => 'string',
            'count' => 'int',
            'enable' => 'bool',
            'data' => 'array',
            'locale' => 'Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale',
            'status' => 'Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status',
        ], $zddMessage->notNullableProperties());
    }

    public function testItThrownAMissingValueForTypeException(): void
    {
        $factory = new ZddMessageFactory(new WithoutValueConfig());

        $this->expectException(MissingValueForTypeException::class);
        $this->expectExceptionMessage('Missing value for property type "dummyMessage" maybe you forgot to add it in "App\WithoutValue\WithoutValueConfig"');
        $factory->create(WithoutValue::class);
    }
}

namespace App\WithoutValue;

use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;
use Yousign\ZddMessageBundle\ZddMessageConfigInterface;

final class WithoutValue
{
    public function __construct(private DummyMessage $dummyMessage)
    {
    }
}

final class WithoutValueConfig implements ZddMessageConfigInterface
{
    public function getMessageToAssert(): array
    {
        return [
            WithoutValue::class,
        ];
    }

    public function getValue(string $typeHint): mixed
    {
        return [];
    }
}
