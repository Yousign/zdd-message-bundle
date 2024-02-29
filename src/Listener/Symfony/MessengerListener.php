<?php

namespace Yousign\ZddMessageBundle\Listener\Symfony;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Yousign\ZddMessageBundle\Factory\ZddMessageCollection;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;

final class MessengerListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ZddMessageFactory $messageFactory,
        private readonly ZddMessageCollection $zddMessageCollection,
        private readonly string $logLevel = 'warning',
    ) {
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        try {
            $message = $event->getEnvelope()->getMessage();

            // In case of $message act like an envelope.
            if (method_exists($message, 'getMessage')) {
                $message = $message->getMessage();
            }

            if (!is_object($message)) {
                return;
            }

            $zddMessage = $this->messageFactory->create(
                $message::class,
                $message,
            );

            if (!$this->zddMessageCollection->fingerprintExists($zddMessage->getFingerprint())) {
                $this->logger->log(
                    $this->logLevel,
                    'Untracked {class} has been detected, add it in your configuration to ensure ZDD compliance.',
                    [
                        'class' => $message::class,
                    ],
                );
            }
        } catch (\Throwable $throwable) {
            // The listener should not throw an exception.
            $this->logger->log(
                'warning',
                'An error occurred when comparing the consumed message to the messages in `ZddMessageConfigInterface::getMessageToAssert`', [
                $throwable->getMessage(),
            ]);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => 'onMessageReceived',
        ];
    }
}
