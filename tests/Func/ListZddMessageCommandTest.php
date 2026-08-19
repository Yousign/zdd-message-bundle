<?php

namespace Youtrust\ZddMessageBundle\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListZddMessageCommandTest extends KernelTestCase
{
    private CommandTester $command;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->command = new CommandTester((new Application($kernel))->find('youtrust:zdd-message:debug'));
    }

    public function testCommandIsSuccess(): void
    {
        $this->command->execute([]);

        $expectedResult = <<<EOF
         --- -------------------------- List of tracked messages for the zdd ------------------------------ 
          #   Message                                                                                       
         --- ---------------------------------------------------------------------------------------------- 
          1   Youtrust\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage                            
          2   Youtrust\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty  
          3   Youtrust\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor      
          4   Youtrust\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes         
          5   Youtrust\ZddMessageBundle\Tests\Fixtures\App\Messages\Other\DummyMessage                      
          6   Youtrust\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyCustomMessage                      
         --- ----------------------------------------------------------------------------------------------  
        EOF;

        $this->assertSame(trim($expectedResult), trim($this->command->getDisplay()));
    }
}
