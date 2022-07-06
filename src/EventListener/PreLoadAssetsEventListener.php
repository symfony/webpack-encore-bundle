<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\EventListener;

use Fig\Link\GenericLinkProvider as FigGenericLinkProvider;
use Fig\Link\Link as FigLink;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

/**
 * @author Ryan Weaver <ryan@symfonycasts.com>
 */
class PreLoadAssetsEventListener implements EventSubscriberInterface
{
    private $tagRenderer;

    public function __construct(TagRenderer $tagRenderer)
    {
        $this->tagRenderer = $tagRenderer;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        // Handle deprecated `KernelEvent::isMasterRequest() - Can be removed when Symfony < 5.3 support is dropped.
        $mainRequestMethod = method_exists($event, 'isMainRequest') ? 'isMainRequest' : 'isMasterRequest';

        if (!$event->$mainRequestMethod()) {
            return;
        }

        $request = $event->getRequest();

        if (null === $linkProvider = $request->attributes->get('_links')) {
            $request->attributes->set(
                '_links',
                // For backwards-compat with symfony/web-link 4.3 and lower
                class_exists(GenericLinkProvider::class) ? new GenericLinkProvider() : new FigGenericLinkProvider()
            );
        }

        /** @var GenericLinkProvider|FigGenericLinkProvider $linkProvider */
        $linkProvider = $request->attributes->get('_links');
        $defaultAttributes = $this->tagRenderer->getDefaultAttributes();
        $crossOrigin = $defaultAttributes['crossorigin'] ?? false;

        foreach ($this->tagRenderer->getRenderedScriptsWithAttributes() as $attributes) {
            $attributes = array_merge($defaultAttributes, $attributes);

            $link = ($this->createLink('preload', $attributes['src']))->withAttribute('as', 'script');

            if (!empty($attributes['crossorigin']) && false !== $attributes['crossorigin']) {
                $link = $link->withAttribute('crossorigin', $attributes['crossorigin']);
            }
            if (!empty($attributes['integrity'])) {
                $link = $link->withAttribute('integrity', $attributes['integrity']);
            }

            $linkProvider = $linkProvider->withLink($link);
        }

        foreach ($this->tagRenderer->getRenderedStylesWithAttributes() as $attributes) {
            $attributes = array_merge($defaultAttributes, $attributes);

            $link = ($this->createLink('preload', $attributes['href']))->withAttribute('as', 'style');

            if (!empty($attributes['crossorigin']) && false !== $attributes['crossorigin']) {
                $link = $link->withAttribute('crossorigin', $attributes['crossorigin']);
            }
            if (!empty($attributes['integrity'])) {
                $link = $link->withAttribute('integrity', $attributes['integrity']);
            }

            $linkProvider = $linkProvider->withLink($link);
        }

        $request->attributes->set('_links', $linkProvider);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must run before AddLinkHeaderListener
            'kernel.response' => ['onKernelResponse', 50],
        ];
    }

    /**
     * For backwards-compat with symfony/web-link 4.3 and lower.
     *
     * @return Link|FigLink
     */
    private function createLink(string $rel, string $href)
    {
        $class = class_exists(Link::class) ? Link::class : FigLink::class;

        return new $class($rel, $href);
    }
}
