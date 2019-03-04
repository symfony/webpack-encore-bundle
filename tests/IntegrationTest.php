<?php

namespace Symfony\WebpackEncoreBundle\Tests;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\WebpackEncoreBundle\CacheWarmer\EntrypointCacheWarmer;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class IntegrationTest extends TestCase
{
    public function testTwigIntegration()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $html1 = $container->get('twig')->render('@integration_test/template.twig');
        $this->assertContains(
            '<script src="/build/file1.js"></script>',
            $html1
        );
        $this->assertContains(
            '<link rel="stylesheet" href="/build/styles.css">'.
            '<link rel="stylesheet" href="/build/styles2.css">',
            $html1
        );
        $this->assertContains(
            '<script src="/build/other3.js"></script>',
            $html1
        );
        $this->assertContains(
            '<link rel="stylesheet" href="/build/styles3.css">'.
            '<link rel="stylesheet" href="/build/styles4.css">',
            $html1
        );

        $html2 = $container->get('twig')->render('@integration_test/manual_template.twig');
        $this->assertContains(
            '<script src="/build/file3.js"></script>',
            $html2
        );
        $this->assertContains(
            '<script src="/build/other4.js"></script>',
            $html2
        );
    }

    public function testEntriesAreNotRepeteadWhenAlreadyOutputIntegration()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $html1 = $container->get('twig')->render('@integration_test/template.twig');
        $html2 = $container->get('twig')->render('@integration_test/manual_template.twig');
        $this->assertContains(
            '<script src="/build/file3.js"></script>',
            $html2
        );
        // file1.js is not repeated
        $this->assertNotContains(
            '<script src="/build/file1.js"></script>',
            $html2
        );
        // styles3.css is not repeated
        $this->assertNotContains(
            '<link rel="stylesheet" href="/build/styles3.css">',
            $html2
        );
        // styles4.css is not repeated
        $this->assertNotContains(
            '<link rel="stylesheet" href="/build/styles4.css">',
            $html2
        );
    }

    public function testCacheWarmer()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $cacheWarmer = $container->get(WebpackEncoreCacheWarmerTester::class);

        $cacheWarmer->warmCache($kernel->getCacheDir());

        $cachePath = $kernel->getCacheDir().'/webpack_encore.cache.php';
        $this->assertFileExists($cachePath);
        $data = require $cachePath;
        // check for both build keys
        $this->assertEquals(['_default' => 0, 'different_build' => 1], $data[0]);
    }
}

class WebpackEncoreIntegrationTestKernel extends Kernel
{
    private $enableAssets;

    public function __construct($enableAssets)
    {
        parent::__construct('test', true);
        $this->enableAssets = $enableAssets;
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new WebpackEncoreBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function(ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'secret' => 'foo',
                'assets' => [
                    'enabled' => $this->enableAssets,
                ],
            ]);

            $container->loadFromExtension('twig', [
                'paths' => [
                    __DIR__.'/fixtures' => 'integration_test'
                ],
                'strict_variables' => true,
            ]);

            $container->loadFromExtension('webpack_encore', [
                'output_path' => __DIR__.'/fixtures/build',
                'cache' => true,
                'builds' => [
                    'different_build' =>  __DIR__.'/fixtures/different_build'
                ]
            ]);

            $container->register(WebpackEncoreCacheWarmerTester::class)
                ->addArgument(new Reference('webpack_encore.entrypoint_lookup.cache_warmer'))
                ->setPublic(true);
        });
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/cache'.spl_object_hash($this);
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/logs'.spl_object_hash($this);
    }
}

class WebpackEncoreCacheWarmerTester
{
    private $entrypointCacheWarmer;

    public function __construct(EntrypointCacheWarmer $entrypointCacheWarmer)
    {
        $this->entrypointCacheWarmer = $entrypointCacheWarmer;
    }

    public function warmCache(string $cacheDir)
    {
        $this->entrypointCacheWarmer->warmUp($cacheDir);
    }
}
