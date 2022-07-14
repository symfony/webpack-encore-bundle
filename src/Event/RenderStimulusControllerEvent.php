<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        return self::TYPE_CONTROLLER === $this->type;
    }

    public function isAction()
    {
        return self::TYPE_ACTION === $this->type;
    }

    public function isTarget()
    {
        return self::TYPE_TARGET === $this->type;
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
