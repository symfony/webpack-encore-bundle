<?php

namespace Symfony\WebpackEncoreBundle\Tests\Asset;

use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\ManifestLookup;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;

class TagRendererTest extends TestCase
{
    public function testRenderScriptTags()
    {
        $entrypointLookup = $this->createMock(EntrypointLookup::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['build/file1.js', 'build/file2.js']);

        $manifestLookup = $this->createMock(ManifestLookup::class);
        $manifestLookup->expects($this->exactly(2))
            ->method('getManifestPath')
            ->withConsecutive(
                ['build/file1.js'],
                ['build/file2.js']
            )
            ->willReturnCallback(function($path) {
                return '/'.$path;
            });

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->exactly(2))
            ->method('getUrl')
            ->withConsecutive(
                ['/build/file1.js'],
                ['/build/file2.js']
            )
            ->willReturnCallback(function($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointLookup, $manifestLookup, $packages);

        $output = $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
        $this->assertContains(
            '<script src="http://localhost:8080/build/file1.js"></script>',
            $output
        );
        $this->assertContains(
            '<script src="http://localhost:8080/build/file2.js"></script>',
            $output
        );
    }

    public function testRenderScriptTagsWithBadFilename()
    {
        $entrypointLookup = $this->createMock(EntrypointLookup::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['build/file<"bad_chars.js']);

        $manifestLookup = $this->createMock(ManifestLookup::class);
        $manifestLookup->expects($this->once())
            ->method('getManifestPath')
            ->willReturnCallback(function($path) {
                return '/'.$path;
            });

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->once())
            ->method('getUrl')
            ->willReturnCallback(function($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointLookup, $manifestLookup, $packages);

        $output = $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
        $this->assertContains(
            '<script src="http://localhost:8080/build/file&lt;&quot;bad_chars.js"></script>',
            $output
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The path "foo/file1.js" could not be found in the Encore "manifest.json"
     */
    public function testBadAssetPrefixThrowException()
    {
        $entrypointLookup = $this->createMock(EntrypointLookup::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['foo/file1.js', 'bar/file2.js']);

        $manifestLookup = $this->createMock(ManifestLookup::class);
        $manifestLookup->expects($this->once())
            ->method('getManifestPath')
            ->willReturn(null);

        $packages = $this->createMock(Packages::class);
        $renderer = new TagRenderer($entrypointLookup, $manifestLookup, $packages);

        $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
    }
}
