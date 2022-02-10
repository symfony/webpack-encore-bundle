<?php

declare(strict_types=1);

namespace Symfony\WebpackEncoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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

    public function resetAssets()
    {
        foreach ($this->buildNames as $name) {
            $this->entrypointLookupCollection->getEntrypointLookup($name)->reset();
        }
    }
}
