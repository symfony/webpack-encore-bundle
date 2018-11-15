<?php

namespace Symfony\WebpackEncoreBundle\Tests\Asset;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use PHPUnit\Framework\TestCase;

class EntrypointLookupCollectionTest extends TestCase
{
    /**
     * @expectedException Symfony\WebpackEncoreBundle\Exception\UndefinedBuildException
     * @expectedExceptionMessage Given entry point "something" is not configured
     */
    public function testExceptionOnMissingEntry()
    {
        $collection = new EntrypointLookupCollection(new ServiceLocator([]));
        $collection->getEntrypointLookup('something');
    }
}
