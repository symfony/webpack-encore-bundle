<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;

use Symfony\WebpackEncoreBundle\Exception\UndefinedBuildException;

interface EntrypointLookupCollectionInterface
{
    /**
     * Retrieve the EntrypointLookupInterface for the given build.
     *
     * @throws UndefinedBuildException if the build does not exist
     */
    public function getEntrypointLookup(string $buildName = null): EntrypointLookupInterface;
}
