<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\CacheWarmer\EntrypointCacheWarmer;
use Symfony\WebpackEncoreBundle\EventListener\ExceptionListener;
use Symfony\WebpackEncoreBundle\EventListener\PreLoadAssetsEventListener;
use Symfony\WebpackEncoreBundle\EventListener\ResetAssetsEventListener;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->set('webpack_encore.entrypoint_lookup_collection', EntrypointLookupCollection::class)
            ->args([
                abstract_arg('build list of entrypoints locator'),
            ])

        ->alias(EntrypointLookupCollectionInterface::class, 'webpack_encore.entrypoint_lookup_collection')

        ->set('webpack_encore.tag_renderer', TagRenderer::class)
            ->tag('kernel.reset', ['method' => 'reset'])
            ->args([
                service('webpack_encore.entrypoint_lookup_collection'),
                service('assets.packages'),
                [], // Default attributes
                [], // Default script attributes
                [], // Default link attributes
                service('event_dispatcher'),
            ])

        ->set('webpack_encore.twig_entry_files_extension', EntryFilesTwigExtension::class)
            ->tag('twig.extension')
            ->args([
                inline_service(ServiceLocator::class)
                    ->tag('container.service_locator')
                    ->args([
                        [
                            'webpack_encore.entrypoint_lookup_collection' => service('webpack_encore.entrypoint_lookup_collection'),
                            'webpack_encore.tag_renderer' => service('webpack_encore.tag_renderer'),
                        ],
                    ]),
            ])

        ->set('webpack_encore.entrypoint_lookup.cache_warmer', EntrypointCacheWarmer::class)
            ->tag('kernel.cache_warmer')
            ->args([
                abstract_arg(' build list of entrypoint paths'),
                service('http_client')->nullOnInvalid(),
                '%kernel.build_dir%/webpack_encore.cache.php',
            ])

        ->set('webpack_encore.cache', PhpArrayAdapter::class)
            ->factory([PhpArrayAdapter::class, 'create'])
            ->args([
                '%kernel.build_dir%/webpack_encore.cache.php',
                service('cache.webpack_encore'),
            ])

        ->set('cache.webpack_encore')
            ->parent('cache.system')
            ->tag('cache.pool')

        ->set('webpack_encore.exception_listener', ExceptionListener::class)
            ->tag('kernel.event_listener', ['event' => 'kernel.exception'])
            ->args([
                service('webpack_encore.entrypoint_lookup_collection'),
                abstract_arg('build list of build names'),
            ])

        ->set('webpack_encore.preload_assets_event_listener', PreLoadAssetsEventListener::class)
            ->tag('kernel.event_subscriber')
            ->args([
                service('webpack_encore.tag_renderer'),
            ])

        ->set(ResetAssetsEventListener::class)
            ->tag('kernel.event_subscriber')
            ->args([
                service('webpack_encore.entrypoint_lookup_collection'),
            ])
    ;
};
