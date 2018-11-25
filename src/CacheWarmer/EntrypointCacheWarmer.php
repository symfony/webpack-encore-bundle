<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\CacheWarmer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;

class EntrypointCacheWarmer implements CacheWarmerInterface
{
    private $builds;
    private $container;

    public function __construct(array $builds, ContainerInterface $container)
    {
        $this->builds = $builds;
        $this->container = $container;
    }

    public function isOptional()
    {
        return true;
    }

    public function warmUp($cacheDir)
    {
        $entryPointCollection = $this->container->get('webpack_encore.entrypoint_lookup_collection');

        if ($entryPointCollection instanceof EntrypointLookupCollection) {
            foreach ($this->builds as $build => $path) {
                $fullPath = $path.'/entrypoints.json';

                // If the file does not exist then just skip past this entry point.
                if (!file_exists($fullPath)) {
                    continue;
                }

                $entryPointLookup = $entryPointCollection->getEntrypointLookup($build);
                $entryPointLookup->warmUp($cacheDir);
            }
        }
    }
}
