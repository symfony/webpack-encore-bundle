<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Dto;

final class StimulusControllersDto extends AbstractStimulusDto
{
    private $controllers = [];
    private $values = [];

    /**
     * @param string|array $dataOrControllerName This can either be a map of controller names
     *                                           as keys set to their "values". Or this
     *                                           can be a string controller name and data
     *                                           is passed as the 2nd argument.
     * @param array        $controllerValues     array of data if a string is passed to the 1st argument
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function addController($dataOrControllerName, array $controllerValues = []): void
    {
        if (\is_string($dataOrControllerName)) {
            $data = [$dataOrControllerName => $controllerValues];
        } else {
            trigger_deprecation('symfony/webpack-encore-bundle', 'v1.15.0', 'Passing an array as first argument of stimulus_controller() is deprecated.');
            if ($controllerValues) {
                throw new \InvalidArgumentException('You cannot pass an array to the first and second argument of stimulus_controller(): check the documentation.');
            }

            $data = $dataOrControllerName;

            if (!$data) {
                return;
            }
        }

        foreach ($data as $controllerName => $controllerValues) {
            $controllerName = $this->getFormattedControllerName($controllerName);
            $this->controllers[] = $controllerName;

            foreach ($controllerValues as $key => $value) {
                if (null === $value) {
                    continue;
                }

                $key = $this->escapeAsHtmlAttr($this->normalizeKeyName($key));
                $value = $this->getFormattedValue($value);

                $this->values['data-'.$controllerName.'-'.$key.'-value'] = $value;
            }
        }
    }

    public function __toString(): string
    {
        if (0 === \count($this->controllers)) {
            return '';
        }

        return rtrim('data-controller="'.implode(' ', $this->controllers).'" '.implode(' ', array_map(static function (string $attribute, string $value): string {
            return $attribute.'="'.$value.'"';
        }, array_keys($this->values), $this->values)));
    }

    public function toArray(): array
    {
        if (0 === \count($this->controllers)) {
            return [];
        }

        return [
            'data-controller' => implode(' ', $this->controllers),
        ] + $this->values;
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
