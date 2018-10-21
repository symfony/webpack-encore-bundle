<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Twig;

use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\ManifestLookup;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class EntryFilesTwigExtension extends AbstractExtension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('encore_entry_js_files', [$this, 'getWebpackJsFiles']),
            new TwigFunction('encore_entry_css_files', [$this, 'getWebpackCssFiles']),
            new TwigFunction('encore_entry_script_tags', [$this, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this, 'renderWebpackLinkTags'], ['is_safe' => ['html']]),
        ];
    }

    public function getWebpackJsFiles(string $entryName): array
    {
        $jsFiles = $this->getEntrypointLookup()
            ->getJavaScriptFiles($entryName);

        return array_map(function ($path) {
            return $this->getManifestLookup()->getManifestPath($path);
        }, $jsFiles);
    }

    public function getWebpackCssFiles(string $entryName): array
    {
        $cssFiles = $this->getEntrypointLookup()
            ->getCssFiles($entryName);

        return array_map(function ($path) {
            return $this->getManifestLookup()->getManifestPath($path);
        }, $cssFiles);
    }

    public function renderWebpackScriptTags(string $entryName, string $packageName = null): string
    {
        return $this->getTagRenderer()
            ->renderWebpackScriptTags($entryName, $packageName);
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null): string
    {
        return $this->getTagRenderer()
            ->renderWebpackLinkTags($entryName, $packageName);
    }

    private function getEntrypointLookup(): EntrypointLookup
    {
        return $this->container->get('webpack_encore.entrypoint_lookup');
    }

    private function getManifestLookup(): ManifestLookup
    {
        return $this->container->get('webpack_encore.manifest_lookup');
    }

    private function getTagRenderer(): TagRenderer
    {
        return $this->container->get('webpack_encore.tag_renderer');
    }
}
