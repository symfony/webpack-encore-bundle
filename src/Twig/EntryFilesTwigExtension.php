<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Twig;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class EntryFilesTwigExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private $container;
    private $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_js_files', [$this, 'getWebpackJsFiles']),
            new TwigFunction('encore_entry_css_files', [$this, 'getWebpackCssFiles']),
            new TwigFunction('encore_entry_script_tags', [$this, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this, 'renderWebpackLinkTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_exists', [$this, 'entryExists']),
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

    public function renderWebpackScriptTags(string $entryName, string $packageName = null, string $entrypointName = '_default', array $attributes = []): string
    {
        try {
            return $this->getTagRenderer()
                ->renderWebpackScriptTags($entryName, $packageName, $entrypointName, $attributes);
        } catch (\InvalidArgumentException $e) {
            if (!$this->logger) {
                throw $e;
            }
            $this->logger->warning($e->getMessage().' Did you forget to build the assets with npm or yarn?');

            return '';
        }
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null, string $entrypointName = '_default', array $attributes = []): string
    {
        try {
            return $this->getTagRenderer()
                ->renderWebpackLinkTags($entryName, $packageName, $entrypointName, $attributes);
        } catch (\InvalidArgumentException $e) {
            if (!$this->logger) {
                throw $e;
            }
            $this->logger->warning($e->getMessage().' Did you forget to build the assets with npm or yarn?');

            return '';
        }
    }

    public function entryExists(string $entryName, string $entrypointName = '_default'): bool
    {
        $entrypointLookup = $this->getEntrypointLookup($entrypointName);
        if (!$entrypointLookup instanceof EntrypointLookup) {
            throw new \LogicException(sprintf('Cannot use entryExists() unless the entrypoint lookup is an instance of "%s"', EntrypointLookup::class));
        }

        return $entrypointLookup->entryExists($entryName);
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

    public static function getSubscribedServices(): array
    {
        return [
            'webpack_encore.entrypoint_lookup_collection' => EntrypointLookupInterface::class,
            'webpack_encore.tag_renderer' => TagRenderer::class,
        ];
    }
}
