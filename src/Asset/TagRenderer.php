<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;

use Symfony\Component\Asset\Packages;

final class TagRenderer
{
    private $entrypointLookup;

    private $packages;

    public function __construct(EntrypointLookupInterface $entrypointLookup, Packages $packages)
    {
        $this->entrypointLookup = $entrypointLookup;
        $this->packages = $packages;
    }

    public function renderWebpackScriptTags(string $entryName, string $packageName = null): string
    {
        $scriptTags = [];
        foreach ($this->entrypointLookup->getJavaScriptFiles($entryName) as $filename) {
            $scriptTags[] = sprintf(
                '<script src="%s"></script>',
                htmlentities($this->getAssetPath($filename, $packageName))
            );
        }

        return implode('', $scriptTags);
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null): string
    {
        $scriptTags = [];
        foreach ($this->entrypointLookup->getCssFiles($entryName) as $filename) {
            $scriptTags[] = sprintf(
                '<link rel="stylesheet" href="%s" />',
                htmlentities($this->getAssetPath($filename, $packageName))
            );
        }

        return implode('', $scriptTags);
    }

    private function getAssetPath(string $assetPath, string $packageName = null): string
    {
        if (null === $this->packages) {
            throw new \Exception('To render the script or link tags, run "composer require symfony/asset".');
        }

        return $this->packages->getUrl(
            $assetPath,
            $packageName
        );
    }
}
