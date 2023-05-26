<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\CacheWarmer;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\AbstractPhpFileCacheWarmer;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;

class EntrypointCacheWarmer extends AbstractPhpFileCacheWarmer
{
    private $cacheKeys;

    public function __construct(array $cacheKeys, string $phpArrayFile)
    {
        $this->cacheKeys = $cacheKeys;
        parent::__construct($phpArrayFile);
    }

    protected function doWarmUp($cacheDir, ArrayAdapter $arrayAdapter): bool
    {
        foreach ($this->cacheKeys as $cacheKey => $path) {
            // If the file does not exist then just skip past this entry point.
            if (!file_exists($path)) {
                continue;
            }

            $entryPointLookup = new EntrypointLookup($path, $arrayAdapter, $cacheKey);

            try {
                $entryPointLookup->getJavaScriptFiles('dummy');
            } catch (EntrypointNotFoundException $e) {
                // ignore exception
            }
        }

        return true;
    }
}
