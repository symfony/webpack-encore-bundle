<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Twig;

use Symfony\WebpackEncoreBundle\Dto\StimulusActionsDto;
use Symfony\WebpackEncoreBundle\Dto\StimulusControllersDto;
use Symfony\WebpackEncoreBundle\Dto\StimulusTargetsDto;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @deprecated since 1.17.0 - install symfony/stimulus-bundle instead.
 */
final class StimulusTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('stimulus_controller', [$this, 'renderStimulusController'], ['needs_environment' => true, 'is_safe' => ['html_attr']]),
            new TwigFunction('stimulus_action', [$this, 'renderStimulusAction'], ['needs_environment' => true, 'is_safe' => ['html_attr']]),
            new TwigFunction('stimulus_target', [$this, 'renderStimulusTarget'], ['needs_environment' => true, 'is_safe' => ['html_attr']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('stimulus_controller', [$this, 'appendStimulusController'], ['is_safe' => ['html_attr']]),
            new TwigFilter('stimulus_action', [$this, 'appendStimulusAction'], ['is_safe' => ['html_attr']]),
            new TwigFilter('stimulus_target', [$this, 'appendStimulusTarget'], ['is_safe' => ['html_attr']]),
        ];
    }

    /**
     * @param string $controllerName    the Stimulus controller name
     * @param array  $controllerValues  array of controller values
     * @param array  $controllerClasses array of controller CSS classes
     */
    public function renderStimulusController(Environment $env, $controllerName, array $controllerValues = [], array $controllerClasses = []): StimulusControllersDto
    {
        $dto = new StimulusControllersDto($env);

        if (\is_array($controllerName)) {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_controller() is deprecated.');

            if ($controllerValues || $controllerClasses) {
                throw new \InvalidArgumentException('You cannot pass an array to the first and second/third argument of stimulus_controller(): check the documentation.');
            }

            $data = $controllerName;

            foreach ($data as $controllerName => $controllerValues) {
                $dto->addController($controllerName, $controllerValues);
            }

            return $dto;
        }

        $dto->addController($controllerName, $controllerValues, $controllerClasses);

        return $dto;
    }

    /**
     * @param array $parameters Parameters to pass to the action. Optional.
     */
    public function renderStimulusAction(Environment $env, $controllerName, string $actionName = null, string $eventName = null, array $parameters = []): StimulusActionsDto
    {
        $dto = new StimulusActionsDto($env);
        if (\is_array($controllerName)) {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_action() is deprecated.');

            if ($actionName || $eventName || $parameters) {
                throw new \InvalidArgumentException('You cannot pass a string to the second or third argument nor an array to the fourth argument while passing an array to the first argument of stimulus_action(): check the documentation.');
            }

            $data = $controllerName;

            foreach ($data as $controllerName => $controllerActions) {
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
                        $dto->addAction($controllerName, $actionName, \is_string($eventName) ? $eventName : null);
                    }
                }
            }

            return $dto;
        }

        $dto->addAction($controllerName, $actionName, $eventName, $parameters);

        return $dto;
    }

    public function appendStimulusController(StimulusControllersDto $dto, string $controllerName, array $controllerValues = [], array $controllerClasses = []): StimulusControllersDto
    {
        $dto->addController($controllerName, $controllerValues, $controllerClasses);

        return $dto;
    }

    /**
     * @param array $parameters Parameters to pass to the action. Optional.
     */
    public function appendStimulusAction(StimulusActionsDto $dto, string $controllerName, string $actionName, string $eventName = null, array $parameters = []): StimulusActionsDto
    {
        $dto->addAction($controllerName, $actionName, $eventName, $parameters);

        return $dto;
    }

    /**
     * @param string      $controllerName the Stimulus controller name
     * @param string|null $targetNames    The space-separated list of target names if a string is passed to the 1st argument. Optional.
     */
    public function renderStimulusTarget(Environment $env, $controllerName, string $targetNames = null): StimulusTargetsDto
    {
        $dto = new StimulusTargetsDto($env);
        if (\is_array($controllerName)) {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_target() is deprecated.');

            if ($targetNames) {
                throw new \InvalidArgumentException('You cannot pass a string to the second argument while passing an array to the first argument of stimulus_target(): check the documentation.');
            }

            $data = $controllerName;

            foreach ($data as $controllerName => $targetNames) {
                $dto->addTarget($controllerName, $targetNames);
            }

            return $dto;
        }

        $dto->addTarget($controllerName, $targetNames);

        return $dto;
    }

    /**
     * @param string      $controllerName the Stimulus controller name
     * @param string|null $targetNames    The space-separated list of target names if a string is passed to the 1st argument. Optional.
     */
    public function appendStimulusTarget(StimulusTargetsDto $dto, string $controllerName, string $targetNames = null): StimulusTargetsDto
    {
        $dto->addTarget($controllerName, $targetNames);

        return $dto;
    }
}
