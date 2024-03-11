<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Yousign\ZddMessageBundle\Factory\ZddPropertyExtractor;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Command;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumInt;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumString;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Wrapper;

class ZddPropertyExtractorTest extends TestCase
{
    public function testExtractProperties(): void
    {
        // Given
        $extractor = new ZddPropertyExtractor();
        $message = new Wrapper(
            new Command(
                'name',
                EnumString::A,
                EnumInt::T1,
            ),
        );

        // When
        $properties = $extractor->extractProperties($message);

        // Then
        $this->assertCount(1, $properties);

        // wapper.command
        $propertyCommand = $properties[0];
        $this->assertSame('command', $propertyCommand->name);
        $this->assertSame(Command::class, $propertyCommand->type);
        $this->assertCount(3, $propertyCommand->children);

        // wapper.command.name
        $childName = $propertyCommand->children[0];
        $this->assertSame('name', $childName->name);
        $this->assertSame('string', $childName->type);
        $this->assertCount(0, $childName->children);

        // wapper.command.stringType
        $childStringType = $propertyCommand->children[1];
        $this->assertSame('myString', $childStringType->name);
        $this->assertSame(EnumString::class, $childStringType->type);
        $this->assertCount(2, $childStringType->children);

        // wapper.command.stringType.name
        $propertyName = $childStringType->children[0];
        $this->assertSame('name', $propertyName->name);
        $this->assertSame('string', $propertyName->type);
        $this->assertCount(0, $propertyName->children);

        // wapper.command.stringType.value
        $propertyValue = $childStringType->children[1];
        $this->assertSame('value', $propertyValue->name);
        $this->assertSame('string', $propertyValue->type);
        $this->assertCount(0, $propertyValue->children);

        // wapper.command.intType
        $childIntType = $propertyCommand->children[2];
        $this->assertSame('myInt', $childIntType->name);
        $this->assertSame(EnumInt::class, $childIntType->type);
        $this->assertCount(2, $childIntType->children);

        // wapper.command.intType.value
        $propertyName = $childIntType->children[0];
        $this->assertSame('name', $propertyName->name);
        $this->assertSame('string', $propertyName->type);
        $this->assertCount(0, $propertyName->children);

        // wapper.command.intType.value
        $propertyValue = $childIntType->children[1];
        $this->assertSame('value', $propertyValue->name);
        $this->assertSame('integer', $propertyValue->type);
        $this->assertCount(0, $propertyValue->children);
    }
}
