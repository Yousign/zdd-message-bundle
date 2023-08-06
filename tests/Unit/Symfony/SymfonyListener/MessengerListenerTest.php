<?php

namespace Yousign\ZddMessageBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Event\Root;
use Symfony\Component\Messenger\Event\User;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Yousign\ZddMessageBundle\Listener\SymfonyMessenger\MessengerListener;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Logger\SpyLogger;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config\MessageConfig;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status;

class MessengerListenerTest extends TestCase
{
    public function provideTrackedMessage(): iterable
    {
        yield DummyMessage::class => [
            new DummyMessage('Hello World'),
        ];
        yield DummyMessageWithAllManagedTypes::class => [
            new DummyMessageWithAllManagedTypes(
                'Hello World',
                12,
                false,
                ['PHP', 'is', 'not', 'dead'],
                new Locale('fr'),
                Status::DRAFT
            ),
        ];
        yield DummyMessageWithNullableNumberProperty::class => [
            new DummyMessageWithNullableNumberProperty('Hello World'),
        ];
    }

    /**
     * @dataProvider provideTrackedMessage
     */
    public function testOnMessageReceivedLogNothingWhenMessageIsTracked(object $message): void
    {
        $messageListener = new MessengerListener(
            $spyLogger = new SpyLogger(),
            new MessageConfig(),
            'warning'
        );

        $event = new WorkerMessageReceivedEvent($message);

        $messageListener->onMessageReceived($event);

        self::assertNull($spyLogger->getLogs('warning'));
    }

    public function provideUnTrackedMessage(): iterable
    {
        yield Locale::class => [new Locale('en'), Locale::class, 'info'];
        yield User::class => [new User('Smaone', new \DateTime()), User::class, 'debug'];
        yield 'User embedded in Root' => [new Root(new User('Smaone', new \DateTime())), User::class, 'error'];
    }

    /**
     * @dataProvider provideUnTrackedMessage
     */
    public function testOnMessageReceivedLogWarningWhenMessageIsNotTracked(object $message, string $class, string $logLevel): void
    {
        $messageListener = new MessengerListener(
            $spyLogger = new SpyLogger(),
            new MessageConfig(),
            $logLevel
        );

        $event = new WorkerMessageReceivedEvent($message);

        $messageListener->onMessageReceived($event);

        $log = $spyLogger->getLogs($logLevel)['message'] ?? null;

        self::assertEquals(
            sprintf(
                'The message "%s" is not in `ZddMessageConfigInterface::getMessageToAssert` and it is not tested as ZDD compliant',
                $class
            ),
            $log
        );
    }

    public function testOnMessageReceivedLogNothingWhenGetMessageIsNotAnObject(): void
    {
        $messageListener = new MessengerListener(
            $spyLogger = new SpyLogger(),
            new MessageConfig(),
            'warning'
        );

        $message = new class() {
            public function getMessage()
            {
                return 'App\Foo\Class\Not\Exist';
            }
        };
        $event = new WorkerMessageReceivedEvent($message);

        $messageListener->onMessageReceived($event);

        self::assertNull($spyLogger->getLogs('warning'));
    }

    public function testOnMessageReceivedLogAWarningWhenAnErrorOccurs(): void
    {
        $messageListener = new MessengerListener(
            $spyLogger = new SpyLogger(),
            new MessageConfig(),
        );

        $message = new class() {
            public function getMessage()
            {
                throw new \Exception('dummy error');
            }
        };
        $event = new WorkerMessageReceivedEvent($message);

        $messageListener->onMessageReceived($event);

        $logMessage = $spyLogger->getLogs('warning')['message'] ?? null;
        $logContext = $spyLogger->getLogs('warning')['context'] ?? null;

        self::assertEquals(
            'An error occurred when comparing the consumed message to the messages in `ZddMessageConfigInterface::getMessageToAssert`',
            $logMessage
        );
        self::assertEquals(
            ['dummy error'],
            $logContext
        );
    }
}

namespace Symfony\Component\Messenger\Event;

class WorkerMessageReceivedEvent
{
    private $enveloppe;

    public function __construct(private readonly object $message)
    {
        $this->enveloppe = new Envelope($this->message);
    }

    public function getEnvelope()
    {
        return $this->enveloppe;
    }
}

class Envelope
{
    public function __construct(private readonly object $message)
    {
    }

    public function getMessage(): object
    {
        return $this->message;
    }
}

class User
{
    public function __construct(private readonly string $name, private readonly \DateTime $createdAt)
    {
    }
}

class Root
{
    public function __construct(private readonly object $message)
    {
    }

    public function getMessage(): object
    {
        return $this->message;
    }
}
