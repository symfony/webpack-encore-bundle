<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Twig;

use Psr\Container\ContainerInterface;
use Symfony\WebpackEncoreBundle\Asset\BuildFileLocator;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
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
            new TwigFunction('encore_entry_js_source', [$this, 'getWebpackJsSource']),
            new TwigFunction('encore_entry_css_source', [$this, 'getWebpackCssSource']),
            new TwigFunction('encore_entry_script_tags', [$this, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this, 'renderWebpackLinkTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_disable_file_tracking', [$this, 'disableReturnedFileTracking']),
            new TwigFunction('encore_enable_file_tracking', [$this, 'enableReturnedFileTracking']),
        ];
    }

    public function getWebpackJsFiles(string $entryName, string $entrypointName = '_default'): array
    {
        return $this->getEntrypointLookup($entrypointName)
            ->getJavaScriptFiles($entryName);
    }

    public function getWebpackCssFiles(string $entryName, string $entrypointName = '_default'): array
    {
        return $this->getEntrypointLookup($entrypointName)
            ->getCssFiles($entryName);
    }

    public function getWebpackJsSource(string $entryName, string $entrypointName = '_default'): string
    {
        $originalTrackingValue = $this->isReturnedFileTrackingEnabled($entrypointName);
        $this->changeReturnedFileTracking(false, $entrypointName);

        $files = $this->getEntrypointLookup($entrypointName)
            ->getJavaScriptFiles($entryName);

        $source = $this->concatenateFileSources($files);
        $this->changeReturnedFileTracking($originalTrackingValue, $entrypointName);

        return $source;
    }

    public function getWebpackCssSource(string $entryName, string $entrypointName = '_default'): string
    {
        $originalTrackingValue = $this->isReturnedFileTrackingEnabled($entrypointName);
        $this->changeReturnedFileTracking(false, $entrypointName);

        $files = $this->getEntrypointLookup($entrypointName)
            ->getCssFiles($entryName);

        $source = $this->concatenateFileSources($files);
        $this->changeReturnedFileTracking($originalTrackingValue, $entrypointName);

        return $source;
    }

    public function renderWebpackScriptTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        return $this->getTagRenderer()
            ->renderWebpackScriptTags($entryName, $packageName, $entrypointName);
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        return $this->getTagRenderer()
            ->renderWebpackLinkTags($entryName, $packageName, $entrypointName);
    }

    public function disableReturnedFileTracking(string $entrypointName = '_default')
    {
        $this->changeReturnedFileTracking(false, $entrypointName);
    }

    public function enableReturnedFileTracking(string $entrypointName = '_default')
    {
        $this->changeReturnedFileTracking(true, $entrypointName);
    }

    private function changeReturnedFileTracking(bool $isEnabled, string $entrypointName)
    {
        $lookup = $this->getEntrypointLookup($entrypointName);

        if (!$lookup instanceof EntrypointLookup) {
            throw new \LogicException('In order to use encore_disable_returned_file_tracking/encore_enable_returned_file_tracking, the EntrypointLookupInterface must be an instance of EntrypointLookup.');
        }

        $lookup->enableReturnedFileTracking($isEnabled);
    }

    private function isReturnedFileTrackingEnabled(string $entrypointName): bool
    {
        $lookup = $this->getEntrypointLookup($entrypointName);

        if (!$lookup instanceof EntrypointLookup) {
            throw new \LogicException('In order to use encore_entry_js_source/encore_entry_css_source, the EntrypointLookupInterface must be an instance of EntrypointLookup.');
        }

        return $lookup->isReturnedFileTrackingEnabled();
    }

    private function concatenateFileSources(array $files): string
    {
        $locator = $this->getBuildFileLocator();
        $source = '';
        foreach ($files as $file) {
            $source .= file_get_contents($locator->findFile($file));
        }

        return $source;
    }

    private function getEntrypointLookup(string $entrypointName): EntrypointLookupInterface
    {
        return $this->container->get('webpack_encore.entrypoint_lookup_collection')
            ->getEntrypointLookup($entrypointName);
    }

    private function getTagRenderer(): TagRenderer
    {
        return $this->container->get('webpack_encore.tag_renderer');
    }

    private function getBuildFileLocator(): BuildFileLocator
    {
        return $this->container->get('webpack_encore.build_file_locator');
    }
}
