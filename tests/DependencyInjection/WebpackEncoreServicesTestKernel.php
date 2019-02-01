<?php

namespace Symfony\WebpackEncoreBundle\Tests\DependencyInjection;


use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;

class WebpackEncoreServicesTestKernel extends Kernel
{
    protected $servicesDefinitionPath;

    public function __construct(string $servicesDefinitionPath)
    {
        parent::__construct('test', true);
        $this->servicesDefinitionPath = $servicesDefinitionPath;
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new WebpackEncoreBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {
            $container->loadFromExtension('framework', [
                'secret' => 'foo',
            ]);

            $container->loadFromExtension('webpack_encore', [
                'output_path' => __DIR__.'/../fixtures/build',
            ]);
        });

        $loader->load($this->servicesDefinitionPath);
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
