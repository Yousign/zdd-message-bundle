<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Tests\Unit\Assert;

use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Throwable;
use Yousign\ZddMessageBundle\Assert\ZddMessageAsserter;
use Yousign\ZddMessageBundle\Factory\Property;
use Yousign\ZddMessageBundle\Factory\ZddMessage;
use Yousign\ZddMessageBundle\Serializer\UnableToDeserializeException;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithWrongPropertyType;
use Yousign\ZddMessageBundle\Tests\Unit\SerializerTrait;

use function str_replace;

class ZddMessageAsserterTest extends TestCase
{
    use SerializerTrait;

    /**
     * @param Property[]
     *
     * @dataProvider provideValidAssertion
     */
    public function testItAssertsWithSuccess(
        object $instance,
        ZddMessage $zddMessage,
    ): void {
        $sut = $this->getSut();
        $sut->assert($instance, $zddMessage);
        self::assertTrue(true); // if we reached this statement, no exception has been thrown => OK test
    }

    public function provideValidAssertion(): iterable
    {
        yield 'Unchanged message' => [
            $instance = new DummyMessage('Hello world'),
            new ZddMessage(
                DummyMessage::class,
                DummyMessage::class,
                $this->getSerializer()->serialize($instance),
                [
                    new Property('content', 'string', []),
                ],
            ),
        ];

        yield 'Number property has been switched to nullable' => [
            $instance = new DummyMessageWithNullableNumberProperty('Hello world'),
            new ZddMessage(
                DummyMessageWithNullableNumberProperty::class,
                DummyMessageWithNullableNumberProperty::class,
                $this->getSerializer()->serialize($instance),
                [
                    new Property('content', 'string', []),
                    new Property('number', 'integer', []),
                ],
            ),
        ];
    }

    public function testItThrowsExceptionWhenAPropertyHasBeenRemoved(): void
    {
        $instance = new DummyMessage('Hello world');
        $zddMessage = new ZddMessage(
            DummyMessage::class,
            DummyMessage::class,
            $this->getSerializer()->serialize($instance),
            [
                new Property('content', 'string', []),
                new Property('number', 'integer', []),
            ],
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('⚠️ The properties "number" in class "Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage" seems to have been removed');

        $sut = $this->getSut();
        $sut->assert($instance, $zddMessage);
    }

    public function testItThrowsExceptionForInvalidIntegrationDueToClassMismatch(): void
    {
        $instance = new StdClass();
        $zddMessage = new ZddMessage(
            DummyMessage::class,
            DummyMessage::class,
            $this->getSerializer()->serialize(new DummyMessage('Hello world')),
            [
                new Property('content', 'string', []),
            ],
        );

        $sut = $this->getSut();

        try {
            $sut->assert($instance, $zddMessage);
            $this->fail('This test should raised expected exception');
        } catch (Throwable $t) {
            $this->assertInstanceOf(LogicException::class, $t);
            $this->assertStringContainsString('Class mismatch', $t->getMessage());
            $this->assertStringContainsString(DummyMessage::class, $t->getMessage());
        }
    }

    public function testItThrowsExceptionForInvalidIntegrationDueToPropertyTypeMismatch(): void
    {
        $instance = new DummyMessage('Hello world');
        $zddMessage = new ZddMessage(
            DummyMessage::class,
            DummyMessage::class,
            $this->getSerializer()->serialize($instance),
            [
                new Property('content', 'integer', []), // Simulate error using 'int' typeHint instead of 'string'
            ],
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error for property "content" in class "Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage", the type mismatch between the old and the new version of class. Please verify your integration.');

        $sut = $this->getSut();
        $sut->assert($instance, $zddMessage);
    }

    public function testItThrowsExceptionForInvalidPropertyType(): void
    {
        $instance = new DummyMessageWithWrongPropertyType(22);

        $serializedMessage = $this->getSerializer()->serialize($instance);
        $serializedMessage = str_replace('DummyMessageWithWrongPropertyType', 'DummyMessage', $serializedMessage);

        $zddMessage = new ZddMessage(
            DummyMessage::class,
            DummyMessage::class,
            $serializedMessage,
            [
                new Property('content', 'integer', []), // Simulate error using 'int' typeHint instead of 'string'
            ],
        );

        $sut = $this->getSut();

        try {
            $sut->assert($instance, $zddMessage);
        } catch (UnableToDeserializeException $e) {
            $this->assertInstanceOf(MessageDecodingFailedException::class, $e->getPrevious());

            return;
        }

        $this->fail('This test should raised expected exception');
    }

    public function getSut(): ZddMessageAsserter
    {
        return new ZddMessageAsserter($this->getSerializer());
    }
}
