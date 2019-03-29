<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;

interface IntegrityDataProviderInterface
{
    /**
     * Returns a map of integrity hashes indexed by asset paths.
     *
     * If multiples hashes are defined for a given asset they must
     * be separated by a space.
     *
     * For instance:
     * [
     *    'path/to/file1.js' => 'sha384-Q86c+opr0lBUPWN28BLJFqmLhho+9ZcJpXHorQvX6mYDWJ24RQcdDarXFQYN8HLc',
     *    'path/to/styles.css' => 'sha384-ymG7OyjISWrOpH9jsGvajKMDEOP/mKJq8bHC0XdjQA6P8sg2nu+2RLQxcNNwE/3J',
     * ]
     *
     * @return string[]
     */
    public function getIntegrityData(): array;
}
