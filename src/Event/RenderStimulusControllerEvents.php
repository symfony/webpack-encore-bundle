<?php

namespace Symfony\WebpackEncoreBundle\Event;

final class RenderStimulusControllerEvents
{
    /**
     * @var RenderStimulusControllerEvent[]
     */
    private array $events = [];

    public function add(RenderStimulusControllerEvent $event): void
    {
        $this->events[] = $event;
    }

    public function getEvents(string $controllerName = null): array
    {
        if (null === $controllerName) {
            return $this->events;
        }

        $events = [];
        foreach ($this->events as $event) {
            if ($controllerName === $event->getControllerName()) {
                $events[] = $event;
            }
        }

        return $events;
    }
}
