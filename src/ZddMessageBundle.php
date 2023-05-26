<?php

namespace Yousign\ZddMessageBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\DependencyInjection\ZddMessageCompilerPass;

final class ZddMessageBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        /* @phpstan-ignore-next-line */
        $definition
            ->rootNode()
                ->children()
                    ->scalarNode('serialized_messages_dir')->defaultNull()->end()
                ->end()
            ->end()
        ;
    }

    /** @phpstan-ignore-next-line */
    public function loadExtension(array $config, ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->registerForAutoconfiguration(ZddMessageConfigInterface::class)->addTag('yousign.zdd.message.config');

        $containerBuilder->setParameter('yousign.zdd.message.serialized_messages_dir', $config['serialized_messages_dir'] ?? $this->getDefaultPath($containerBuilder));
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
