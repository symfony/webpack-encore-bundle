<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;

use Symfony\WebpackEncoreBundle\Exception\UndefinedBuildException;
use Psr\Container\ContainerInterface;

/**
 * Aggregate the different entry points configured in the container.
 *
 * Retrieve the EntrypointLookup instance from the given key.
 *
 * @final
 */
class EntrypointLookupCollection implements EntrypointLookupCollectionInterface
{
    private $buildEntrypoints;

    private $defaultBuildName;

    public function __construct(ContainerInterface $buildEntrypoints, string $defaultBuildName = null)
    {
        $this->buildEntrypoints = $buildEntrypoints;
        $this->defaultBuildName = $defaultBuildName;
    }

    public function getEntrypointLookup(string $buildName = null): EntrypointLookupInterface
    {
        if (null === $buildName) {
            if (null === $this->defaultBuildName) {
                throw new UndefinedBuildException('There is no default build configured: please pass an argument to getEntrypointLookup().');
            }

            $buildName = $this->defaultBuildName;
        }

        if (!$this->buildEntrypoints->has($buildName)) {
            throw new UndefinedBuildException(sprintf('The build "%s" is not configured', $buildName));
        }

        return $this->buildEntrypoints->get($buildName);
    }
}
