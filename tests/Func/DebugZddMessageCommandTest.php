<?php

namespace Yousign\ZddMessageBundle\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DebugZddMessageCommandTest extends KernelTestCase
{
    private CommandTester $command;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->command = new CommandTester((new Application($kernel))->find('yousign:zdd-message:debug'));
    }

    public function testCommandIsSuccess(): void
    {
        $this->command->execute([]);

        $expectedResult = <<<EOF
         --- ------------------------- List of tracked messages for the zdd ------------------------------ 
          #   Message                                                                                      
         --- --------------------------------------------------------------------------------------------- 
          1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage                            
          2   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty  
          3   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor      
          4   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes         
          5   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Other\DummyMessage                      
         --- ---------------------------------------------------------------------------------------------  
        EOF;

        $this->assertSame(trim($expectedResult), trim($this->command->getDisplay()));
    }
}
