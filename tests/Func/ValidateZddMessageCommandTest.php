<?php

namespace Yousign\ZddMessageBundle\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
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
        $this->command = new CommandTester((new Application($kernel))->find('yousign:zdd-message:validate'));
        $this->serializedMessagesDir = __DIR__.'/../Fixtures/App/tmp/serialized_messages_directory';

        MessageConfig::$messagesToAssert = [
            DummyMessage::class => new DummyMessage('Hi'),
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
        $baseDirectory = $this->serializedMessagesDir.'/Yousign/ZddMessageBundle/Tests/Fixtures/App/Messages';

        mkdir($baseDirectory, recursive: true);
        file_put_contents($baseDirectory.'/DummyMessage.txt', $this->getSerializer()->serialize(new DummyMessage('Hi')));
        file_put_contents($baseDirectory.'/DummyMessage.properties.json', '[{"name":"content","type":"string", "children":[]}]');
        $this->assertSerializedFilesExist($baseDirectory);

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
        $baseDirectory = $this->serializedMessagesDir.'/Yousign/ZddMessageBundle/Tests/Fixtures/App/Messages';

        $this->assertFileDoesNotExist($baseDirectory.'/DummyMessage.txt');

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
        $baseDirectory = $this->serializedMessagesDir.'/Yousign/ZddMessageBundle/Tests/Fixtures/App/Messages';

        $serializedMessage = $this->getSerializedMessageForPreviousVersionOfDummyMessageWithNumberProperty();
        $data = [
            [
                'name' => 'content',
                'type' => 'string',
                'children' => [],
            ],
            [
                'name' => 'number',
                'type' => 'int',
                'children' => [],
            ],
        ];
        mkdir($baseDirectory, recursive: true);
        file_put_contents($baseDirectory.'/DummyMessage.txt', $serializedMessage);
        file_put_contents($baseDirectory.'/DummyMessage.properties.json', json_encode($data));
        $this->assertSerializedFilesExist($baseDirectory);

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
