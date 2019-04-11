<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\EventListener;

use Fig\Link\GenericLinkProvider;
use Fig\Link\Link;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
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

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (null === $linkProvider = $request->attributes->get('_links')) {
            $request->attributes->set('_links', new GenericLinkProvider());
        }

        /** @var GenericLinkProvider $linkProvider */
        $linkProvider = $request->attributes->get('_links');
        $defaultAttributes = $this->tagRenderer->getDefaultAttributes();
        $crossOrigin = $defaultAttributes['crossorigin'] ?? false;

        foreach ($this->tagRenderer->getRenderedScripts() as $href) {
            $link = (new Link('preload', $href))->withAttribute('as', 'script');

            if (false !== $crossOrigin) {
                $link = $link->withAttribute('crossorigin', $crossOrigin);
            }

            $linkProvider = $linkProvider->withLink($link);
        }

        foreach ($this->tagRenderer->getRenderedStyles() as $href) {
            $link = (new Link('preload', $href))->withAttribute('as', 'style');

            if (false !== $crossOrigin) {
                $link = $link->withAttribute('crossorigin', $crossOrigin);
            }

            $linkProvider = $linkProvider->withLink($link);
        }

        $request->attributes->set('_links', $linkProvider);
    }

    public static function getSubscribedEvents()
    {
        return [
            // must run before AddLinkHeaderListener
            'kernel.response' => ['onKernelResponse', 50],
        ];
    }
}
