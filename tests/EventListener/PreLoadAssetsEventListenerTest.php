<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Asset;

use Fig\Link\GenericLinkProvider;
use Fig\Link\Link;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use PHPUnit\Framework\TestCase;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\EventListener\PreLoadAssetsEventListener;

class PreLoadAssetsEventListenerTest extends TestCase
{
    public function testItPreloadsAssets()
    {
        $tagRenderer = $this->createMock(TagRenderer::class);
        $tagRenderer->expects($this->once())->method('getDefaultAttributes')->willReturn(['crossorigin' => 'anonymous']);
        $tagRenderer->expects($this->once())->method('getRenderedScripts')->willReturn(['/file1.js']);
        $tagRenderer->expects($this->once())->method('getRenderedStyles')->willReturn(['/css/file1.css']);

        $request = new Request();
        $response = new Response();
        $event = new FilterResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );
        $listener = new PreLoadAssetsEventListener($tagRenderer);
        $listener->onKernelResponse($event);
        $this->assertTrue($request->attributes->has('_links'));
        /** @var GenericLinkProvider $linkProvider */
        $linkProvider = $request->attributes->get('_links');
        $this->assertInstanceOf(GenericLinkProvider::class, $linkProvider);
        /** @var Link[] $links */
        $links = array_values($linkProvider->getLinks());
        $this->assertCount(2, $links);
        $this->assertSame('/file1.js', $links[0]->getHref());
        $this->assertSame(['preload'], $links[0]->getRels());
        $this->assertSame(['as' => 'script', 'crossorigin' => 'anonymous'], $links[0]->getAttributes());

        $this->assertSame('/css/file1.css', $links[1]->getHref());
        $this->assertSame(['preload'], $links[1]->getRels());
        $this->assertSame(['as' => 'style', 'crossorigin' => 'anonymous'], $links[1]->getAttributes());
    }

    public function testItReusesExistingLinkProvider()
    {
        $tagRenderer = $this->createMock(TagRenderer::class);
        $tagRenderer->expects($this->once())->method('getDefaultAttributes')->willReturn(['crossorigin' => 'anonymous']);
        $tagRenderer->expects($this->once())->method('getRenderedScripts')->willReturn(['/file1.js']);
        $tagRenderer->expects($this->once())->method('getRenderedStyles')->willReturn([]);

        $request = new Request();
        $linkProvider = new GenericLinkProvider([new Link('preload', 'bar.js')]);
        $request->attributes->set('_links', $linkProvider);

        $response = new Response();
        $event = new FilterResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );
        $listener = new PreLoadAssetsEventListener($tagRenderer);
        $listener->onKernelResponse($event);
        /** @var GenericLinkProvider $linkProvider */
        $linkProvider = $request->attributes->get('_links');
        $this->assertCount(2, $linkProvider->getLinks());
    }

    public function testItDoesNothingOnSubRequest()
    {
        $tagRenderer = $this->createMock(TagRenderer::class);
        $tagRenderer->expects($this->never())->method('getDefaultAttributes');
        $tagRenderer->expects($this->never())->method('getRenderedScripts');

        $request = new Request();
        $response = new Response();
        $event = new FilterResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::SUB_REQUEST,
            $response
        );
        $listener = new PreLoadAssetsEventListener($tagRenderer);
        $listener->onKernelResponse($event);
    }
}
