<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Asset;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

class EntrypointLookupCollectionTest extends TestCase
{
    public function testExceptionOnMissingEntry()
    {
        $this->expectException(\Symfony\WebpackEncoreBundle\Exception\UndefinedBuildException::class);
        $this->expectExceptionMessage('The build "something" is not configured');

        $collection = new EntrypointLookupCollection(new ServiceLocator([]));
        $collection->getEntrypointLookup('something');
    }

    public function testExceptionOnMissingDefaultBuildEntry()
    {
        $this->expectException(\Symfony\WebpackEncoreBundle\Exception\UndefinedBuildException::class);
        $this->expectExceptionMessage('There is no default build configured: please pass an argument to getEntrypointLookup().');

        $collection = new EntrypointLookupCollection(new ServiceLocator([]));
        $collection->getEntrypointLookup();
    }

    public function testDefaultBuildIsReturned()
    {
        $lookup = $this->createMock(EntrypointLookupInterface::class);
        $collection = new EntrypointLookupCollection(new ServiceLocator(['the_default' => function () use ($lookup) { return $lookup; }]), 'the_default');

        $this->assertSame($lookup, $collection->getEntrypointLookup());
    }
}
