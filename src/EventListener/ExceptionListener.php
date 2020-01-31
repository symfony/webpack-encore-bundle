<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\EventListener;

use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;

class ExceptionListener
{
    private $entrypointLookupCollection;

    private $buildNames;

    public function __construct(EntrypointLookupCollection $entrypointLookupCollection, array $buildNames)
    {
        $this->entrypointLookupCollection = $entrypointLookupCollection;
        $this->buildNames = $buildNames;
    }

    public function onKernelException()
    {
        foreach ($this->buildNames as $buildName) {
            $this->entrypointLookupCollection->getEntrypointLookup($buildName)->reset();
        }
    }
}
