<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\Container;
use Dagger\Directory;
use function Dagger\dag;

#[DaggerObject]
#[Doc('A generated module for WebpackEncoreBundle functions')]
class ContainerObject
{
    public function php(
        Directory $source,
        string $phpVersion,
    ): Container {
        $aptCache = dag()->cacheVolume(sprintf('apt-cache-%s', $phpVersion));
        $composerBin = dag()->container()->from('composer/composer')->file('/usr/bin/composer');

        $container = dag()
            ->container()
            ->from(sprintf('php:%s', $phpVersion))
            ->withMountedCache('/var/cache/apt/archives', $aptCache)
            ->withExec(['apt-get', 'update'])
            ->withExec(['apt-get', 'install', '--yes',
                'git',
                'zip',
            ])
            ->withMountedFile('/usr/bin/composer', $composerBin)
            ->withEnvVariable('COMPOSER_ALLOW_SUPERUSER', '1')
            ->withEnvVariable('COMPOSER_BIN', 'php')
            ->withWorkdir('/bundle')
            ->withMountedDirectory('/bundle', $source)
        ;

        if (empty($container->envVariable('PHP_VERSION'))) {
            $container->withEnvVariable('PHP_VERSION', $phpVersion);
        }

        return $container;
    }

    public function symfony(
        Directory $source,
        string $phpVersion,
        string $symfonyVersion,
        ?Container $phpContainer = null,
    ): Container {
        $phpContainer ??= $this->php($source, $phpVersion);

        return $phpContainer
            ->withEnvVariable('SYMFONY_REQUIRE', $symfonyVersion)
            ->withExec(['composer', 'global', 'config', '--no-plugins', 'allow-plugins.symfony/flex', 'true'])
            ->withExec(['composer', 'global', 'require', 'symfony/flex'])
        ;
    }

    public function symfonyWithVendor(
        Directory $source,
        string $phpVersion,
        string $symfonyVersion,
        ?Container $symfonyContainer = null,
    ): Container {
        $symfonyContainer ??= $this->symfony($source, $phpVersion, $symfonyVersion);

        $vendorCache = dag()->cacheVolume(sprintf('php-%s-symfony-%s-vendor-cache', $phpVersion, $symfonyVersion));

        return $symfonyContainer
            ->withMountedCache('/bundle/vendor', $vendorCache)
            ->withExec(['composer', 'update'])
        ;
    }
}
