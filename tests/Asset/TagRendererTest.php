<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Asset;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Tests\TestEntrypointLookupIntegrityDataProviderInterface;

class TagRendererTest extends TestCase
{
    public function testRenderScriptTagsWithDefaultAttributes()
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
            ->willReturnCallback(function ($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointCollection, $packages, []);

        $output = $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
        $this->assertStringContainsString(
            '<script src="http://localhost:8080/build/file1.js"></script>',
            $output
        );
        $this->assertStringContainsString(
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
            ->willReturnCallback(function ($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointCollection, $packages, ['crossorigin' => 'anonymous']);

        $output = $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
        $this->assertStringContainsString(
            '<script crossorigin="anonymous" src="http://localhost:8080/build/file&lt;&quot;bad_chars.js"></script>',
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
            ->willReturnCallback(function ($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointCollection, $packages, ['crossorigin' => 'anonymous']);

        $output = $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
        $this->assertStringContainsString(
            '<script crossorigin="anonymous" src="http://localhost:8080/build/file1.js"></script>',
            $output
        );
        $output = $renderer->renderWebpackScriptTags('my_entry', null, 'second');
        $this->assertStringContainsString(
            '<script crossorigin="anonymous" src="http://localhost:8080/build/file2.js"></script>',
            $output
        );
        $output = $renderer->renderWebpackScriptTags('my_entry', 'specific_package', 'third');
        $this->assertStringContainsString(
            '<script crossorigin="anonymous" src="http://localhost:8080/build/file3.js"></script>',
            $output
        );
    }

    public function testRenderScriptTagsWithHashes()
    {
        $entrypointLookup = $this->createMock(TestEntrypointLookupIntegrityDataProviderInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file1.js', '/build/file2.js']);
        $entrypointLookup->expects($this->once())
            ->method('getIntegrityData')
            ->willReturn([
                '/build/file1.js' => 'sha384-Q86c+opr0lBUPWN28BLJFqmLhho+9ZcJpXHorQvX6mYDWJ24RQcdDarXFQYN8HLc',
                '/build/file2.js' => 'sha384-ymG7OyjISWrOpH9jsGvajKMDEOP/mKJq8bHC0XdjQA6P8sg2nu+2RLQxcNNwE/3J',
            ]);
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
            ->willReturnCallback(function ($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointCollection, $packages, ['crossorigin' => 'anonymous']);

        $output = $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
        $this->assertStringContainsString(
            '<script crossorigin="anonymous" src="http://localhost:8080/build/file1.js" integrity="sha384-Q86c+opr0lBUPWN28BLJFqmLhho+9ZcJpXHorQvX6mYDWJ24RQcdDarXFQYN8HLc"></script>',
            $output
        );
        $this->assertStringContainsString(
            '<script crossorigin="anonymous" src="http://localhost:8080/build/file2.js" integrity="sha384-ymG7OyjISWrOpH9jsGvajKMDEOP/mKJq8bHC0XdjQA6P8sg2nu+2RLQxcNNwE/3J"></script>',
            $output
        );
    }

    public function testGetRenderedFilesAndReset()
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file1.js', '/build/file2.js']);
        $entrypointLookup->expects($this->once())
            ->method('getCssFiles')
            ->willReturn(['/build/file1.css']);
        $entrypointCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointCollection->expects($this->any())
            ->method('getEntrypointLookup')
            ->willReturn($entrypointLookup);

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->any())
            ->method('getUrl')
            ->willReturnCallback(function ($path) {
                return 'http://localhost:8080'.$path;
            });
        $renderer = new TagRenderer($entrypointCollection, $packages);

        $renderer->renderWebpackScriptTags('my_entry');
        $renderer->renderWebpackLinkTags('my_entry');
        $this->assertSame(['http://localhost:8080/build/file1.js', 'http://localhost:8080/build/file2.js'], $renderer->getRenderedScripts());
        $this->assertSame(['http://localhost:8080/build/file1.css'], $renderer->getRenderedStyles());

        $renderer->reset();
        $this->assertEmpty($renderer->getRenderedScripts());
        $this->assertEmpty($renderer->getRenderedStyles());
    }
}
