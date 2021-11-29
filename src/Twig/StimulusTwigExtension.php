<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
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

    /**
     * @param string|array $dataOrControllerName This can either be a map of controller names
     *                                           as keys set to their "values". Or this
     *                                           can be a string controller name and data
     *                                           is passed as the 2nd argument.
     * @param array        $controllerValues     array of data if a string is passed to the 1st argument
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function renderStimulusController(Environment $env, $dataOrControllerName, array $controllerValues = []): string
    {
        if (\is_string($dataOrControllerName)) {
            $data = [$dataOrControllerName => $controllerValues];
        } else {
            if ($controllerValues) {
                throw new \InvalidArgumentException('You cannot pass an array to the first and second argument of stimulus_controller(): check the documentation.');
            }

            $data = $dataOrControllerName;

            if (!$data) {
                return '';
            }
        }

        $controllers = [];
        $values = [];

        foreach ($data as $controllerName => $controllerValues) {
            $controllerName = twig_escape_filter($env, $this->normalizeControllerName($controllerName), 'html_attr');
            $controllers[] = $controllerName;

            foreach ($controllerValues as $key => $value) {
                if (null === $value) {
                    continue;
                }

                if (!is_scalar($value)) {
                    $value = json_encode($value);
                }

                if (\is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                $key = twig_escape_filter($env, $this->normalizeKeyName($key), 'html_attr');
                $value = twig_escape_filter($env, $value, 'html_attr');

                $values[] = 'data-'.$controllerName.'-'.$key.'-value="'.$value.'"';
            }
        }

        return rtrim('data-controller="'.implode(' ', $controllers).'" '.implode(' ', $values));
    }

    /**
     * @param string|array $dataOrControllerName This can either be a map of controller names
     *                                           as keys set to their "actions" and "events".
     *                                           Or this can be a string controller name and
     *                                           action and event are passed as the 2nd and 3rd arguments.
     * @param string|null  $actionName           The action to trigger if a string is passed to the 1st argument. Optional.
     * @param string|null  $eventName            The event to listen to trigger if a string is passed to the 1st argument. Optional.
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function renderStimulusAction(Environment $env, $dataOrControllerName, string $actionName = null, string $eventName = null): string
    {
        if (\is_string($dataOrControllerName)) {
            $data = [$dataOrControllerName => null === $eventName ? [[$actionName]] : [[$eventName => $actionName]]];
        } else {
            if ($actionName || $eventName) {
                throw new \InvalidArgumentException('You cannot pass a string to the second or third argument while passing an array to the first argument of stimulus_action(): check the documentation.');
            }

            $data = $dataOrControllerName;

            if (!$data) {
                return '';
            }
        }

        $actions = [];

        foreach ($data as $controllerName => $controllerActions) {
            $controllerName = twig_escape_filter($env, $this->normalizeControllerName($controllerName), 'html_attr');

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
                    $action = $controllerName.'#'.twig_escape_filter($env, $actionName, 'html_attr');

                    if (\is_string($eventName)) {
                        $action = $eventName.'->'.$action;
                    }

                    $actions[] = $action;
                }
            }
        }

        return 'data-action="'.implode(' ', $actions).'"';
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
    public function renderStimulusTarget(Environment $env, $dataOrControllerName, string $targetNames = null): string
    {
        if (\is_string($dataOrControllerName)) {
            $data = [$dataOrControllerName => $targetNames];
        } else {
            if ($targetNames) {
                throw new \InvalidArgumentException('You cannot pass a string to the second argument while passing an array to the first argument of stimulus_target(): check the documentation.');
            }

            $data = $dataOrControllerName;

            if (!$data) {
                return '';
            }
        }

        $targets = [];

        foreach ($data as $controllerName => $targetNames) {
            $controllerName = twig_escape_filter($env, $this->normalizeControllerName($controllerName), 'html_attr');

            $targets['data-'.$controllerName.'-target'] = twig_escape_filter($env, $targetNames, 'html_attr');
        }

        return implode(' ', array_map(static function (string $attribute, string $value): string {
            return $attribute.'="'.$value.'"';
        }, array_keys($targets), $targets));
    }

    /**
     * Normalize a Stimulus controller name into its HTML equivalent (no special character and / becomes --).
     *
     * @see https://stimulus.hotwired.dev/reference/controllers
     */
    private function normalizeControllerName(string $str): string
    {
        return preg_replace('/^@/', '', str_replace('_', '-', str_replace('/', '--', $str)));
    }

    /**
     * Normalize a Stimulus Value API key into its HTML equivalent ("kebab case").
     * Backport features from symfony/string.
     *
     * @see https://stimulus.hotwired.dev/reference/values
     */
    private function normalizeKeyName(string $str): string
    {
        // Adapted from ByteString::camel
        $str = ucfirst(str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9\x7f-\xff]++/', ' ', $str))));

        // Adapted from ByteString::snake
        return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], '\1-\2', $str));
    }
}
