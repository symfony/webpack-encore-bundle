<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Asset;

use PHPUnit\Framework\TestCase;
use Symfony\WebpackEncoreBundle\Asset\BuildFileLocator;

class BuildFileLocatorTest extends TestCase
{
    /**
     * @dataProvider getFindFileTests
     */
    public function testFindFile(string $buildPath, string $assetPath, string $expectedPath)
    {
        $fileLocator = new BuildFileLocator(['_default' => $buildPath]);
        $fileLocator->disableFileExistsCheck();

        $this->assertSame($expectedPath, $fileLocator->findFile($assetPath));
    }

    public function getFindFileTests()
    {
        yield 'no_overlap' => [
            '/app/public',
            'foo.js',
            '/app/public/foo.js'
        ];

        yield 'simple_overlap' => [
            '/app/public/build',
            'build/foo.js',
            '/app/public/build/foo.js'
        ];

        yield 'simple_overlap_with_slash' => [
            '/app/public/build',
            '/build/foo.js',
            '/app/public/build/foo.js'
        ];

        yield 'simple_overlap_with_build_path_slash' => [
            '/app/public/build/',
            'build/foo.js',
            '/app/public/build/foo.js'
        ];

        yield 'partial_overlap' => [
            '/app/public/build',
            'build/subdirectory/foo.js',
            '/app/public/build/subdirectory/foo.js'
        ];

        yield 'overlap_in_wrong_spot' => [
            '/app/public/build',
            'subdirectory/build/foo.js',
            '/app/public/build/subdirectory/build/foo.js'
        ];

        yield 'windows_paths' => [
            'C:\\\\app\\public\\build',
            'build/foo.js',
            'C:\\\\app\\public\\build/build/foo.js'
        ];
    }

    // test only CSS & JS allowed
}
