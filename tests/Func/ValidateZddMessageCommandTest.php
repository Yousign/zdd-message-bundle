<?php

namespace Yousign\ZddMessageBundle\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Yousign\ZddMessageBundle\Factory\Property;
use Yousign\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer;

class ValidateZddMessageCommandTest extends KernelTestCase
{
    private CommandTester $generateCommand;
    private CommandTester $validateCommand;
    private string $serializedMessagesDir;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->generateCommand = new CommandTester((new Application($kernel))->find('yousign:zdd-message:generate'));
        $this->validateCommand = new CommandTester((new Application($kernel))->find('yousign:zdd-message:validate'));
        $this->serializedMessagesDir = __DIR__.'/../Fixtures/App/tmp/serialized_messages_directory';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->serializedMessagesDir);
    }

    public function getSerializer(): ZddMessageMessengerSerializer
    {
        return new ZddMessageMessengerSerializer(new PhpSerializer());
    }

    public function testThatCommandIsSuccessful(): void
    {
        $this->generateCommand->execute([]);

        $this->validateCommand->execute([]);
        $this->validateCommand->assertCommandIsSuccessful();

        $expectedResult = <<<EOF
         --- --------------------------------------------------------------------------------------------- ---------------- 
          #   Message                                                                                       ZDD Compliant?  
         --- --------------------------------------------------------------------------------------------- ---------------- 
          1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage                             Yes ✅          
          2   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty   Yes ✅          
          3   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor       Yes ✅          
          4   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithSafeDateTimeImmutable    Yes ✅          
          5   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes          Yes ✅          
          6   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Other\DummyMessage                       Yes ✅          
         --- --------------------------------------------------------------------------------------------- ----------------
        EOF;

        $this->assertSame(trim($expectedResult), trim($this->validateCommand->getDisplay()));
    }

    public function testThatCommandIsSuccessfulEvenIfTheSerializedMessageDoesNotExists(): void
    {
        $this->validateCommand->execute([]);
        $this->validateCommand->assertCommandIsSuccessful();

        $expectedResult = <<<EOF
         --- --------------------------------------------------------------------------------------------- ---------------- 
          #   Message                                                                                       ZDD Compliant?  
         --- --------------------------------------------------------------------------------------------- ---------------- 
          1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage                             Yes ✅          
          2   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty   Yes ✅          
          3   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor       Yes ✅          
          4   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithSafeDateTimeImmutable    Yes ✅          
          5   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes          Yes ✅          
          6   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Other\DummyMessage                       Yes ✅          
         --- --------------------------------------------------------------------------------------------- ----------------
        EOF;

        $this->assertSame(trim($expectedResult), trim($this->validateCommand->getDisplay()));
    }

    public function testThatCommandFailsWhenMessageIsNotZddCompliant(): void
    {
        $baseDirectory = $this->serializedMessagesDir.'/Yousign/ZddMessageBundle/Tests/Fixtures/App/Messages';
        $serializedMessage = $this->getSerializedMessageForPreviousVersionOfDummyMessageWithNumberProperty();

        $this->generateCommand->execute([]);

        file_put_contents($baseDirectory.'/DummyMessage.txt', $serializedMessage);
        file_put_contents($baseDirectory.'/DummyMessage.properties.json', json_encode([
            new Property('content', 'string', []),
            new Property('number', 'int', []),
        ]));

        $this->validateCommand->execute([]);
        $this->validateCommand->execute([]);

        self::assertEquals(Command::FAILURE, $this->validateCommand->getStatusCode());
        $expectedResult = <<<EOF
         --- --------------------------------------------------------------------------------------------- ---------------- 
          #   Message                                                                                       ZDD Compliant?  
         --- --------------------------------------------------------------------------------------------- ---------------- 
          1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage                             No ❌           
          2   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty   Yes ✅          
          3   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithPrivateConstructor       Yes ✅          
          4   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithSafeDateTimeImmutable    Yes ✅          
          5   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes          Yes ✅          
          6   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Other\DummyMessage                       Yes ✅          
         --- --------------------------------------------------------------------------------------------- ---------------- 
        
         ! [NOTE] 1 error(s) triggered.
        EOF;

        $this->assertSame(trim($expectedResult), trim($this->validateCommand->getDisplay()));
    }

    private function getSerializedMessageForPreviousVersionOfDummyMessageWithNumberProperty(): string
    {
        return
            <<<TXT
            O:65:"Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage":1:{s:74:" Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage content";s:11:"Hello world";}
            TXT;
    }
}
