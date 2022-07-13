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
     * @param string $controllerName   the Stimulus controller name
     * @param array  $controllerValues array of data if a string is passed to the 1st argument
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function renderStimulusController(Environment $env, $controllerName, array $controllerValues = []): StimulusControllersDto
    {
        if (!\is_string($dataOrControllerName)) {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_controller() is deprecated.');
        }

        $dto = new StimulusControllersDto($env);
        $dto->addController($controllerName, $controllerValues);

        return $dto;
    }

    /**
     * @param string $controllerName   the Stimulus controller name
     * @param array  $controllerValues array of data if a string is passed to the 1st argument
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function appendStimulusController(StimulusControllersDto $dto, $controllerName, array $controllerValues = []): StimulusControllersDto
    {
        if (!\is_string($controllerName)) {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_controller() is deprecated.');
        }

        $dto->addController($controllerName, $controllerValues);

        return $dto;
    }

    /**
     * @param string      $controllerName the Stimulus controller name
     * @param string      $actionName     the action to trigger
     * @param string|null $eventName      The event to listen to trigger. Optional.
     * @param array       $parameters     Parameters to pass to the action. Optional.
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function renderStimulusAction(Environment $env, $controllerName, string $actionName = null, string $eventName = null, array $parameters = []): StimulusActionsDto
    {
        if (!\is_string($dataOrControllerName)) {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_action() is deprecated.');
        }

        $dto = new StimulusActionsDto($env);
        $dto->addAction($controllerName, $actionName, $eventName, $parameters);

        return $dto;
    }

    /**
     * @param string      $controllerName the Stimulus controller name
     * @param string      $actionName     the action to trigger
     * @param string|null $eventName      The event to listen to trigger. Optional.
     * @param array       $parameters     Parameters to pass to the action. Optional.
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function appendStimulusAction(StimulusActionsDto $dto, $controllerName, string $actionName = null, string $eventName = null, array $parameters = []): StimulusActionsDto
    {
        if (!\is_string($controllerName)) {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_action() is deprecated.');
        }

        $dto->addAction($controllerName, $actionName, $eventName, $parameters);

        return $dto;
    }

    /**
     * @param string      $controllerName the Stimulus controller name
     * @param string|null $targetNames    The space-separated list of target names if a string is passed to the 1st argument. Optional.
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function renderStimulusTarget(Environment $env, $controllerName, string $targetNames = null): StimulusTargetsDto
    {
        if (!\is_string($dataOrControllerName)) {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_target() is deprecated.');
        }

        $dto = new StimulusTargetsDto($env);
        $dto->addTarget($controllerName, $targetNames);

        return $dto;
    }

    /**
     * @param string      $controllerName the Stimulus controller name
     * @param string|null $targetNames    The space-separated list of target names if a string is passed to the 1st argument. Optional.
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function appendStimulusTarget(StimulusTargetsDto $dto, $controllerName, string $targetNames = null): StimulusTargetsDto
    {
        if (!\is_string($controllerName)) {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_target() is deprecated.');
        }

        $dto->addTarget($controllerName, $targetNames);

        return $dto;
    }
}
