<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Twig;

use Psr\Container\ContainerInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
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
        return $this->getEntrypointLookup()
            ->getJavaScriptFiles($entryName);
    }

    public function getWebpackCssFiles(string $entryName): array
    {
        return $this->getEntrypointLookup()
            ->getCssFiles($entryName);
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

    private function getEntrypointLookup(): EntrypointLookupInterface
    {
        return $this->container->get('webpack_encore.entrypoint_lookup');
    }

    private function getTagRenderer(): TagRenderer
    {
        return $this->container->get('webpack_encore.tag_renderer');
    }
}
