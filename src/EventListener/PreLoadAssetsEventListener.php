<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\EventListener;

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
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (null === $linkProvider = $request->attributes->get('_links')) {
            $request->attributes->set(
                '_links',
                new GenericLinkProvider()
            );
        }

        /** @var GenericLinkProvider $linkProvider */
        $linkProvider = $request->attributes->get('_links');
        $defaultAttributes = $this->tagRenderer->getDefaultAttributes();

        foreach ($this->tagRenderer->getRenderedScripts(true) as $attributes) {
            $src = $attributes['src'];
            unset($attributes['src']);
            $attributes = [...$defaultAttributes, ...$attributes];

            $link = $this->createLink('preload', $src)
                ->withAttribute('as', 'script');

            foreach ($attributes as $k => $v) {
                $link = $link->withAttribute($k, $v);
            }

            $linkProvider = $linkProvider->withLink($link);
        }

        foreach ($this->tagRenderer->getRenderedStyles(true) as $attributes) {
            $href = $attributes['href'];
            unset($attributes['href']);
            $attributes = [...$defaultAttributes, ...$attributes];

            $link = $this->createLink('preload', $href)->withAttribute('as', 'style');

            foreach ($attributes as $k => $v) {
                $link = $link->withAttribute($k, $v);
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

    private function createLink(string $rel, string $href): Link
    {
        return new Link($rel, $href);
    }
}
