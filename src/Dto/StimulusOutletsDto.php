<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Dto;

use function array_keys;
use function array_map;
use function implode;

final class StimulusOutletsDto extends AbstractStimulusDto
{
    private $outlets = [];

    /**
     * @param string      $controllerName the Stimulus controller name
     * @param string|null $outletNames    The space-separated list of outlet names if a string is passed to the 1st argument. Optional.
     */
    public function addOutlet(string $controllerName, string $outletNames = null): void
    {
        $controllerName = $this->getFormattedControllerName($controllerName);

        $this->outlets['data-'.$controllerName.'-outlet'] = $outletNames;
    }

    public function __toString(): string
    {
        if (0 === \count($this->outlets)) {
            return '';
        }

        return implode(' ', array_map(function (string $attribute, string $value): string {
            return $attribute.'="'.$this->escapeAsHtmlAttr($value).'"';
        }, array_keys($this->outlets), $this->outlets));
    }

    public function toArray(): array
    {
        return $this->outlets;
    }
}
