<?php

namespace Symfony\WebpackEncoreBundle\Event;

final class RenderStimulusControllerEvent
{
    public const TYPE_CONTROLLER = 'controller';
    public const TYPE_ACTION = 'action';
    public const TYPE_TARGET = 'target';

    private $type;
    private $controllerName;
    private $values;

    public function __construct(string $type, string $controllerName, $values)
    {
        $this->type = $type;
        $this->controllerName = $controllerName;
        $this->values = $values;
    }

    public function isController()
    {
        return $this->type === self::TYPE_CONTROLLER;
    }

    public function isAction()
    {
        return $this->type === self::TYPE_ACTION;
    }

    public function isTarget()
    {
        return $this->type === self::TYPE_TARGET;
    }

    public function getControllerName()
    {
        return $this->controllerName;
    }

    public function getValues()
    {
        return $this->values;
    }
}
