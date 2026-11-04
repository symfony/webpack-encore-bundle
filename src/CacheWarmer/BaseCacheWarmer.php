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

use Symfony\Bundle\FrameworkBundle\CacheWarmer\AbstractPhpFileCacheWarmer as LegacyAbstractPhpFileCacheWarmer;
use Symfony\Component\Cache\CacheWarmer\AbstractPhpFileCacheWarmer;

// The base cache warmer moved from FrameworkBundle to the Cache component in Symfony 8.2
if (class_exists(AbstractPhpFileCacheWarmer::class)) {
    /**
     * @internal
     */
    abstract class BaseCacheWarmer extends AbstractPhpFileCacheWarmer
    {
    }
} else {
    /**
     * @internal
     */
    abstract class BaseCacheWarmer extends LegacyAbstractPhpFileCacheWarmer
    {
    }
}
