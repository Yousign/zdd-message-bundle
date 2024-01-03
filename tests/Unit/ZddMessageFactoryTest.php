<?php

namespace Yousign\ZddMessageBundle\Tests\Unit;

use App\WithoutValue\WithoutValue;
use App\WithoutValue\WithoutValueConfig;
use PHPUnit\Framework\TestCase;
use Yousign\ZddMessageBundle\Exceptions\MissingValueForTypeException;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config\MessageConfig;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status;

class ZddMessageFactoryTest extends TestCase
{
    use SerializerTrait;

    private readonly ZddMessageFactory $zddMessageFactory;

    public function setUp(): void
    {
        $this->zddMessageFactory = new ZddMessageFactory(new MessageConfig(), $this->getSerializer());
    }

    public function testItGeneratesSerializedMessageWithNullAndNotNullableProperties(): void
    {
        $zddMessage = $this->zddMessageFactory->create(DummyMessageWithNullableNumberProperty::class);
        self::assertSame(
            $this->getSerializer()->serialize(new DummyMessageWithNullableNumberProperty('Hello World!')),
            $zddMessage->serializedMessage()
        );

        self::assertEquals(2, $zddMessage->propertyList()->count());
        self::assertTrue($zddMessage->propertyList()->has('content'));
        $property = $zddMessage->propertyList()->get('content');
        self::assertSame('string', $property->type);
        self::assertSame('Hello World!', $property->value);

        $propertyNumber = $zddMessage->propertyList()->get('number');
        self::assertSame('int', $propertyNumber->type);
        self::assertNull($propertyNumber->value);
    }

    public function testItGeneratesSerializedMessageForDummyMessageWithPrivateConstructor(): void
    {
        $zddMessage = $this->zddMessageFactory->create(DummyMessageWithPrivateConstructor::class);
        self::assertSame(
            $this->getSerializer()->serialize(DummyMessageWithPrivateConstructor::create('Hello World!')),
            $zddMessage->serializedMessage()
        );

        self::assertEquals(1, $zddMessage->propertyList()->count());
        self::assertTrue($zddMessage->propertyList()->has('content'));
        $property = $zddMessage->propertyList()->get('content');
        self::assertSame('string', $property->type);
        self::assertSame('Hello World!', $property->value);
    }

    public function testItGeneratesSerializedMessageForDummyMessageContainingAllManagedTypesWithoutError(): void
    {
        $zddMessage = $this->zddMessageFactory->create(DummyMessageWithAllManagedTypes::class);

        self::assertSame(
            $this->getSerializer()->serialize(new DummyMessageWithAllManagedTypes(
                'Hello World!',
                42,
                true,
                ['PHP', 'For The Win'],
                new Locale('fr'),
                Status::DRAFT
            )),
            $zddMessage->serializedMessage());

        self::assertEquals(6, $zddMessage->propertyList()->count());
        self::assertSame('string', $zddMessage->propertyList()->get('content')->type);
        self::assertSame('int', $zddMessage->propertyList()->get('count')->type);
        self::assertSame('bool', $zddMessage->propertyList()->get('enable')->type);
        self::assertSame('array', $zddMessage->propertyList()->get('data')->type);
        self::assertSame(Locale::class, $zddMessage->propertyList()->get('locale')->type);
        self::assertSame(Status::class, $zddMessage->propertyList()->get('status')->type);
    }

    public function testItThrownAMissingValueForTypeException(): void
    {
        $factory = new ZddMessageFactory(new WithoutValueConfig(), $this->getSerializer());

        $this->expectException(MissingValueForTypeException::class);
        $this->expectExceptionMessage('Missing value for property type "Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage" maybe you forgot to add it in "App\WithoutValue\WithoutValueConfig"');
        $factory->create(WithoutValue::class);
    }
}

namespace App\WithoutValue;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;

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

    public function generateValueForCustomPropertyType(string $type): mixed
    {
        return null;
    }
}
