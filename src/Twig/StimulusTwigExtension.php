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
     * @param string|array $dataOrControllerName This can either be a map of controller names
     *                                           as keys set to their "values". Or this
     *                                           can be a string controller name and data
     *                                           is passed as the 2nd argument.
     * @param array        $controllerValues     array of data if a string is passed to the 1st argument
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function renderStimulusController(Environment $env, $dataOrControllerName, array $controllerValues = []): StimulusControllersDto
    {
        $dto = new StimulusControllersDto($env);
        $dto->addController($dataOrControllerName, $controllerValues);

        return $dto;
    }

    /**
     * @param string|array $dataOrControllerName This can either be a map of controller names
     *                                           as keys set to their "values". Or this
     *                                           can be a string controller name and data
     *                                           is passed as the 2nd argument.
     * @param array        $controllerValues     array of data if a string is passed to the 1st argument
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function appendStimulusController(StimulusControllersDto $dto, $dataOrControllerName, array $controllerValues = []): StimulusControllersDto
    {
        $dto->addController($dataOrControllerName, $controllerValues);

        return $dto;
    }

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
    public function renderStimulusAction(Environment $env, $dataOrControllerName, string $actionName = null, string $eventName = null, array $parameters = []): StimulusActionsDto
    {
        $dto = new StimulusActionsDto($env);
        $dto->addAction($dataOrControllerName, $actionName, $eventName, $parameters);

        return $dto;
    }

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
    public function appendStimulusAction(StimulusActionsDto $dto, $dataOrControllerName, string $actionName = null, string $eventName = null, array $parameters = []): StimulusActionsDto
    {
        $dto->addAction($dataOrControllerName, $actionName, $eventName, $parameters);

        return $dto;
    }

    /**
     * @param string|array $dataOrControllerName This can either be a map of controller names
     *                                           as keys set to their "targets". Or this can
     *                                           be a string controller name and targets are
     *                                           passed as the 2nd argument.
     * @param string|null  $targetNames          The space-separated list of target names if a string is passed to the 1st argument. Optional.
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function renderStimulusTarget(Environment $env, $dataOrControllerName, string $targetNames = null): StimulusTargetsDto
    {
        $dto = new StimulusTargetsDto($env);
        $dto->addTarget($dataOrControllerName, $targetNames);

        return $dto;
    }

    /**
     * @param string|array $dataOrControllerName This can either be a map of controller names
     *                                           as keys set to their "targets". Or this can
     *                                           be a string controller name and targets are
     *                                           passed as the 2nd argument.
     * @param string|null  $targetNames          The space-separated list of target names if a string is passed to the 1st argument. Optional.
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function appendStimulusTarget(StimulusTargetsDto $dto, $dataOrControllerName, string $targetNames = null): StimulusTargetsDto
    {
        $dto->addTarget($dataOrControllerName, $targetNames);

        return $dto;
    }
}
