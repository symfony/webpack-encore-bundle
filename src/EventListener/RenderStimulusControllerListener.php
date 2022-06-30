<?php

namespace Symfony\WebpackEncoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\WebpackEncoreBundle\Event\RenderStimulusControllerEvent;
use Symfony\WebpackEncoreBundle\Event\RenderStimulusControllerEvents;

class RenderStimulusControllerListener implements EventSubscriberInterface, ResetInterface
{
    private RenderStimulusControllerEvents $events;

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

    public static function getSubscribedEvents()
    {
        return [
            RenderStimulusControllerEvent::class => ['onRenderController', -255]
        ];
    }
}
