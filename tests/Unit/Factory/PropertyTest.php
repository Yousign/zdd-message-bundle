<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Yousign\ZddMessageBundle\Factory\Property;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Command;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumInt;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumString;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Wrapper;

class PropertyTest extends TestCase
{
    public function testJsonSerialization(): void
    {
        // Given
        $property = new Property('command', Command::class, [
            new Property('name', 'string', []),
            new Property('string', EnumString::class, [
                new Property('name', 'string', []),
                new Property('value', 'string', []),
            ]),
            new Property('int', EnumInt::class, [
                new Property('name', 'string', []),
                new Property('value', 'int', []),
            ]),
        ]);

        // When
        $jsonMessage = json_encode($property);
        $decodedJson = json_decode($jsonMessage, true);
        $decodedMessage = Property::fromArray($decodedJson);

        // Then
        $this->assertEquals($property, $decodedMessage);
    }

    /**
     * @dataProvider provideProperties
     */
    public function testGetFingerprint(Property $property, string $expectedFingerprint): void
    {
        // When
        $fingerprint = $property->getFingerprint();

        // Then
        $this->assertEquals($expectedFingerprint, $fingerprint);
    }

    public function provideProperties(): iterable
    {
        yield [
            new Property('myString', 'string', []),
            'myString:string',
        ];

        yield [
            new Property('myString', EnumString::class, [
                new Property('name', 'string', []),
                new Property('value', 'string', []),
            ]),
            'myString:Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumString(name:string,value:string)',
        ];

        yield [
            new Property('myInt', EnumInt::class, [
                new Property('name', 'string', []),
                new Property('value', 'int', []),
            ]),
            'myInt:Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumInt(name:string,value:int)',
        ];

        yield [
            new Property('command', Command::class, [
                new Property('name', 'string', []),
                new Property('myString', EnumString::class, [
                    new Property('name', 'string', []),
                    new Property('value', 'string', []),
                ]),
                new Property('myInt', EnumInt::class, [
                    new Property('name', 'string', []),
                    new Property('value', 'int', []),
                ]),
            ]),
            'command:Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Command(name:string,myString:Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumString(name:string,value:string),myInt:Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumInt(name:string,value:int))',
        ];

        yield [
            new Property('wrapper', Wrapper::class, [
                new Property('command', Command::class, [
                    new Property('name', 'string', []),
                    new Property('myString', EnumString::class, [
                        new Property('name', 'string', []),
                        new Property('value', 'string', []),
                    ]),
                    new Property('myInt', EnumInt::class, [
                        new Property('name', 'string', []),
                        new Property('value', 'int', []),
                    ]),
                ]),
            ]),
            'wrapper:Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Wrapper(command:Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Command(name:string,myString:Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumString(name:string,value:string),myInt:Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumInt(name:string,value:int)))',
        ];
    }
}
