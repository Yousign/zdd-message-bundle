<?php

namespace Youtrust\ZddMessageBundle\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LegacyCommandAliasTest extends KernelTestCase
{
    public static function legacyNameProvider(): \Generator
    {
        yield 'generate' => ['yousign:zdd-message:generate', 'youtrust:zdd-message:generate'];
        yield 'validate' => ['yousign:zdd-message:validate', 'youtrust:zdd-message:validate'];
        yield 'debug' => ['yousign:zdd-message:debug', 'youtrust:zdd-message:debug'];
    }

    /**
     * @dataProvider legacyNameProvider
     */
    public function testLegacyNameStillResolves(string $legacyName, string $expectedName): void
    {
        $application = new Application(self::bootKernel());

        $this->assertSame($expectedName, $application->find($legacyName)->getName());
    }
}
