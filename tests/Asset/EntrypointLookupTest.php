<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Asset;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use PHPUnit\Framework\TestCase;

class EntrypointLookupTest extends TestCase
{
    private $entrypointLookup;

    private static $testJson = <<<EOF
{
  "entrypoints": {
    "my_entry": {
        "js": [
          "file1.js",
          "file2.js"
        ],
        "css": [
          "styles.css",
          "styles2.css"
        ]
    },
    "other_entry": {
      "js": [
        "file1.js",
        "file3.js"
      ],
      "css": []
    }
  },
  "integrity": {
    "file1.js": "sha384-Q86c+opr0lBUPWN28BLJFqmLhho+9ZcJpXHorQvX6mYDWJ24RQcdDarXFQYN8HLc",
    "styles.css": "sha384-ymG7OyjISWrOpH9jsGvajKMDEOP/mKJq8bHC0XdjQA6P8sg2nu+2RLQxcNNwE/3J"
  }
}
EOF;

    public function setUp()
    {
        $filename = tempnam(sys_get_temp_dir(), 'WebpackEncoreBundle');
        file_put_contents($filename, self::$testJson);

        $this->entrypointLookup = new EntrypointLookup($filename);
    }

    public function testGetJavaScriptFiles()
    {
        $this->assertEquals(
            ['file1.js', 'file2.js'],
            $this->entrypointLookup->getJavaScriptFiles('my_entry')
        );

        $this->assertEquals(
            [],
            $this->entrypointLookup->getJavaScriptFiles('my_entry')
        );

        $this->entrypointLookup->reset();

        $this->assertEquals(
            ['file1.js', 'file2.js'],
            $this->entrypointLookup->getJavaScriptFiles('my_entry')
        );
    }

    public function testGetJavaScriptFilesReturnsUniqueFilesOnly()
    {
        $this->assertEquals(
            ['file1.js', 'file2.js'],
            $this->entrypointLookup->getJavaScriptFiles('my_entry')
        );

        $this->assertEquals(
            // file1.js is not returned - it was already returned above
            ['file3.js'],
            $this->entrypointLookup->getJavaScriptFiles('other_entry')
        );
    }

    public function testGetCssFiles()
    {
        $this->assertEquals(
            ['styles.css', 'styles2.css'],
            $this->entrypointLookup->getCssFiles('my_entry')
        );
    }

    public function testEmptyReturnOnValidEntryNoJsOrCssFile()
    {
        $this->assertEmpty(
            $this->entrypointLookup->getCssFiles('other_entry')
        );
    }

    public function testGetIntegrityData()
    {
        $this->assertEquals([
            'file1.js' => 'sha384-Q86c+opr0lBUPWN28BLJFqmLhho+9ZcJpXHorQvX6mYDWJ24RQcdDarXFQYN8HLc',
            'styles.css' => 'sha384-ymG7OyjISWrOpH9jsGvajKMDEOP/mKJq8bHC0XdjQA6P8sg2nu+2RLQxcNNwE/3J',
        ], $this->entrypointLookup->getIntegrityData());
    }

    public function testMissingIntegrityData()
    {
        $filename = tempnam(sys_get_temp_dir(), 'WebpackEncoreBundle');
        file_put_contents($filename, '{ "entrypoints": { "other_entry": { "js": { } } } }');

        $this->entrypointLookup = new EntrypointLookup($filename);
        $this->assertEquals([], $this->entrypointLookup->getIntegrityData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageContains There was a problem JSON decoding the
     */
    public function testExceptionOnInvalidJson()
    {
        $filename = tempnam(sys_get_temp_dir(), 'WebpackEncoreBundle');
        file_put_contents($filename, 'abcd');

        $this->entrypointLookup = new EntrypointLookup($filename);
        $this->entrypointLookup->getJavaScriptFiles('an_entry');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageContains Could not find an "entrypoints" key in the
     */
    public function testExceptionOnMissingEntrypointsKeyInJson()
    {
        $filename = tempnam(sys_get_temp_dir(), 'WebpackEncoreBundle');
        file_put_contents($filename, '{}');

        $this->entrypointLookup = new EntrypointLookup($filename);
        $this->entrypointLookup->getJavaScriptFiles('an_entry');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not find the entrypoints file
     */
    public function testExceptionOnBadFilename()
    {
        $entrypointLookup = new EntrypointLookup('fake_file');
        $entrypointLookup->getCssFiles('anything');
    }

    /**
     * @expectedException \Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException
     * @expectedExceptionMessage Could not find the entry
     */
    public function testExceptionOnMissingEntry()
    {
        $this->entrypointLookup->getCssFiles('fake_entry');
    }

    /**
     * @expectedException \Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException
     * @expectedExceptionMessage Try "my_entry" instead
     */
    public function testExceptionOnEntryWithExtension()
    {
        $this->entrypointLookup->getJavaScriptFiles('my_entry.js');
    }

    public function testCachingEntryPointLookupCacheMissed()
    {
        $filename = tempnam(sys_get_temp_dir(), 'WebpackEncoreBundle');
        file_put_contents($filename, self::$testJson);

        $cache = new ArrayAdapter();
        $entrypointLookup = new EntrypointLookup($filename, $cache, 'cacheKey');

        $this->assertEquals(
            ['file1.js', 'file2.js'],
            $entrypointLookup->getJavaScriptFiles('my_entry')
        );
        // Test it saved the result to cache
        $cached = $cache->getItem('cacheKey');
        $this->assertTrue($cached->isHit());
        $this->assertEquals(json_decode(self::$testJson, true), $cached->get());
    }

    public function testCachingEntryPointLookupCacheHit()
    {
        $filename = tempnam(sys_get_temp_dir(), 'WebpackEncoreBundle');
        file_put_contents($filename, self::$testJson);

        $cache = new ArrayAdapter();
        $entrypointLookup = new EntrypointLookup($filename, $cache, 'cacheKey');

        $cached = $cache->getItem('cacheKey');
        $cached->set(json_decode(self::$testJson, true));
        $cache->save($cached);

        $this->assertEquals(
            ['file1.js', 'file2.js'],
            $entrypointLookup->getJavaScriptFiles('my_entry')
        );
    }
}
