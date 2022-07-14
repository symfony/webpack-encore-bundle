<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\WebpackEncoreBundle\Event\RenderStimulusControllerEvent;
use Symfony\WebpackEncoreBundle\Event\RenderStimulusControllerEvents;

class RenderStimulusControllerListener implements EventSubscriberInterface, ResetInterface
{
    private $events;

    public function __construct()
    {
        $this->events = new RenderStimulusControllerEvents();
    }

    public function reset()
    {
        $this->events = new RenderStimulusControllerEvents();
    }

    public function onRenderController(RenderStimulusControllerEvent $event): void
    {
        $this->events->add($event);
    }

    public function getEvents(): RenderStimulusControllerEvents
    {
        return $this->events;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RenderStimulusControllerEvent::class => ['onRenderController', -255],
        ];
    }
}
