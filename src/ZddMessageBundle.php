<?php

namespace Youtrust\ZddMessageBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Youtrust\ZddMessageBundle\Command\GenerateZddMessageCommand;
use Youtrust\ZddMessageBundle\Command\ListZddMessageCommand;
use Youtrust\ZddMessageBundle\Command\ValidateZddMessageCommand;
use Youtrust\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Youtrust\ZddMessageBundle\Listener\Symfony\MessengerListener;
use Youtrust\ZddMessageBundle\Serializer\SerializerInterface;
use Youtrust\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer;

final class ZddMessageBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
                ->children()
                    ->scalarNode('message_config_service')->defaultNull()->end()
                    ->scalarNode('serialized_messages_dir')->defaultNull()->end()
                    ->scalarNode('serializer')->defaultValue(ZddMessageMessengerSerializer::class)->end()
                    ->arrayNode('log_untracked_messages')
                        ->children()
                            ->arrayNode('messenger')
                                ->children()
                                    ->booleanNode('enable')->defaultFalse()->end()
                                    ->scalarNode('level')->defaultValue('warning')->end()
                                ->end()
                            ->end() // messenger
                        ->end()
                    ->end() // log_untracked_messages
                ->end()
            ->end()
        ;
    }

    /** @phpstan-ignore-next-line */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $serviceConfigurator = $container
            ->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure()
        ;

        $messageConfigServiceId = $config['message_config_service'];
        if (!$messageConfigServiceId) {
            throw new \LogicException(sprintf('You should configure zdd_message.message_config_service with a service that implements %s', ZddMessageConfigInterface::class));
        }

        $serviceConfigurator->bind('$zddMessageConfig', service($messageConfigServiceId));
        $serviceConfigurator->bind('$zddMessagePath', $config['serialized_messages_dir'] ?? $this->getDefaultPath($builder));

        $messengerEnable = $config['log_untracked_messages']['messenger']['enable'] ?? false;
        if ($messengerEnable) {
            $messengerLevel = $config['log_untracked_messages']['messenger']['level'] ?? 'warning';
            $serviceConfigurator
                ->set(MessengerListener::class)
                ->autowire()
                ->tag('kernel.event_subscriber')
                ->args([
                    service('logger'),
                    service($messageConfigServiceId),
                    $messengerLevel,
                ])
            ;
        }

        $serviceConfigurator
            ->set(SerializerInterface::class, $config['serializer'])
            ->set(GenerateZddMessageCommand::class)
            ->set(ValidateZddMessageCommand::class)
            ->set(ListZddMessageCommand::class)
        ;
    }

    private function getDefaultPath(ContainerBuilder $containerBuilder): string
    {
        $projectDir = $containerBuilder->getParameter('kernel.project_dir');

        if (!is_string($projectDir)) {
            throw new \InvalidArgumentException('The project directory should be a string.');
        }

        return $projectDir.'/var/zdd-message';
    }
}
