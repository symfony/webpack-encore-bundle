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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Event\RenderAssetTagEvent;
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
        $renderer = new TagRenderer($entrypointCollection, $packages, ['defer' => true]);

        $output = $renderer->renderWebpackScriptTags('my_entry', 'custom_package');
        $this->assertStringContainsString(
            '<script src="http://localhost:8080/build/file1.js" defer></script>',
            $output
        );
        $this->assertStringContainsString(
            '<script src="http://localhost:8080/build/file2.js" defer></script>',
            $output
        );
    }

    public function testRenderScriptTagsWithExtraAttributes()
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file1.js']);
        $entrypointCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointCollection->expects($this->once())
            ->method('getEntrypointLookup')
            ->willReturn($entrypointLookup);

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->exactly(1))
            ->method('getUrl')
            ->willReturn('http://localhost:8080/build/file1.js');
        $renderer = new TagRenderer($entrypointCollection, $packages, [
            'defer' => true,
            'nonce' => 'abc123'
        ], ['referrerpolicy' => 'origin']);

        $output = $renderer->renderWebpackScriptTags('my_entry', null, null, [
            // override the attribute
            'nonce' => '12345',
        ]);
        $this->assertStringContainsString(
            '<script src="http://localhost:8080/build/file1.js" defer nonce="12345" referrerpolicy="origin"></script>',
            $output
        );
    }

    public function testRenderScriptTagsWithFalseyAttributes()
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file1.js']);
        $entrypointCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointCollection->expects($this->once())
            ->method('getEntrypointLookup')
            ->willReturn($entrypointLookup);

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->once())
            ->method('getUrl')
            ->willReturnCallback(function ($path) {
                return 'http://localhost:8080' . $path;
            });
        $renderer = new TagRenderer($entrypointCollection, $packages, [
            'defer' => false, // false disables the attribute
            'async' => null, // null allows the attribute
        ]);

        $output = $renderer->renderWebpackScriptTags('my_entry');
        $this->assertStringContainsString(
            '<script src="http://localhost:8080/build/file1.js" async></script>',
            $output
        );
    }

    public function testRenderScriptTagsDispatchesAnEvent()
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['/build/file1.js']);
        $entrypointCollection = $this->createMock(EntrypointLookupCollection::class);
        $entrypointCollection->expects($this->once())
            ->method('getEntrypointLookup')
            ->willReturn($entrypointLookup);

        $packages = $this->createMock(Packages::class);
        $packages->expects($this->exactly(1))
            ->method('getUrl')
            ->willReturn('http://localhost:8080/build/file1.js');

        $event = new RenderAssetTagEvent(RenderAssetTagEvent::TYPE_SCRIPT, 'http://foo', [
            'src' => 'http://localhost:8080/build/file1.js',
            'nonce' => 'some_nonce_here',
        ]);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturn($event);

        $renderer = new TagRenderer($entrypointCollection, $packages, [], [], [], $dispatcher);

        $output = $renderer->renderWebpackScriptTags('my_entry');
        $this->assertStringContainsString(
            '<script src="http://localhost:8080/build/file1.js" nonce="some_nonce_here"></script>',
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
            '<script src="http://localhost:8080/build/file&lt;&quot;bad_chars.js" crossorigin="anonymous"></script>',
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
            '<script src="http://localhost:8080/build/file1.js" crossorigin="anonymous"></script>',
            $output
        );
        $output = $renderer->renderWebpackScriptTags('my_entry', null, 'second');
        $this->assertStringContainsString(
            '<script src="http://localhost:8080/build/file2.js" crossorigin="anonymous"></script>',
            $output
        );
        $output = $renderer->renderWebpackScriptTags('my_entry', 'specific_package', 'third');
        $this->assertStringContainsString(
            '<script src="http://localhost:8080/build/file3.js" crossorigin="anonymous"></script>',
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
            '<script src="http://localhost:8080/build/file1.js" crossorigin="anonymous" integrity="sha384-Q86c+opr0lBUPWN28BLJFqmLhho+9ZcJpXHorQvX6mYDWJ24RQcdDarXFQYN8HLc"></script>',
            $output
        );
        $this->assertStringContainsString(
            '<script src="http://localhost:8080/build/file2.js" crossorigin="anonymous" integrity="sha384-ymG7OyjISWrOpH9jsGvajKMDEOP/mKJq8bHC0XdjQA6P8sg2nu+2RLQxcNNwE/3J"></script>',
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
