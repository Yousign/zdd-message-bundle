<?php

namespace Yousign\ZddMessageBundle\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;
use Yousign\ZddMessageBundle\Factory\ZddPropertyExtractor;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status;
use Yousign\ZddMessageBundle\Tests\Unit\SerializerTrait;

class ZddMessageFactoryTest extends TestCase
{
    use SerializerTrait;

    private readonly ZddMessageFactory $zddMessageFactory;

    public function setUp(): void
    {
        $this->zddMessageFactory = new ZddMessageFactory($this->getSerializer(), new ZddPropertyExtractor());
    }

    public function testItGeneratesSerializedMessageWithNullAndNotNullableProperties(): void
    {
        $zddMessage = $this->zddMessageFactory->create(DummyMessageWithNullableNumberProperty::class, new DummyMessageWithNullableNumberProperty('Hello World!'));
        self::assertSame(
            $this->getSerializer()->serialize(new DummyMessageWithNullableNumberProperty('Hello World!')),
            $zddMessage->serializedMessage,
        );

        self::assertCount(2, $zddMessage->properties);

        $propertyContent = $zddMessage->properties[0];
        $this->assertSame('content', $propertyContent->name);
        $this->assertSame('string', $propertyContent->type);
        $this->assertCount(0, $propertyContent->children);

        $propertyNumber = $zddMessage->properties[1];
        $this->assertSame('number', $propertyNumber->name);
        $this->assertSame('int', $propertyNumber->type);
        $this->assertCount(0, $propertyNumber->children);
    }

    public function testItGeneratesSerializedMessageForDummyMessageWithPrivateConstructor(): void
    {
        $zddMessage = $this->zddMessageFactory->create(DummyMessageWithPrivateConstructor::class, DummyMessageWithPrivateConstructor::create('Hello World!'));
        self::assertSame(
            $this->getSerializer()->serialize(DummyMessageWithPrivateConstructor::create('Hello World!')),
            $zddMessage->serializedMessage,
        );

        self::assertCount(1, $zddMessage->properties);

        $propertyContent = $zddMessage->properties[0];
        $this->assertSame('content', $propertyContent->name);
        $this->assertSame('string', $propertyContent->type);
        $this->assertCount(0, $propertyContent->children);
    }

    public function testItGeneratesSerializedMessageForDummyMessageContainingAllManagedTypesWithoutError(): void
    {
        $zddMessage = $this->zddMessageFactory->create(DummyMessageWithAllManagedTypes::class, new DummyMessageWithAllManagedTypes(
            'Hello World!',
            42,
            true,
            ['PHP', 'For The Win'],
            new Locale('fr'),
            Status::DRAFT,
        ));

        self::assertSame(
            $this->getSerializer()->serialize(new DummyMessageWithAllManagedTypes(
                'Hello World!',
                42,
                true,
                ['PHP', 'For The Win'],
                new Locale('fr'),
                Status::DRAFT
            )),
            $zddMessage->serializedMessage,
        );

        self::assertCount(6, $zddMessage->properties);

        $propertyContent = $zddMessage->properties[0];
        $this->assertSame('content', $propertyContent->name);
        $this->assertSame('string', $propertyContent->type);
        $this->assertCount(0, $propertyContent->children);

        $propertyCount = $zddMessage->properties[1];
        $this->assertSame('count', $propertyCount->name);
        $this->assertSame('int', $propertyCount->type);
        $this->assertCount(0, $propertyCount->children);

        $propertyEnable = $zddMessage->properties[2];
        $this->assertSame('enable', $propertyEnable->name);
        $this->assertSame('bool', $propertyEnable->type);
        $this->assertCount(0, $propertyEnable->children);

        $propertyData = $zddMessage->properties[3];
        $this->assertSame('data', $propertyData->name);
        $this->assertSame('array', $propertyData->type);
        $this->assertCount(0, $propertyData->children);

        $propertyLocale = $zddMessage->properties[4];
        $this->assertSame('locale', $propertyLocale->name);
        $this->assertSame(Locale::class, $propertyLocale->type);
        $this->assertCount(1, $propertyLocale->children);

        $propertyLocaleValue = $propertyLocale->children[0];
        $this->assertSame('locale', $propertyLocaleValue->name);
        $this->assertSame('string', $propertyLocaleValue->type);
        $this->assertCount(0, $propertyLocaleValue->children);

        $propertyStatus = $zddMessage->properties[5];
        $this->assertSame('status', $propertyStatus->name);
        $this->assertSame(Status::class, $propertyStatus->type);
        $this->assertCount(2, $propertyStatus->children);

        $propertyStatusName = $propertyStatus->children[0];
        $this->assertSame('name', $propertyStatusName->name);
        $this->assertSame('string', $propertyStatusName->type);
        $this->assertCount(0, $propertyStatusName->children);

        $propertyStatusValue = $propertyStatus->children[1];
        $this->assertSame('value', $propertyStatusValue->name);
        $this->assertSame('string', $propertyStatusValue->type);
        $this->assertCount(0, $propertyStatusValue->children);
    }
}
