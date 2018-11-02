<?php

namespace Symfony\WebpackEncoreBundle\Tests;

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

        $html2 = $container->get('twig')->render('@integration_test/template.twig');
        $this->assertContains(
            '<script src="/build/file1.js"></script>',
            $html2
        );

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
            ]);
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