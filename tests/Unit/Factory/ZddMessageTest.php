<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Yousign\ZddMessageBundle\Factory\Property;
use Yousign\ZddMessageBundle\Factory\ZddMessage;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;
use Yousign\ZddMessageBundle\Factory\ZddPropertyExtractor;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Command;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumInt;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumString;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Wrapper;

class ZddMessageTest extends TestCase
{
    public function testJsonSerialization(): void
    {
        // Given
        $message = new ZddMessage(
            Wrapper::class,
            Wrapper::class,
            'serializedMessage',
            [
                new Property('command', Command::class, [
                    new Property('name', 'string', []),
                    new Property('string', EnumString::class, [
                        new Property('name', 'string', []),
                        new Property('value', 'string', []),
                    ]),
                    new Property('int', EnumInt::class, [
                        new Property('name', 'string', []),
                        new Property('value', 'int', []),
                    ]),
                ]),
            ],
        );

        // When
        $jsonMessage = json_encode($message);
        $decodedJson = json_decode($jsonMessage, true);
        $decodedMessage = ZddMessage::fromArray($decodedJson);

        // Then
        $this->assertEquals($message, $decodedMessage);
    }
}
