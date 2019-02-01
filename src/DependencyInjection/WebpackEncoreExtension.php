<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;

final class WebpackEncoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $factories = [
            '_default' => new Reference($this->entrypointFactory($container, '_default', $config['output_path']))
        ];
        foreach ($config['builds'] as $name => $path) {
            $factories[$name] = new Reference($this->entrypointFactory($container, $name, $path));
        };

        $container->getDefinition('webpack_encore.entrypoint_lookup')
            ->replaceArgument(0, $config['output_path'].'/entrypoints.json');
        $container->getDefinition('webpack_encore.entrypoint_lookup_collection')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $factories));
    }

    private function entrypointFactory(ContainerBuilder $container, string $name, string $path): string
    {
        $id = sprintf('webpack_encore.entrypoint_lookup[%s]', $name);
        $container->setDefinition($id, new Definition(EntrypointLookup::class, [$path.'/entrypoints.json']));
        return $id;
    }
}
