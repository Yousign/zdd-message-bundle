<?php

namespace Yousign\ZddMessageBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Yousign\ZddMessageBundle\Assert\ZddMessageAsserter;
use Yousign\ZddMessageBundle\Factory\Property;
use Yousign\ZddMessageBundle\Factory\PropertyList;
use Yousign\ZddMessageBundle\Serializer\UnableToDeserializeException;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithWrongPropertyType;

class ZddMessageAsserterTest extends TestCase
{
    use SerializerTrait;

    /**
     * @param class-string $messageFqcn
     * @param Property[]
     *
     * @dataProvider provideValidAssertion
     */
    public function testItAssertsWithSuccess(
        string $messageFqcn,
        string $serializedMessage,
        string $jsonProperties,
    ): void {
        $propertyList = PropertyList::fromJson($jsonProperties);
        $sut = $this->getSut();
        $sut->assert($messageFqcn, $serializedMessage, $propertyList);
        self::assertTrue(true); // if we reached this statement, no exception has been thrown => OK test
    }

    public function provideValidAssertion(): iterable
    {
        yield 'Unchanged message' => [
            DummyMessage::class,
            $this->getSerializer()->serialize(new DummyMessage('Hello world')),
            <<<JSON
            [
              {
                "name": "content",
                "type": "string",
                "isNullable": false
              }
            ]
            JSON,
        ];

        yield 'Number property has been switched to nullable' => [
            DummyMessageWithNullableNumberProperty::class,
            $this->getSerializer()->serialize(new DummyMessageWithNullableNumberProperty('Hello world')),
            <<<JSON
            [
              {
                "name": "content",
                "type": "string",
                "isNullable": false
              },
              {
                "name": "number",
                "type": "int",
                "isNullable": false
              }
            ]
            JSON,
        ];
    }

    public function testItThrowsExceptionWhenAPropertyHasBeenRemoved(): void
    {
        [$serializedMessage, $jsonProperties] = $this->getSerializedMessageForPreviousVersionOfDummyMessageWithNumberProperty();

        $propertyList = PropertyList::fromJson($jsonProperties);
        self::assertTrue($propertyList->has('content'));
        self::assertTrue($propertyList->has('number'));
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('⚠️ The properties "number" in class "Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage" seems to have been removed');

        $sut = $this->getSut();
        $sut->assert(DummyMessage::class, $serializedMessage, $propertyList);
    }

    private function getSerializedMessageForPreviousVersionOfDummyMessageWithNumberProperty(): array
    {
        $jsonProperties = <<<JSON
          [
            {
              "name": "content",
              "type": "string",
              "isNullable": false
            },
            {
              "name": "number",
              "type": "int",
              "isNullable": false
            }
          ]
        JSON;

        return
            [
                $this->getSerializer()->serialize(new DummyMessage('Hello world')),
                $jsonProperties,
            ];
    }

    public function testItThrowsExceptionForInvalidIntegrationDueToClassMismatch(): void
    {
        $sut = $this->getSut();

        try {
            $sut->assert(DummyMessage::class, $this->getSerializer()->serialize(new \stdClass()), new PropertyList());
            $this->fail('This test should raised expected exception');
        } catch (\Throwable $t) {
            $this->assertInstanceOf(\LogicException::class, $t);
            $this->assertStringContainsString('Class mismatch', $t->getMessage());
            $this->assertStringContainsString(DummyMessage::class, $t->getMessage());
        }
    }

    public function testItThrowsExceptionForInvalidIntegrationDueToPropertyTypeMismatch(): void
    {
        // Simulate error using 'int' typeHint instead of 'string'
        $serializedMessage = $this->getSerializer()->serialize(new DummyMessage('Hello world'));
        $propertyList = PropertyList::fromJson(<<<JSON
        [
           {
              "name": "content",
              "type": "int",
              "isNullable": false
            }
        ]
        JSON
        );
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Error for property "content" in class "Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage", the type mismatch between the old and the new version of class. Please verify your integration.');

        $sut = $this->getSut();
        $sut->assert(DummyMessage::class, $serializedMessage, $propertyList);
    }

    public function testItThrowsExceptionForInvalidPropertyType(): void
    {
        $serializedMessage = $this->getSerializer()->serialize(new DummyMessageWithWrongPropertyType(22));
        $serializedMessage = \str_replace('DummyMessageWithWrongPropertyType', 'DummyMessage', $serializedMessage);

        $sut = $this->getSut();
        try {
            $sut->assert(DummyMessage::class, $serializedMessage, new PropertyList());
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
