<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;

class ResetAssetsEventListener implements EventSubscriberInterface
{
    private $entrypointLookupCollection;
    private $buildNames;

    public function __construct(EntrypointLookupCollection $entrypointLookupCollection, array $buildNames)
    {
        $this->entrypointLookupCollection = $entrypointLookupCollection;
        $this->buildNames = $buildNames;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::FINISH_REQUEST => 'resetAssets',
        ];
    }

    public function resetAssets(FinishRequestEvent $event)
    {
        // Handle deprecated `KernelEvent::isMasterRequest() - Can be removed when Symfony < 5.3 support is dropped.
        $mainRequestMethod = method_exists($event, 'isMainRequest') ? 'isMainRequest' : 'isMasterRequest';

        if (!$event->$mainRequestMethod()) {
            return;
        }
        foreach ($this->buildNames as $name) {
            $this->entrypointLookupCollection->getEntrypointLookup($name)->reset();
        }
    }
}
