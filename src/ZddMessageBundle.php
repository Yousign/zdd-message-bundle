<?php

namespace Yousign\ZddMessageBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface as MessengerSerializerInterface;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\DependencyInjection\ZddMessageCompilerPass;
use Yousign\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer;

final class ZddMessageBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        /* @phpstan-ignore-next-line */
        $definition
            ->rootNode()
                ->children()
                    ->scalarNode('serialized_messages_dir')->defaultNull()->end()
                    ->scalarNode('serializer')->defaultValue('Yousign\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer')->end()
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
    public function loadExtension(array $config, ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setDefinition(
            ZddMessageMessengerSerializer::class,
            new Definition(
                ZddMessageMessengerSerializer::class,
                [
                    new Reference(MessengerSerializerInterface::class),
                ]
            )
        );

        $containerBuilder->setAlias(
            'yousign.zdd.message.serializer',
            new Alias($config['serializer'] ?? ZddMessageMessengerSerializer::class)
        );

        $containerBuilder->registerForAutoconfiguration(ZddMessageConfigInterface::class)->addTag('yousign.zdd.message.config');

        $containerBuilder->setParameter('yousign.zdd.message.serialized_messages_dir', $config['serialized_messages_dir'] ?? $this->getDefaultPath($containerBuilder));
        $containerBuilder->setParameter('yousign.zdd.message.log_untracked_messages.messenger.enable', $config['log_untracked_messages']['messenger']['enable'] ?? false);
        $containerBuilder->setParameter('yousign.zdd.message.log_untracked_messages.messenger.level', $config['log_untracked_messages']['messenger']['level'] ?? 'warning');
    }

    private function getDefaultPath(ContainerBuilder $containerBuilder): string
    {
        $projectDir = $containerBuilder->getParameter('kernel.project_dir');

        if (!is_string($projectDir)) {
            throw new \InvalidArgumentException('The project directory should be a string.');
        }

        return $projectDir.'/var/zdd-message';
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ZddMessageCompilerPass());
    }
}
