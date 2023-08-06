<?php

namespace Yousign\ZddMessageBundle\Listener\SymfonyMessenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;

final class MessengerListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ZddMessageConfigInterface $config,
        private readonly string $logLevel = 'warning'
    ) {
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        try {
            /** @var object $message */
            $message = $event->getEnvelope()->getMessage();
            // In case of $message act like an envelope.
            if (method_exists($message, 'getMessage')) {
                $message = $message->getMessage();
            }

            if (is_object($message) && !in_array($class = get_class($message), $this->config->getMessageToAssert(), true)) {
                $this->logger->log(
                    $this->logLevel,
                    sprintf(
                        'The message "%s" is not in `ZddMessageConfigInterface::getMessageToAssert` and it is not tested as ZDD compliant',
                        $class
                    ));
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
