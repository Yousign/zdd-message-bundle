<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Yousign\ZddMessageBundle\Command\ListTrackedMessageCommand;
use Yousign\ZddMessageBundle\Command\ZddMessageCommand;

final class ZddMessageCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $ids = [];
        foreach ($container->findTaggedServiceIds('yousign.zdd.message.config') as $id => $service) {
            $ids[] = $id;
        }

        if (1 !== \count($ids)) {
            throw new \LogicException('Only one instance of ZddMessageConfigInterface allowed.');
        }

        $container
            ->setDefinition(
                'yousign_zdd_message_command',
                new Definition(ZddMessageCommand::class)
            )
            ->addTag('console.command')
            ->setArguments([
                $container->getParameter('yousign.zdd.message.serialized_messages_dir'),
                new Reference($ids[0]),
            ])
        ;

        $container
            ->setDefinition(
                'yousign_list_tracked_message_command',
                new Definition(ListTrackedMessageCommand::class)
            )
            ->addTag('console.command')
            ->setArguments([
                new Reference($ids[0]),
            ])
        ;
    }
}
