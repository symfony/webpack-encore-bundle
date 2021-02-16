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
    public function getFunctions()
    {
        return [
            new TwigFunction('stimulus_controller', [$this, 'renderStimulusController'], ['needs_environment' => true, 'is_safe' => ['html_attr']]),
        ];
    }

    /**
     * @param string|array $dataOrControllerName This can either be a map of controller names
     *                                           as keys set to their "values". Or this
     *                                           can be a string controller name and data
     *                                           is passed as the 2nd argument.
     * @param array $controllerValues Array of data if a string is passed to the first argument.
     * @return string
     * @throws \Twig\Error\RuntimeError
     */
    public function renderStimulusController(Environment $env, $dataOrControllerName, array $controllerValues = []): string
    {
        if (is_string($dataOrControllerName)) {
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
                if (!is_scalar($value)) {
                    $value = json_encode($value);
                }

                $key = twig_escape_filter($env, $this->normalizeKeyName($key), 'html_attr');
                $value = twig_escape_filter($env, $value, 'html_attr');

                $values[] = 'data-'.$controllerName.'-'.$key.'-value="'.$value.'"';
            }
        }

        return rtrim('data-controller="'.implode(' ', $controllers).'" '.implode(' ', $values));
    }

    /**
     * Normalize a Stimulus controller name into its HTML equivalent (no special character and / becomes --).
     *
     * @see https://stimulus.hotwire.dev/reference/controllers
     */
    private function normalizeControllerName(string $str): string
    {
        return preg_replace('/^@/', '', str_replace('_', '-', str_replace('/', '--', $str)));
    }

    /**
     * Normalize a Stimulus Value API key into its HTML equivalent ("kebab case").
     * Backport features from symfony/string.
     *
     * @see https://stimulus.hotwire.dev/reference/values
     */
    private function normalizeKeyName(string $str): string
    {
        // Adapted from ByteString::camel
        $str = ucfirst(str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9\x7f-\xff]++/', ' ', $str))));

        // Adapted from ByteString::snake
        return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], '\1-\2', $str));
    }
}
