<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\CacheWarmer\EntrypointCacheWarmer;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;
use Twig\Environment;

class IntegrationTest extends TestCase
{
    public function testTwigIntegration()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->scriptAttributes = ['referrerpolicy' => 'origin'];
        $kernel->boot();
        $twig = $this->getTwigEnvironmentFromBootedKernel($kernel);

        $html1 = $twig->render('@integration_test/template.twig');
        $this->assertStringContainsString(
            '<script src="/build/file1.js" referrerpolicy="origin" defer integrity="sha384-Q86c+opr0lBUPWN28BLJFqmLhho+9ZcJpXHorQvX6mYDWJ24RQcdDarXFQYN8HLc"></script>',
            $html1
        );
        $this->assertStringContainsString(
            '<link rel="stylesheet" href="/build/styles.css" integrity="sha384-4g+Zv0iELStVvA4/B27g4TQHUMwZttA5TEojjUyB8Gl5p7sarU4y+VTSGMrNab8n">'.
            '<link rel="stylesheet" href="/build/styles2.css" integrity="sha384-hfZmq9+2oI5Cst4/F4YyS2tJAAYdGz7vqSMP8cJoa8bVOr2kxNRLxSw6P8UZjwUn">',
            $html1
        );
        $this->assertStringContainsString(
            '<script src="/build/other3.js" referrerpolicy="origin"></script>',
            $html1
        );
        $this->assertStringContainsString(
            '<link rel="stylesheet" href="/build/styles3.css">'.
            '<link rel="stylesheet" href="/build/styles4.css">',
            $html1
        );

        $html2 = $twig->render('@integration_test/manual_template.twig');
        $this->assertStringContainsString(
            '<script src="/build/file3.js"></script>',
            $html2
        );
        $this->assertStringContainsString(
            '<script src="/build/other4.js"></script>',
            $html2
        );
    }

    public function testEntriesAreNotRepeatedWhenAlreadyOutputIntegration()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->boot();
        $twig = $this->getTwigEnvironmentFromBootedKernel($kernel);

        $html1 = $twig->render('@integration_test/template.twig');
        $html2 = $twig->render('@integration_test/manual_template.twig');
        $this->assertStringContainsString(
            '<script src="/build/file3.js"></script>',
            $html2
        );
        // file1.js is not repeated
        $this->assertStringNotContainsString(
            '<script src="/build/file1.js"></script>',
            $html2
        );
        // styles3.css is not repeated
        $this->assertStringNotContainsString(
            '<link rel="stylesheet" href="/build/styles3.css">',
            $html2
        );
        // styles4.css is not repeated
        $this->assertStringNotContainsString(
            '<link rel="stylesheet" href="/build/styles4.css">',
            $html2
        );
    }

    public function testEntriesExistsWhenDoingSubRequestIntegration()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->boot();

        $request = Request::create('/render-sub-requests');
        $request->attributes->set('template', '@integration_test/template.twig');
        $response = $kernel->handle($request);

        $html = $response->getContent();

        $containsCount0 = substr_count($html, '<script src="/build/file1.js"');
        $this->assertSame(1, $containsCount0);

        $containsCount1 = substr_count($html, '<link rel="stylesheet" href="/build/styles3.css"');
        $this->assertSame(1, $containsCount1);

        $containsCount2 = substr_count($html, '<link rel="stylesheet" href="/build/styles4.css"');
        $this->assertSame(1, $containsCount2);
    }

    public function testCacheWarmer()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->boot();
        $container = $this->getContainerFromBootedKernel($kernel);

        $cacheWarmer = $container->get(WebpackEncoreCacheWarmerTester::class);

        $cacheWarmer->warmCache($kernel->getCacheDir());

        $cachePath = $kernel->getCacheDir().'/webpack_encore.cache.php';
        $this->assertFileExists($cachePath);
        $data = require $cachePath;
        // check for both build keys
        $this->assertSame(['_default', 'different_build'], array_keys($data[0] ?? $data));
    }

    public function testEnabledStrictModeThrowsExceptionIfBuildMissing()
    {
        $this->expectException(\Twig\Error\RuntimeError::class);
        $this->expectExceptionMessage('Could not find the entrypoints file from Webpack: the file "missing_build/entrypoints.json" does not exist.');

        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->outputPath = 'missing_build';
        $kernel->builds = ['different_build' => 'missing_build'];
        $kernel->boot();
        $twig = $this->getTwigEnvironmentFromBootedKernel($kernel);
        $twig->render('@integration_test/template.twig');
    }

    public function testDisabledStrictModeIgnoresMissingBuild()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->outputPath = 'missing_build';
        $kernel->strictMode = false;
        $kernel->builds = ['different_build' => 'missing_build'];
        $kernel->boot();
        $twig = $this->getTwigEnvironmentFromBootedKernel($kernel);
        $html = $twig->render('@integration_test/template.twig');
        self::assertSame('', trim($html));
    }

    public function testAutowireableInterfaces()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->boot();
        $container = $this->getContainerFromBootedKernel($kernel);
        $this->assertInstanceOf(WebpackEncoreAutowireTestService::class, $container->get(WebpackEncoreAutowireTestService::class));
    }

    public function testPreload()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->boot();
        $container = $this->getContainerFromBootedKernel($kernel);

        /** @var TagRenderer $tagRenderer */
        $tagRenderer = $container->get('public.webpack_encore.tag_renderer');
        $tagRenderer->renderWebpackLinkTags('my_entry');
        $tagRenderer->renderWebpackScriptTags('my_entry');

        $request = Request::create('/foo');
        $response = $kernel->handle($request);
        $this->assertStringContainsString('</build/file1.js>; rel="preload"; as="script"', $response->headers->get('Link'));
    }

    public function testAutowireDefaultBuildArgument()
    {
        $kernel = new WebpackEncoreIntegrationTestKernel(true);
        $kernel->boot();
        $container = $this->getContainerFromBootedKernel($kernel);

        $container->get('public.webpack_encore.entrypoint_lookup_collection')
            ->getEntrypointLookup();

        // Testing that it doesn't throw an exception is enough
        $this->assertTrue(true);
    }

    private function getContainerFromBootedKernel(WebpackEncoreIntegrationTestKernel $kernel)
    {
        return $kernel->getContainer()->get('test.service_container');
    }

    private function getTwigEnvironmentFromBootedKernel(WebpackEncoreIntegrationTestKernel $kernel)
    {
        $container = $this->getContainerFromBootedKernel($kernel);

        if ($container->has(\Twig\Environment::class)) {
            return $container->get(\Twig\Environment::class);
        }

        return $container->get('twig');
    }
}

