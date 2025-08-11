<?php

namespace Yousign\ZddMessageBundle\Listener\Symfony;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;

final class MessengerListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ZddMessageConfigInterface $config,
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

            if (is_object($message) && !in_array($class = get_class($message), $this->config->getMessageToAssert(), true)) {
                $this->logger->log(
                    $this->logLevel,
                    'Untracked {class} has been detected, add it in your configuration to ensure ZDD compliance.',
                    [
                        'class' => $class,
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
