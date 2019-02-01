<?php

namespace Symfony\WebpackEncoreBundle\Tests\DependencyInjection;


use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;

class DummyService
{
    protected $entryPointLookup;

    public function __construct(EntrypointLookup $entrypointLookup)
    {
        $this->entryPointLookup = $entrypointLookup;
    }

    public function getEntryPointLookup(): EntrypointLookup
    {
        return $this->entryPointLookup;
    }
}
