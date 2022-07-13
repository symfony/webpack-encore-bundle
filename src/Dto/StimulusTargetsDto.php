<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Dto;

final class StimulusTargetsDto extends AbstractStimulusDto
{
    private $targets = [];

    /**
     * @param string      $controllerName the Stimulus controller name
     * @param string|null $targetNames    The space-separated list of target names if a string is passed to the 1st argument. Optional.
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function addTarget($controllerName, string $targetNames = null): void
    {
        if (\is_string($controllerName)) {
            $data = [$controllerName => $targetNames];
        } else {
            if ($targetNames) {
                throw new \InvalidArgumentException('You cannot pass a string to the second argument while passing an array to the first argument of stimulus_target(): check the documentation.');
            }

            $data = $controllerName;

            if (!$data) {
                return;
            }
        }

        foreach ($data as $controllerName => $targetNames) {
            $controllerName = $this->getFormattedControllerName($controllerName);

            $this->targets['data-'.$controllerName.'-target'] = $this->escapeAsHtmlAttr($targetNames);
        }
    }

    public function __toString(): string
    {
        if (0 === \count($this->targets)) {
            return '';
        }

        return implode(' ', array_map(static function (string $attribute, string $value): string {
            return $attribute.'="'.$value.'"';
        }, array_keys($this->targets), $this->targets));
    }

    public function toArray(): array
    {
        return $this->targets;
    }
}
