<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\WebpackEncoreBundle\Event\RenderStimulusControllerEvents;
use Symfony\WebpackEncoreBundle\EventListener\RenderStimulusControllerListener;

class StimulusCollector extends AbstractDataCollector
{
    /**
     * @var RenderStimulusControllerEvents
     */
    private $events;

    public function __construct(RenderStimulusControllerListener $logger)
    {
        $this->events = $logger->getEvents();
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $this->data['events'] = $this->events;
    }

    public function getName(): string
    {
        return 'ux';
    }
}
