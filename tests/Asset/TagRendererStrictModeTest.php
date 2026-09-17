<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Asset;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Exception\AssetNotFoundException;
use Symfony\Component\Asset\Packages;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

/**
 * When the default asset package is backed by the JSON manifest with
 * strict_mode enabled, it cannot resolve the already-final entrypoint paths a
 * second time and throws. The renderer must fall back to the raw path.
 *
 * @see https://github.com/symfony/webpack-encore-bundle/issues/230
 */
class TagRendererStrictModeTest extends TestCase
{
    public function testRenderScriptTagsFallsBackWhenAssetPackageCannotResolveTheEntrypointPath()
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/web-subfolder/app.53d71f57.js']);
        $entrypointCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointCollection->expects($this->once())
            ->method('getEntrypointLookup')
            ->willReturn($entrypointLookup);

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->once())
            ->method('getUrl')
            ->willThrowException(new AssetNotFoundException('Asset "/web-subfolder/app.53d71f57.js" not found in manifest.'));
        $renderer = new TagRenderer($entrypointCollection, $packages);

        $output = $renderer->renderWebpackScriptTags('my_entry');
        $this->assertStringContainsString(
            '<script src="/web-subfolder/app.53d71f57.js"></script>',
            $output
        );
    }

    public function testRenderLinkTagsFallsBackWhenAssetPackageCannotResolveTheEntrypointPath()
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getCssFiles')
            ->willReturn(['/web-subfolder/app.b75294ae.css']);
        $entrypointCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointCollection->expects($this->once())
            ->method('getEntrypointLookup')
            ->willReturn($entrypointLookup);

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->once())
            ->method('getUrl')
            ->willThrowException(new AssetNotFoundException('Asset "/web-subfolder/app.b75294ae.css" not found in manifest.'));
        $renderer = new TagRenderer($entrypointCollection, $packages);

        $output = $renderer->renderWebpackLinkTags('my_entry');
        $this->assertStringContainsString(
            '<link rel="stylesheet" href="/web-subfolder/app.b75294ae.css">',
            $output
        );
    }
}
