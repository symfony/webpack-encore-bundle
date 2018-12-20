<?php

namespace Symfony\WebpackEncoreBundle\Tests\Asset;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

class TagRendererTest extends TestCase
{
    public function testRenderScriptTags()
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file1.js', '/build/file2.js']);
        $entrypointCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointCollection->expects($this->once())
            ->method('getEntrypointLookup')
            ->withConsecutive(['_default'])
            ->will($this->onConsecutiveCalls($entrypointLookup));

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->exactly(2))
            ->method('getUrl')
            ->withConsecutive(
                ['/build/file1.js', 'custom_package'],
                ['/build/file2.js', 'custom_package']
            )
            ->willReturnCallback(function($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointCollection, $packages);

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
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file<"bad_chars.js']);
        $entrypointCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointCollection->expects($this->once())
            ->method('getEntrypointLookup')
            ->withConsecutive(['_default'])
            ->will($this->onConsecutiveCalls($entrypointLookup));

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->once())
            ->method('getUrl')
            ->willReturnCallback(function($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointCollection, $packages);

        $output = $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
        $this->assertContains(
            '<script src="http://localhost:8080/build/file&lt;&quot;bad_chars.js"></script>',
            $output
        );
    }

    public function testRenderScriptTagsWithinAnEntryPointCollection()
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file1.js']);

        $secondEntrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $secondEntrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file2.js']);
        $thirdEntrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $thirdEntrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file3.js']);

        $entrypointCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointCollection->expects($this->exactly(3))
            ->method('getEntrypointLookup')
            ->withConsecutive(['_default'], ['second'], ['third'])
            ->will($this->onConsecutiveCalls(
                $entrypointLookup,
                $secondEntrypointLookup,
                $thirdEntrypointLookup
            ));

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->exactly(3))
            ->method('getUrl')
            ->withConsecutive(
                ['/build/file1.js', 'custom_package'],
                ['/build/file2.js', null],
                ['/build/file3.js', 'specific_package']
            )
            ->willReturnCallback(function($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointCollection, $packages);

        $output = $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
        $this->assertContains(
            '<script src="http://localhost:8080/build/file1.js"></script>',
            $output
        );
        $output = $renderer->renderWebpackScriptTags('my_entry', null, 'second');
        $this->assertContains(
            '<script src="http://localhost:8080/build/file2.js"></script>',
            $output
        );
        $output = $renderer->renderWebpackScriptTags('my_entry', 'specific_package', 'third');
        $this->assertContains(
            '<script src="http://localhost:8080/build/file3.js"></script>',
            $output
        );
    }

}
