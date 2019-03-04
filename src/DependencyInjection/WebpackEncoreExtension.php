<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;

final class WebpackEncoreExtension extends Extension
{
    private const ENTRYPOINTS_FILE_NAME = 'entrypoints.json';

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $factories = [
            '_default' => $this->entrypointFactory($container, '_default', $config['output_path'], $config['cache']),
        ];
        $cacheKeys = [
            '_default' => $config['output_path'].'/'.self::ENTRYPOINTS_FILE_NAME,
        ];
        foreach ($config['builds'] as $name => $path) {
            $factories[$name] = $this->entrypointFactory($container, $name, $path, $config['cache']);
            $cacheKeys[rawurlencode($name)] = $path.'/'.self::ENTRYPOINTS_FILE_NAME;
        }

        $container->getDefinition('webpack_encore.entrypoint_lookup.cache_warmer')
            ->replaceArgument(0, $cacheKeys);

        $container->getDefinition('webpack_encore.entrypoint_lookup_collection')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $factories));
    }

    private function entrypointFactory(ContainerBuilder $container, string $name, string $path, bool $cacheEnabled): Reference
    {
        $id = sprintf('webpack_encore.entrypoint_lookup[%s]', $name);
        $arguments = [$path.'/'.self::ENTRYPOINTS_FILE_NAME, $cacheEnabled ? new Reference('webpack_encore.cache') : null, $name];
        $container->setDefinition($id, new Definition(EntrypointLookup::class, $arguments));

        return new Reference($id);
    }
}
