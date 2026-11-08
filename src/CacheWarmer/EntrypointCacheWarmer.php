<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\CacheWarmer;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;

class EntrypointCacheWarmer extends BaseCacheWarmer
{
    private $cacheKeys;
    private $httpClient;

    public function __construct(array $cacheKeys, ?HttpClientInterface $httpClient, string $phpArrayFile)
    {
        $this->cacheKeys = $cacheKeys;
        $this->httpClient = $httpClient;
        parent::__construct($phpArrayFile);
    }

    protected function doWarmUp(string $cacheDir, ArrayAdapter $arrayAdapter, ?string $buildDir = null): bool
    {
        foreach ($this->cacheKeys as $cacheKey => $path) {
            // If the file does not exist then just skip past this entry point.
            if (!str_starts_with($path, 'http') && !file_exists($path)) {
                continue;
            }

            $entryPointLookup = new EntrypointLookup($path, $arrayAdapter, $cacheKey, httpClient: $this->httpClient);

            try {
                $entryPointLookup->getJavaScriptFiles('dummy');
            } catch (EntrypointNotFoundException $e) {
                // ignore exception
            }
        }

        return true;
    }
}
