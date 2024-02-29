<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Filesystem\ZddMessageFilesystem;
use Yousign\ZddMessageBundle\Listener\Symfony\MessengerListener;
use Yousign\ZddMessageBundle\Serializer\MessageSerializerInterface;
use Yousign\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer;

final class ZddMessageBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        /* @phpstan-ignore-next-line */
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
                ->load('Yousign\\ZddMessageBundle\\', __DIR__)
        ;

        $messageConfigServiceId = $config['message_config_service'];
        if (!$messageConfigServiceId) {
            throw new \LogicException(sprintf('You should configure zdd_message.message_config_service with a service that implements %s', ZddMessageConfigInterface::class));
        }

        $serviceConfigurator->bind('$config', service($messageConfigServiceId));

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
            ->set(ZddMessageFilesystem::class)
                ->arg('$path', $config['serialized_messages_dir'] ?? $this->getDefaultPath($builder))
            ->set(MessageSerializerInterface::class, $config['serializer'])
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
