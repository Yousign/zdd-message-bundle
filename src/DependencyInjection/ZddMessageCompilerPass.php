<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Yousign\ZddMessageBundle\Command\GenerateZddMessageCommand;
use Yousign\ZddMessageBundle\Command\ListZddMessageCommand;
use Yousign\ZddMessageBundle\Command\ValidateZddMessageCommand;
use Yousign\ZddMessageBundle\Listener\Symfony\MessengerListener;

/**
 * @internal
 */
final class ZddMessageCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $ids = [];
        foreach ($container->findTaggedServiceIds('yousign.zdd.message.config') as $id => $service) {
            $ids[] = $id;
        }

        if (0 === \count($ids)) {
            return;
        }

        if (\count($ids) > 1) {
            throw new \LogicException('Only one instance of ZddMessageConfigInterface allowed.');
        }

        $zddMessageConfig = $ids[0];
        $container
            ->setDefinition(
                'yousign_generate_zdd_message_command',
                new Definition(GenerateZddMessageCommand::class)
            )
            ->addTag('console.command')
            ->setArguments([
                $container->getParameter('yousign.zdd.message.serialized_messages_dir'),
                new Reference($zddMessageConfig),
            ])
        ;

        $container
            ->setDefinition(
                'yousign_validate_zdd_message_command',
                new Definition(ValidateZddMessageCommand::class)
            )
            ->addTag('console.command')
            ->setArguments([
                $container->getParameter('yousign.zdd.message.serialized_messages_dir'),
                new Reference($zddMessageConfig),
            ])
        ;

        $container
            ->setDefinition(
                'yousign_list_tracked_message_command',
                new Definition(ListZddMessageCommand::class)
            )
            ->addTag('console.command')
            ->setArguments([
                new Reference($zddMessageConfig),
            ])
        ;

        if (!class_exists(WorkerMessageReceivedEvent::class)) {
            return;
        }

        if (true === $container->getParameter('yousign.zdd.message.log_untracked_messages.messenger.enable')) {
            $container
                ->setDefinition(
                    'yousign_symfony_messenger_listener',
                    new Definition(MessengerListener::class)
                )
                ->addTag('kernel.event_subscriber')
                ->setArguments([
                    new Reference('logger'),
                    new Reference($zddMessageConfig),
                    $container->getParameter('yousign.zdd.message.log_untracked_messages.messenger.level'),
                ])
            ;
        }
    }
}
