<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Twig_Error_Runtime;

class ExceptionListener
{
    private $entrypointLookup;

    public function __construct(EntrypointLookup $entrypointLookup)
    {
        $this->entrypointLookup = $entrypointLookup;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        // Reset the entrypointLookup list of previously returned entries, as Twig_Error_Runtime will initialise a sub-request
        if ($exception instanceof Twig_Error_Runtime) {
            $this->entrypointLookup->reset();
        }
    }
}
