<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use PHPUnit\Framework\TestCase;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\EventListener\ExceptionListener;

class ExceptionListenerTest extends TestCase
{
    public function testItResetsAllEntrypointLookups()
    {
        /** @var EntrypointLookupInterface[]|Prophecy\Prophecy\ObjectProphecy[] $entrypointLookups */
        $entrypointLookups = [];
        $entrypointLookupsValueMap = [];

        $buildNames = ['_default', '_test'];
        foreach ($buildNames as $buildName) {
            $entrypointLookups[$buildName] = $this->createMock(EntrypointLookupInterface::class);
            $entrypointLookups[$buildName]->expects($this->once())->method('reset');

            $entrypointLookupsValueMap[] = [$buildName, $entrypointLookups[$buildName]];
        }

        $entrypointLookupCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointLookupCollection->method('getEntrypointLookup')
            ->willReturnMap($entrypointLookupsValueMap);

        $request = new Request();
        $exception = new \Exception();
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );
        $listener = new ExceptionListener($entrypointLookupCollection, $buildNames);
        $listener->onKernelException($event);
    }
}
