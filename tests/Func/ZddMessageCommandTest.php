<?php

namespace Yousign\ZddMessageBundle\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config\MessageConfig;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\FooNotCompliantMessage;
use Yousign\ZddMessageBundle\ZddMessageConfigInterface;

class ZddMessageCommandTest extends KernelTestCase
{
    private const RESOURCE_DIR = __DIR__.'/../resources';

    private string $serializedMessagesDir;
    private string $serializedMessagesInputDir;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $customBasePathFile = $kernel->getContainer()->getParameter('custom_path_file');
        $this->serializedMessagesDir = $customBasePathFile.'/Messages';
        $this->serializedMessagesInputDir = $customBasePathFile.'/Input';

        (new Filesystem())->remove($this->serializedMessagesDir);
        (new Filesystem())->remove($this->serializedMessagesInputDir);
        MessageConfig::reset();
    }

    public function testCommandIsSuccess(): void
    {
        $commandTester = $this->getCommandTester();
        $this->assertDirectoryDoesNotExist($this->serializedMessagesDir);

        $commandTester->execute([
            'action' => 'serialize',
        ]);

        $this->assertDirectoryExists($this->serializedMessagesDir);
        $this->assertSerializedFilesExist($this->serializedMessagesDir);

        $commandTester->execute([
            'action' => 'validate',
        ]);

        $commandTester->assertCommandIsSuccessful();
        self::assertStringStartsWith(
            '[OK] Message "Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage" is ZDD compliant ✅',
            $this->getReadableOutput($commandTester)
        );
    }

    public function testCommandFailsWhenMessageIsNotZddCompliant(): void
    {
        MessageConfig::$messagesToAssert = [
            FooNotCompliantMessage::class,
        ];

        mkdir($this->serializedMessagesInputDir);
        $this->assertDirectoryExists($this->serializedMessagesInputDir);

        $this->moveNotCompliantFileInSerializedDir();
        $this->assertSerializedFilesExist($this->serializedMessagesInputDir);

        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'action' => 'validate',
        ]);

        self::assertEquals(Command::FAILURE, $commandTester->getStatusCode());
        self::assertStringStartsWith(
            '[ERROR] Message "Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages',
            $this->getReadableOutput($commandTester)
        );
    }

    public function testCommandIsInvalid(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'action' => 'invalid',
        ]);

        self::assertEquals(Command::INVALID, $commandTester->getStatusCode());
    }

    public function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->serializedMessagesDir);
        (new Filesystem())->remove($this->serializedMessagesInputDir);
    }

    private function getReadableOutput(CommandTester $commandTester): string
    {
        return trim(preg_replace('/  +/', ' ',
            str_replace(PHP_EOL, '', $commandTester->getDisplay())
        ));
    }

    private function assertSerializedFilesExist(string $baseDirectory): void
    {
        /** @var ZddMessageConfigInterface $messageConfig */
        $messageConfig = self::$kernel->getContainer()->get(MessageConfig::class);
        foreach ($messageConfig->getMessageToAssert() as $message) {
            $shortName = (new \ReflectionClass($message))->getShortName();
            $this->assertFileExists($baseDirectory.'/'.$shortName.'.txt');
            $this->assertFileExists($baseDirectory.'/'.$shortName.'.not_nullable_properties.json');
        }
    }

    private function moveNotCompliantFileInSerializedDir(): void
    {
        foreach (['FooNotCompliantMessage.not_nullable_properties.json', 'FooNotCompliantMessage.txt'] as $fileName) {
            file_put_contents(sprintf('%s/%s', $this->serializedMessagesInputDir, $fileName),
                file_get_contents(\sprintf('%s/%s', self::RESOURCE_DIR, $fileName))
            );
        }
    }

    private function getCommandTester(): CommandTester
    {
        $application = new Application(self::$kernel);

        return new CommandTester($application->find('yousign:zdd-message'));
    }
}
