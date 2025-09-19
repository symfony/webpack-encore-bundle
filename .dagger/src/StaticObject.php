<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\Container;
use function Dagger\dag;

#[DaggerObject]
#[Doc('All tools to run static code analysis.')]
class StaticObject
{
    public function __construct(
        private readonly Container $symfonyContainer,
    ) {
    }

    #[DaggerFunction]
    #[Doc('Run PHPStan')]
    public function phpstan(): Container
    {
        $phpVersion = $this->symfonyContainer->envVariable('PHP_VERSION');
        $vendorCache = dag()->cacheVolume(sprintf('phpstan-vendor-%s', $phpVersion));

        return $this->installCs2Pr($this->symfonyContainer)
            ->withMountedCache('/bundle/tools/phpstan/vendor', $vendorCache)
            ->withExec(['composer', 'install', '--working-dir=tools/phpstan'])
//            ->withExec(
//                ['tools/phpstan/vendor/bin/phpstan', 'analyze', '--no-progress', '--error-format=checkstyle'],
//                redirectStdout: '/tmp/foo',
//            )
//            ->withExec(
//                ['cs2pr'],
//                redirectStdin: '/tmp/foo',
//            )
            ->withExec(['tools/phpstan/vendor/bin/phpstan', 'analyze', '--no-progress'])
        ;
    }

    private function installCs2Pr(Container $container): Container
    {
        $phpVersion = $container->envVariable('PHP_VERSION');
        $vendorCache = dag()->cacheVolume(sprintf('cs2pr-vendor-%s', $phpVersion));

        $path = $container->envVariable('PATH').':/root/.composer/vendor/bin';

        return $container
            ->withMountedCache('/root/.composer/vendor', $vendorCache)
            ->withExec(['composer', 'global', 'require', 'staabm/annotate-pull-request-from-checkstyle'])
            ->withEnvVariable('PATH', $path)
        ;
    }
}
