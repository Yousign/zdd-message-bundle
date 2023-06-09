<?php

namespace Yousign\ZddMessageBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yousign\ZddMessageBundle\Assert\ZddMessageAssert;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;

class ZddMessageAssertTest extends TestCase
{
    /**
     * @param class-string $messageFqcn
     *
     * @dataProvider provideValidAssertion
     */
    public function testItAssertsWithSuccess(
        string $messageFqcn,
        string $serializedMessage,
        array $notNullableProperties
    ): void {
        ZddMessageAssert::assert($messageFqcn, $serializedMessage, $notNullableProperties);
        self::assertTrue(true); // if we reached this statement, no exception has been thrown => OK test
    }

    public function provideValidAssertion(): iterable
    {
        yield 'Unchanged message' => [
            DummyMessage::class,
            serialize(new DummyMessage('Hello world')),
            [
                'content' => 'string',
            ],
        ];

        yield 'Number property has been switched to nullable' => [
            DummyMessageWithNullableNumberProperty::class,
            <<<TXT
            O:91:"Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty":2:{s:100:" Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty content";s:12:"Hello World!";s:99:" Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty number";N;}
            TXT,
            [
                'content' => 'string',
                'number' => 'int',
            ],
        ];
    }

    public function testItThrowsExceptionWhenANotNullablePropertyHasBeenRemoved(): void
    {
        [$serializedMessage, $notNullableProperties] = $this->getSerializedMessageAndNotNullablePropertiesForPreviousVersionOfDummyMessageWithNumberProperty();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The properties "number" in class "Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage" seems to have been removed, make it nullable first, deploy it and then remove it 🔥');

        ZddMessageAssert::assert(DummyMessage::class, $serializedMessage, $notNullableProperties);
    }

    private function getSerializedMessageAndNotNullablePropertiesForPreviousVersionOfDummyMessageWithNumberProperty(): array
    {
        return [
            <<<TXT
            O:65:"Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage":1:{s:74:" Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage content";s:11:"Hello world";}
            TXT,
            [
                'content' => 'string',
                'number' => 'int',
            ],
        ];
    }

    public function testItThrowsExceptionForInvalidIntegrationDueToClassMismatch(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Class mismatch between $messageFqcn: "Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage" and $serializedMessage: "O:8:"stdClass":0:{}". Please verify your integration.');

        ZddMessageAssert::assert(DummyMessage::class, serialize(new \stdClass()), []);
    }

    public function testItThrowsExceptionForInvalidIntegrationDueToPropertyTypeMismatch(): void
    {
        $serializedMessage = serialize(new DummyMessage('Hello world'));
        // Simulate error using 'int' typeHint instead of 'string'
        $notNullableProperties = ['content' => 'int'];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Property type mismatch between properties from $messageFqcn class and $notNullableProperties. Please verify your integration.');

        ZddMessageAssert::assert(DummyMessage::class, $serializedMessage, $notNullableProperties);
    }
}
