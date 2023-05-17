<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Dto;

use Twig\Environment;

/**
 * @internal
 *
 * @deprecated since 1.17.0 - install symfony/stimulus-bundle instead.
 */
abstract class AbstractStimulusDto implements \Stringable
{
    /**
     * @var Environment
     */
    private $env;

    public function __construct(Environment $env)
    {
        $this->env = $env;
    }

    abstract public function toArray(): array;

    protected function getFormattedControllerName(string $controllerName): string
    {
        return $this->escapeAsHtmlAttr($this->normalizeControllerName($controllerName));
    }

    protected function getFormattedValue($value)
    {
        if ($value instanceof \Stringable || (\is_object($value) && \is_callable([$value, '__toString']))) {
            $value = (string) $value;
        } elseif (!\is_scalar($value)) {
            $value = json_encode($value);
        } elseif (\is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    protected function escapeAsHtmlAttr($value): string
    {
        return (string) twig_escape_filter($this->env, $value, 'html_attr');
    }

    /**
     * Normalize a Stimulus controller name into its HTML equivalent (no special character and / becomes --).
     *
     * @see https://stimulus.hotwired.dev/reference/controllers
     */
    private function normalizeControllerName(string $controllerName): string
    {
        return preg_replace('/^@/', '', str_replace('_', '-', str_replace('/', '--', $controllerName)));
    }
}
