<?php

namespace Yousign\ZddMessageBundle\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config\MessageConfig;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;

class ValidateZddMessageCommandTest extends KernelTestCase
{
    private CommandTester $command;
    private string $serializedMessagesDir;

    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->command = new CommandTester((new Application(self::$kernel))->find('yousign:zdd-message:validate'));
        $customBasePathFile = $kernel->getContainer()->getParameter('custom_path_file');
        $this->serializedMessagesDir = $customBasePathFile.'/Yousign/ZddMessageBundle/Tests/Fixtures/App/Messages';

        MessageConfig::$messagesToAssert = [
            DummyMessage::class,
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->serializedMessagesDir);
        MessageConfig::reset();
    }

    public function getSerializer(): ZddMessageMessengerSerializer
    {
        return new ZddMessageMessengerSerializer(new PhpSerializer());
    }

    public function testThatCommandIsSuccessful(): void
    {
        mkdir($this->serializedMessagesDir);
        file_put_contents($this->serializedMessagesDir.'/DummyMessage.txt', $this->getSerializer()->serialize(new DummyMessage('Hi')));
        file_put_contents($this->serializedMessagesDir.'/DummyMessage.properties.json', '[{"name":"content","type":"string"}]');
        $this->assertSerializedFilesExist($this->serializedMessagesDir);

        $this->command->execute([]);
        $this->command->assertCommandIsSuccessful();

        $expectedResult = <<<EOF
         --- ------------------------------------------------------------------- ---------------- 
          #   Message                                                             ZDD Compliant?  
         --- ------------------------------------------------------------------- ---------------- 
          1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage   Yes ✅          
         --- ------------------------------------------------------------------- ----------------
        EOF;

        $this->assertSame(trim($expectedResult), trim($this->command->getDisplay()));
    }

    public function testThatCommandIsSuccessfulEvenIfTheSerializedMessageDoesNotExists(): void
    {
        $this->assertFileDoesNotExist($this->serializedMessagesDir.'/DummyMessage.txt');

        $this->command->execute([]);

        $this->command->assertCommandIsSuccessful();

        $expectedResult = <<<EOF
         --- ------------------------------------------------------------------- ---------------- 
          #   Message                                                             ZDD Compliant?  
         --- ------------------------------------------------------------------- ---------------- 
          1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage   Yes ✅          
         --- ------------------------------------------------------------------- ----------------
        EOF;

        $this->assertSame(trim($expectedResult), trim($this->command->getDisplay()));
    }

    public function testThatCommandFailsWhenMessageIsNotZddCompliant(): void
    {
        $serializedMessage = $this->getSerializedMessageForPreviousVersionOfDummyMessageWithNumberProperty();
        $data = [
            [
                'name' => 'content',
                'type' => 'string',
            ],
            [
                'name' => 'number',
                'type' => 'int',
            ],
        ];
        mkdir($this->serializedMessagesDir);
        file_put_contents($this->serializedMessagesDir.'/DummyMessage.txt', $serializedMessage);
        file_put_contents($this->serializedMessagesDir.'/DummyMessage.properties.json', json_encode($data));
        $this->assertSerializedFilesExist($this->serializedMessagesDir);

        $this->command->execute([]);

        self::assertEquals(Command::FAILURE, $this->command->getStatusCode());
        $expectedResult = <<<EOF
         --- ------------------------------------------------------------------- ---------------- 
          #   Message                                                             ZDD Compliant?  
         --- ------------------------------------------------------------------- ---------------- 
          1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage   No ❌           
         --- ------------------------------------------------------------------- ---------------- 
        
         ! [NOTE] 1 error(s) triggered.
        EOF;

        $this->assertSame(trim($expectedResult), trim($this->command->getDisplay()));
    }

    public function testThatErrorTableIsShownInVerboseMode(): void
    {
        $serializedMessage = $this->getSerializedMessageForPreviousVersionOfDummyMessageWithNumberProperty();
        $data = [
            [
                'name' => 'content',
                'type' => 'string',
            ],
            [
                'name' => 'number',
                'type' => 'int',
            ],
        ];
        mkdir($this->serializedMessagesDir);
        file_put_contents($this->serializedMessagesDir.'/DummyMessage.txt', $serializedMessage);
        file_put_contents($this->serializedMessagesDir.'/DummyMessage.properties.json', json_encode($data));
        $this->assertSerializedFilesExist($this->serializedMessagesDir);

        $this->command->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        self::assertEquals(Command::FAILURE, $this->command->getStatusCode());
        $expectedResult = <<<EOF
         --- ------------------------------------------------------------------- ---------------- 
          #   Message                                                             ZDD Compliant?  
         --- ------------------------------------------------------------------- ---------------- 
          1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage   No ❌           
         --- ------------------------------------------------------------------- ---------------- 
        
         ! [NOTE] 1 error(s) triggered.

         ------------------------------------------------------------------- -------------- 
          Message                                                             Error         
         ------------------------------------------------------------------- -------------- 
          Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage   Syntax error  
         ------------------------------------------------------------------- --------------
        EOF;

        $this->assertSame(
            preg_replace('/\s+$/m', '', $expectedResult),
            preg_replace('/\s+$/m', '', $this->command->getDisplay())
        );
    }

    private function getSerializedMessageForPreviousVersionOfDummyMessageWithNumberProperty(): string
    {
        return
            <<<TXT
            O:65:"Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage":1:{s:74:" Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage content";s:11:"Hello world";}
            TXT;
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
