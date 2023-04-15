<?php

namespace Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config\MessageConfig;

class ListTrackedMessageCommandTest extends KernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $customBasePathFile = $kernel->getContainer()->getParameter('custom_path_file');
        $this->serializedMessagesDir = $customBasePathFile.'/Messages';

        (new Filesystem())->remove($this->serializedMessagesDir);
        MessageConfig::reset();
    }

    public function testCommandIsSuccess(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([]);

        $expectedResult = <<<EOF
         --- ------------------------- List of tracked messages for the zdd ------------------------------ 
          #   Message                                                                                      
         --- --------------------------------------------------------------------------------------------- 
          1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage                            
          2   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty  
          3   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor      
          4   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes         
         --- ---------------------------------------------------------------------------------------------  
        EOF;

        $this->assertSame(trim($expectedResult), trim($commandTester->getDisplay()));
    }

    private function getCommandTester(): CommandTester
    {
        $application = new Application(self::$kernel);

        return new CommandTester($application->find('yousign:zdd-message:debug'));
    }
}
