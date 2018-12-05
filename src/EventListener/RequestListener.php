<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;

class RequestListener
{
    private $entrypointLookup;

    public function __construct(EntrypointLookup $entrypointLookup)
    {
        $this->entrypointLookup = $entrypointLookup;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // reset the entrypointLookup list of previously returned entries for subrequests sub-request
            $this->entrypointLookup->reset();
        }
    }
}
