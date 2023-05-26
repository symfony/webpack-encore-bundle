<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;

use Symfony\Component\Asset\Packages;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\WebpackEncoreBundle\Event\RenderAssetTagEvent;

/**
 * @final
 */
class TagRenderer implements ResetInterface
{
    private $entrypointLookupCollection;
    private $packages;
    private $defaultAttributes;
    private $defaultScriptAttributes;
    private $defaultLinkAttributes;
    private $eventDispatcher;

    private $renderedFiles = [];

    public function __construct(
        EntrypointLookupCollectionInterface $entrypointLookupCollection,
        Packages $packages,
        array $defaultAttributes = [],
        array $defaultScriptAttributes = [],
        array $defaultLinkAttributes = [],
        EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->entrypointLookupCollection = $entrypointLookupCollection;
        $this->packages = $packages;
        $this->defaultAttributes = $defaultAttributes;
        $this->defaultScriptAttributes = $defaultScriptAttributes;
        $this->defaultLinkAttributes = $defaultLinkAttributes;
        $this->eventDispatcher = $eventDispatcher;

        $this->reset();
    }

    public function renderWebpackScriptTags(string $entryName, string $packageName = null, string $entrypointName = null, array $extraAttributes = []): string
    {
        $entrypointName = $entrypointName ?: '_default';
        $scriptTags = [];
        $entryPointLookup = $this->getEntrypointLookup($entrypointName);
        $integrityHashes = ($entryPointLookup instanceof IntegrityDataProviderInterface) ? $entryPointLookup->getIntegrityData() : [];

        foreach ($entryPointLookup->getJavaScriptFiles($entryName) as $filename) {
            $attributes = [];
            $attributes['src'] = $this->getAssetPath($filename, $packageName);
            $attributes = array_merge($attributes, $this->defaultAttributes, $this->defaultScriptAttributes, $extraAttributes);

            if (isset($integrityHashes[$filename])) {
                $attributes['integrity'] = $integrityHashes[$filename];
            }

            $event = new RenderAssetTagEvent(
                RenderAssetTagEvent::TYPE_SCRIPT,
                $attributes['src'],
                $attributes
            );
            if (null !== $this->eventDispatcher) {
                $event = $this->eventDispatcher->dispatch($event);
            }
            $attributes = $event->getAttributes();

            $scriptTags[] = sprintf(
                '<script %s></script>',
                $this->convertArrayToAttributes($attributes)
            );

            $this->renderedFiles['scripts'][] = $attributes['src'];
        }

        return implode('', $scriptTags);
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null, string $entrypointName = null, array $extraAttributes = []): string
    {
        $entrypointName = $entrypointName ?: '_default';
        $scriptTags = [];
        $entryPointLookup = $this->getEntrypointLookup($entrypointName);
        $integrityHashes = ($entryPointLookup instanceof IntegrityDataProviderInterface) ? $entryPointLookup->getIntegrityData() : [];

        foreach ($entryPointLookup->getCssFiles($entryName) as $filename) {
            $attributes = [];
            $attributes['rel'] = 'stylesheet';
            $attributes['href'] = $this->getAssetPath($filename, $packageName);
            $attributes = array_merge($attributes, $this->defaultAttributes, $this->defaultLinkAttributes, $extraAttributes);

            if (isset($integrityHashes[$filename])) {
                $attributes['integrity'] = $integrityHashes[$filename];
            }

            $event = new RenderAssetTagEvent(
                RenderAssetTagEvent::TYPE_LINK,
                $attributes['href'],
                $attributes
            );
            if (null !== $this->eventDispatcher) {
                $this->eventDispatcher->dispatch($event);
            }
            $attributes = $event->getAttributes();

            $scriptTags[] = sprintf(
                '<link %s>',
                $this->convertArrayToAttributes($attributes)
            );

            $this->renderedFiles['styles'][] = $attributes['href'];
        }

        return implode('', $scriptTags);
    }

    public function getRenderedScripts(): array
    {
        return $this->renderedFiles['scripts'];
    }

    public function getRenderedStyles(): array
    {
        return $this->renderedFiles['styles'];
    }

    public function getDefaultAttributes(): array
    {
        return $this->defaultAttributes;
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->renderedFiles = [
            'scripts' => [],
            'styles' => [],
        ];
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

    private function getEntrypointLookup(string $buildName): EntrypointLookupInterface
    {
        return $this->entrypointLookupCollection->getEntrypointLookup($buildName);
    }

    private function convertArrayToAttributes(array $attributesMap): string
    {
        // remove attributes set specifically to false
        $attributesMap = array_filter($attributesMap, static function ($value) {
            return false !== $value;
        });

        return implode(' ', array_map(
            static function ($key, $value) {
                // allows for things like defer: true to only render "defer"
                if (true === $value || null === $value) {
                    return $key;
                }

                return sprintf('%s="%s"', $key, htmlentities($value));
            },
            array_keys($attributesMap),
            $attributesMap
        ));
    }
}
