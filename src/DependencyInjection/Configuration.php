<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('webpack_encore');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = method_exists($treeBuilder, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('webpack_encore');

        $rootNode
            ->children()
                ->scalarNode('output_path')
                    ->isRequired()
                    ->info('The path where Encore is building the assets - i.e. Encore.setOutputPath()')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
