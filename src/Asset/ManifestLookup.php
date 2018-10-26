<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;

/**
 * Looks up values in Encore's `manifest.json` and returns them.
 *
 * @final
 */
class ManifestLookup
{
    private $manifestPath;
    private $manifestData;

    /**
     * @param string $manifestPath Absolute path to the manifest.json file
     */
    public function __construct(string $manifestPath)
    {
        $this->manifestPath = $manifestPath;
    }

    public function getManifestPath(string $path): ?string
    {
        if (null === $this->manifestData) {
            if (!file_exists($this->manifestPath)) {
                throw new \RuntimeException(sprintf('Asset manifest file "%s" does not exist.', $this->manifestPath));
            }

            $this->manifestData = json_decode(file_get_contents($this->manifestPath), true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException(sprintf('Error parsing JSON from asset manifest file "%s" - %s', $this->manifestPath, json_last_error_msg()));
            }
        }

        return $this->manifestData[$path] ?? null;
    }
}
