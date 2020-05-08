<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\WebLink\EventListener\AddLinkHeaderListener;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

final class WebpackEncoreExtension extends Extension
{
    private const ENTRYPOINTS_FILE_NAME = 'entrypoints.json';

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $factories = [];
        $cacheKeys = [];
        $buildPaths = [];

        if (false !== $config['output_path']) {
            $factories['_default'] = $this->entrypointFactory($container, '_default', $config['output_path'], $config['cache'], $config['strict_mode']);
            $cacheKeys['_default'] = $config['output_path'].'/'.self::ENTRYPOINTS_FILE_NAME;
            $buildPaths['_default'] = $config['output_path'];

            $container->getDefinition('webpack_encore.entrypoint_lookup_collection')
                ->setArgument(1, '_default');
        }

        foreach ($config['builds'] as $name => $path) {
            $factories[$name] = $this->entrypointFactory($container, $name, $path, $config['cache'], $config['strict_mode']);
            $cacheKeys[rawurlencode($name)] = $path.'/'.self::ENTRYPOINTS_FILE_NAME;
            $buildPaths[$name] = $path;
        }

        $container->getDefinition('webpack_encore.exception_listener')
            ->replaceArgument(1, array_keys($factories));

        $container->getDefinition('webpack_encore.entrypoint_lookup.cache_warmer')
            ->replaceArgument(0, $cacheKeys);

        $container->getDefinition('webpack_encore.entrypoint_lookup_collection')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $factories));
        if (false !== $config['output_path']) {
            $container->setAlias(EntrypointLookupInterface::class, new Alias($this->getEntrypointServiceId('_default')));
        }

        $container->getDefinition('webpack_encore.build_file_locator')
            ->replaceArgument(0, $buildPaths);

        $defaultAttributes = [];

        if (false !== $config['crossorigin']) {
            $defaultAttributes['crossorigin'] = $config['crossorigin'];
        }

        $container->getDefinition('webpack_encore.tag_renderer')
            ->replaceArgument(2, $defaultAttributes);

        if ($config['preload']) {
            if (!class_exists(AddLinkHeaderListener::class)) {
                throw new \LogicException('To use the "preload" option, the WebLink component must be installed. Try running "composer require symfony/web-link".');
            }
        } else {
            $container->removeDefinition('webpack_encore.preload_assets_event_listener');
        }
    }

    private function entrypointFactory(ContainerBuilder $container, string $name, string $path, bool $cacheEnabled, bool $strictMode): Reference
    {
        $id = $this->getEntrypointServiceId($name);
        $arguments = [
            $path.'/'.self::ENTRYPOINTS_FILE_NAME,
            $cacheEnabled ? new Reference('webpack_encore.cache') : null,
            $name,
            $strictMode,
        ];
        $definition = new Definition(EntrypointLookup::class, $arguments);
        $definition->addTag('kernel.reset', ['method' => 'reset']);
        $container->setDefinition($id, $definition);

        return new Reference($id);
    }

    private function getEntrypointServiceId(string $name): string
    {
        return sprintf('webpack_encore.entrypoint_lookup[%s]', $name);
    }
}
