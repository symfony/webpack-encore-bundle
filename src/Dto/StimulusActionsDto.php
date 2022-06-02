<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Dto;

final class StimulusActionsDto extends AbstractStimulusDto
{
    private $actions = [];
    private $parameters = [];

    /**
     * @param string|array $dataOrControllerName This can either be a map of controller names
     *                                           as keys set to their "actions" and "events".
     *                                           Or this can be a string controller name and
     *                                           action and event are passed as the 2nd and 3rd arguments.
     * @param string|null  $actionName           The action to trigger if a string is passed to the 1st argument. Optional.
     * @param string|null  $eventName            The event to listen to trigger if a string is passed to the 1st argument. Optional.
     * @param array        $parameters           Parameters to pass to the action if a string is passed to the 1st argument. Optional.
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function addAction($dataOrControllerName, string $actionName = null, string $eventName = null, array $parameters = []): void
    {
        if (\is_string($dataOrControllerName)) {
            $data = [$dataOrControllerName => null === $eventName ? [[$actionName]] : [[$eventName => $actionName]]];
        } else {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_action() is deprecated.');
            if ($actionName || $eventName || $parameters) {
                throw new \InvalidArgumentException('You cannot pass a string to the second or third argument nor an array to the fourth argument while passing an array to the first argument of stimulus_action(): check the documentation.');
            }

            $data = $dataOrControllerName;

            if (!$data) {
                return;
            }
        }

        foreach ($data as $controllerName => $controllerActions) {
            $controllerName = $this->getFormattedControllerName($controllerName);

            if (\is_string($controllerActions)) {
                $controllerActions = [[$controllerActions]];
            }

            foreach ($controllerActions as $possibleEventName => $controllerAction) {
                if (\is_string($possibleEventName) && \is_string($controllerAction)) {
                    $controllerAction = [$possibleEventName => $controllerAction];
                } elseif (\is_string($controllerAction)) {
                    $controllerAction = [$controllerAction];
                }

                foreach ($controllerAction as $eventName => $actionName) {
                    $action = $controllerName.'#'.$this->escapeAsHtmlAttr($actionName);

                    if (\is_string($eventName)) {
                        $action = $eventName.'->'.$action;
                    }

                    $this->actions[] = $action;
                }
            }

            foreach ($parameters as $name => $value) {
                $this->parameters['data-'.$controllerName.'-'.$name.'-param'] = $this->getFormattedValue($value);
            }
        }
    }

    public function __toString(): string
    {
        if (0 === \count($this->actions)) {
            return '';
        }

        return rtrim('data-action="'.implode(' ', $this->actions).'" '.implode(' ', array_map(static function (string $attribute, string $value): string {
            return $attribute.'="'.$value.'"';
        }, array_keys($this->parameters), $this->parameters)));
    }

    public function toArray(): array
    {
        return ['data-action' => implode(' ', $this->actions)] + $this->parameters;
    }
}
