<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Factory\Property;
use Yousign\ZddMessageBundle\Factory\ZddMessage;
use Yousign\ZddMessageBundle\Factory\ZddMessageCollection;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;
use Yousign\ZddMessageBundle\Factory\ZddPropertyExtractor;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Command;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumInt;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\EnumString;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Wrapper;
use Yousign\ZddMessageBundle\Tests\Unit\SerializerTrait;

class ZddMessageCollectionTest extends TestCase
{
    use SerializerTrait;

    public function testFingerprintExists(): void
    {
        // Given
        $message1 = new Wrapper(new Command('name', EnumString::A, EnumInt::T1));
        $message2 = new Command('name', EnumString::A, EnumInt::T2);

        $collection = new ZddMessageCollection(
            new class($message1) implements ZddMessageConfigInterface {
                public function __construct(private $message1)
                {
                }

                public function getMessageToAssert(): \Generator
                {
                    yield Wrapper::class => $this->message1;
                }
            },
            $factory = new ZddMessageFactory(
                $this->getSerializer(),
                new ZddPropertyExtractor(),
            ),
        );

        $zddMessage1 = $factory->create($message1::class, $message1);
        $zddMessage2 = $factory->create($message2::class, $message2);

        // When / Then
        $this->assertTrue($collection->fingerprintExists($zddMessage1->getFingerprint()));
        $this->assertFalse($collection->fingerprintExists($zddMessage2->getFingerprint()));
    }
}
