<?php

namespace Yousign\ZddMessageBundle\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config\MessageConfig;

class GenerateZddMessageCommandTest extends KernelTestCase
{
    private CommandTester $command;
    private string $serializedMessagesDir;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->command = new CommandTester((new Application($kernel))->find('yousign:zdd-message:generate'));
        $this->serializedMessagesDir = __DIR__.'/../Fixtures/App/tmp/serialized_messages_directory';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->serializedMessagesDir);
        MessageConfig::reset();
    }

    public function testThatCommandIsSuccessful(): void
    {
        $baseDirectory = $this->serializedMessagesDir.'/Yousign/ZddMessageBundle/Tests/Fixtures/App/Messages';

        $this->assertDirectoryDoesNotExist($baseDirectory);

        $this->command->execute([]);

        $this->assertDirectoryExists($baseDirectory);
        $this->assertSerializedFilesExist($baseDirectory);

        $expectedResult = <<<EOF
         --- --------------------------------------------------------------------------------------------- 
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

    private function assertSerializedFilesExist(string $baseDirectory): void
    {
        /** @var ZddMessageConfigInterface $messageConfig */
        $messageConfig = self::$kernel->getContainer()->get(MessageConfig::class);
        foreach ($messageConfig->getMessageToAssert() as $message) {
            $shortName = (new \ReflectionClass($message))->getShortName();
            $this->assertFileExists($baseDirectory.'/'.$shortName.'.txt');
            $this->assertFileExists($baseDirectory.'/'.$shortName.'.properties.json');
        }
    }
}
