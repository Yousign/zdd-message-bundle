<?php

namespace Yousign\ZddMessageBundle\Tests\Unit\Listener\Symfony;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Yousign\ZddMessageBundle\Factory\ZddMessageCollection;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;
use Yousign\ZddMessageBundle\Factory\ZddPropertyExtractor;
use Yousign\ZddMessageBundle\Listener\Symfony\MessengerListener;
use Yousign\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Logger\SpyLogger;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Config\MessageConfig;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithAllManagedTypes;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessageWithNullableNumberProperty;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Locale;
use Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input\Status;
use Yousign\ZddMessageBundle\Tests\Unit\SerializerTrait;

class MessengerListenerTest extends TestCase
{
    use SerializerTrait;

    public function provideTrackedMessages(): iterable
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
                Status::DRAFT,
            ),
        ];

        yield DummyMessageWithNullableNumberProperty::class => [
            new DummyMessageWithNullableNumberProperty('Hello World'),
        ];
    }

    /**
     * @dataProvider provideTrackedMessages
     */
    public function testOnMessageReceivedLogNothingWhenMessageIsTracked(object $message): void
    {
        $messageFactory = new ZddMessageFactory(
            $this->getSerializer(),
            new ZddPropertyExtractor(),
        );

        MessageConfig::$messagesToAssert = [
            $message::class => $message,
        ];

        $messageListener = new MessengerListener(
            $spyLogger = new SpyLogger(),
            $messageFactory,
            new ZddMessageCollection(
                new MessageConfig(),
                $messageFactory
            ),
            'warning',
        );

        $event = new WorkerMessageReceivedEvent(new Envelope($message), 'receiver');

        $messageListener->onMessageReceived($event);

        self::assertEmpty($spyLogger->getLogs());
    }

    public function provideUntrackedMessages(): iterable
    {
        yield OtherDummyMessage::class => [
            new OtherDummyMessage('Smaone', new \DateTime()),
            OtherDummyMessage::class,
            'debug',
        ];

        yield 'OtherDummyMessage embedded in OtherDummyMessageAsEnveloppe' => [
            new OtherDummyMessageAsEnveloppe(new OtherDummyMessage('Smaone', new \DateTime())),
            OtherDummyMessage::class,
            'error',
        ];
    }

    /**
     * @dataProvider provideUntrackedMessages
     */
    public function testOnMessageReceivedLogMessageWhenMessageIsNotTracked(object $message, string $class, string $logLevel): void
    {
        $messageFactory = new ZddMessageFactory(
            $this->getSerializer(),
            new ZddPropertyExtractor(),
        );

        MessageConfig::reset();

        $messageListener = new MessengerListener(
            $spyLogger = new SpyLogger(),
            $messageFactory,
            new ZddMessageCollection(
                new MessageConfig(),
                $messageFactory
            ),
            $logLevel,
        );

        $event = new WorkerMessageReceivedEvent(new Envelope($message), 'receiver');

        $messageListener->onMessageReceived($event);

        self::assertTrue($spyLogger->hasRecord(
            'Untracked {class} has been detected, add it in your configuration to ensure ZDD compliance.',
            $logLevel,
            [
                'class' => $class,
            ]
        ));
    }

    public function testOnMessageReceivedLogNothingWhenGetMessageIsNotAnObject(): void
    {
        $messageFactory = new ZddMessageFactory(
            $this->getSerializer(),
            new ZddPropertyExtractor(),
        );

        MessageConfig::reset();

        $messageListener = new MessengerListener(
            $spyLogger = new SpyLogger(),
            $messageFactory,
            new ZddMessageCollection(
                new MessageConfig(),
                $messageFactory
            ),
            'warning',
        );

        $message = new class() {
            public function getMessage(): string
            {
                return 'App\Foo\Class\Not\Exist';
            }
        };
        $event = new WorkerMessageReceivedEvent(new Envelope($message), 'receiver');

        $messageListener->onMessageReceived($event);

        self::assertEmpty($spyLogger->getLogs());
    }

    public function testOnMessageReceivedLogAWarningWhenAnErrorOccurs(): void
    {
        $messageFactory = new ZddMessageFactory(
            $this->getSerializer(),
            new ZddPropertyExtractor(),
        );

        MessageConfig::reset();

        $messageListener = new MessengerListener(
            $spyLogger = new SpyLogger(),
            $messageFactory,
            new ZddMessageCollection(
                new MessageConfig(),
                $messageFactory
            ),
            'warning',
        );

        $message = new class() {
            public function getMessage()
            {
                throw new \Exception('dummy error');
            }
        };
        $event = new WorkerMessageReceivedEvent(new Envelope($message), 'receiver');

        $messageListener->onMessageReceived($event);

        self::assertTrue($spyLogger->hasRecord(
            'An error occurred when comparing the consumed message to the messages in `ZddMessageConfigInterface::getMessageToAssert`',
            'warning',
            ['dummy error'],
        ));
    }
}

class OtherDummyMessage
{
    public function __construct(private readonly string $name, private readonly \DateTime $createdAt)
    {
    }
}

class OtherDummyMessageAsEnveloppe
{
    public function __construct(private readonly object $message)
    {
    }

    public function getMessage(): object
    {
        return $this->message;
    }
}
