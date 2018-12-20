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
class EntrypointLookupCollection
{
    private $buildEntrypoints;

    public function __construct(ContainerInterface $buildEntrypoints)
    {
        $this->buildEntrypoints = $buildEntrypoints;
    }

    public function getEntrypointLookup(string $buildName): EntrypointLookupInterface
    {
        if (!$this->buildEntrypoints->has($buildName)) {
            throw new UndefinedBuildException(sprintf('Given entry point "%s" is not configured', $buildName));
        }

        return $this->buildEntrypoints->get($buildName);
    }
}