class WebpackEncoreIntegrationTestKernel extends Kernel
{
    use MicroKernelTrait;

    private $enableAssets;
    public $strictMode = true;
    public $outputPath = __DIR__.'/fixtures/build';
    public $builds = [
        'different_build' => __DIR__.'/fixtures/different_build',
    ];
    public $scriptAttributes = [];

    public function __construct(bool $enableAssets)
    {
        parent::__construct('test', true);
        $this->enableAssets = $enableAssets;
    }

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new WebpackEncoreBundle(),
        ];
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('foo', '/foo')->controller('kernel::renderFoo');
        $routes->add('render', '/render')->controller('kernel::renderTwig');
        $routes->add('render_sub_requests', '/render-sub-requests')->controller('kernel::renderSubRequests');
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $frameworkConfig = [
            'secret' => 'foo',
            'assets' => [
                'enabled' => $this->enableAssets,
            ],
            'test' => true,
        ];
        if (self::VERSION_ID >= 50100) {
            $frameworkConfig['router'] = [
                'utf8' => true,
            ];
        }
        if (self::VERSION_ID >= 60200) {
            $frameworkConfig['handle_all_throwables'] = true;
        }
        $container->loadFromExtension('framework', $frameworkConfig);

        $container->loadFromExtension('twig', [
            'paths' => [
                __DIR__.'/fixtures' => 'integration_test',
            ],
            'strict_variables' => true,
            'exception_controller' => null,
        ]);

        $container->loadFromExtension('webpack_encore', [
            'output_path' => $this->outputPath,
            'cache' => true,
            'crossorigin' => false,
            'preload' => true,
            'builds' => $this->builds,
            'strict_mode' => $this->strictMode,
            'script_attributes' => $this->scriptAttributes,
        ]);

        $container->register(WebpackEncoreCacheWarmerTester::class)
            ->addArgument(new Reference('webpack_encore.entrypoint_lookup.cache_warmer'))
            ->setPublic(true);

        $container->autowire(WebpackEncoreAutowireTestService::class)
            ->setPublic(true);

        $container->setAlias(new Alias('public.webpack_encore.tag_renderer', true), 'webpack_encore.tag_renderer');
        $container->getAlias('public.webpack_encore.tag_renderer')->setPublic(true);

        $container->setAlias(new Alias('public.webpack_encore.entrypoint_lookup_collection', true), 'webpack_encore.entrypoint_lookup_collection');
        $container->getAlias('public.webpack_encore.entrypoint_lookup_collection')->setPublic(true);

        // avoid logging request logs
        $container->register('logger', Logger::class)
            ->setArgument(0, LogLevel::EMERGENCY);

        // @legacy for 5.0 and earlier: did not have controller.service_arguments tag
        $container->getDefinition('kernel')
            ->addTag('controller.service_arguments');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/cache'.spl_object_hash($this);
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/logs'.spl_object_hash($this);
    }

    public function renderFoo()
    {
        return new Response('I am a page!');
    }

    public function renderSubRequests(Request $request, HttpKernelInterface $httpKernel)
    {
        $subRequest = Request::create('/render');
        $subRequest->attributes->set('template', $request->attributes->get('template'));

        $response0 = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $response1 = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return new Response($response0->getContent().$response1->getContent());
    }

    public function renderTwig(Environment $twig, Request $request)
    {
        return new Response($twig->render($request->attributes->get('template')));
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

class WebpackEncoreAutowireTestService
{
    public function __construct(EntrypointLookupInterface $entrypointLookup, EntrypointLookupCollectionInterface $entrypointLookupCollection)
    {
    }
}
