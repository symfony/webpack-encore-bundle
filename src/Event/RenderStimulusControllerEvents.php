<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Event;

final class RenderStimulusControllerEvents
{
    /**
     * @var RenderStimulusControllerEvent[]
     */
    private $events = [];

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
