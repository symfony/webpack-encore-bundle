<?php

namespace Symfony\WebpackEncoreBundle\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\WebpackEncoreBundle\Event\RenderStimulusControllerEvents;
use Symfony\WebpackEncoreBundle\EventListener\RenderStimulusControllerListener;

class StimulusCollector extends AbstractDataCollector
{
    private RenderStimulusControllerEvents $events;

    public function __construct(RenderStimulusControllerListener $logger)
    {
        $this->events = $logger->getEvents();
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $this->data['events'] = $this->events;
    }

    public static function getTemplate(): ?string
    {
        return '@Symfony/WebpackEncoreBundle/Resources/collector/ux.html.twig';
    }

    public function getName(): string
    {
        return 'ux';
    }
}
