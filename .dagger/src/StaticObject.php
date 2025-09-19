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
//        return $this->installTool($this->installCs2Pr($this->symfonyContainer), 'phpstan')
//            ->withExec(
//                ['tools/phpstan/vendor/bin/phpstan', 'analyze', '--no-progress', '--error-format=checkstyle'],
//                redirectStdout: '/tmp/foo',
//            )
//            ->withExec(
//                ['cs2pr'],
//                redirectStdin: '/tmp/foo',
//            )
        return $this->installTool($this->symfonyContainer, 'phpstan')
            ->withExec(['tools/phpstan/vendor/bin/phpstan', 'analyze', '--no-progress'])
        ;
    }

    #[DaggerFunction]
    #[Doc('Run Psalm')]
    public function psalm(): Container
    {
        return $this->installTool($this->symfonyContainer, 'psalm')
            ->withExec(['tools/psalm/vendor/bin/psalm', '--no-progress'])
        ;
    }

    #[DaggerFunction]
    #[Doc('Run PHP CS Fixer')]
    public function cs(): Container
    {
        return $this->installTool($this->symfonyContainer, 'php-cs-fixer')
            ->withExec(['tools/php-cs-fixer/vendor/bin/php-cs-fixer', 'fix', '--dry-run', '--diff'])
        ;
    }

    #[DaggerFunction]
    #[Doc('Run PHP CS Fixer')]
    public function composerNormalize(): Container
    {
        $phpVersion = $this->symfonyContainer->envVariable('PHP_VERSION');
        $vendorCache = dag()->cacheVolume(sprintf('composer-normalize-vendor-%s', $phpVersion));

        return $this->symfonyContainer
            ->withMountedCache('/root/.composer/vendor', $vendorCache)
            ->withExec(['composer', 'global', 'require', '--dev', 'ergebnis/composer-normalize'])
            ->withExec(['composer', 'global', 'config', 'allow-plugins.ergebnis/composer-normalize', 'true'])
            ->withExec(['composer', 'normalize'])
        ;
    }

    private function installTool(Container $container, string $tool): Container
    {
        $phpVersion = $container->envVariable('PHP_VERSION');
        $vendorCache = dag()->cacheVolume(sprintf('%s-vendor-%s', $tool, $phpVersion));

        return $container
            ->withMountedCache(sprintf('/bundle/tools/%s/vendor', $tool), $vendorCache)
            ->withExec(['composer', 'install', "--working-dir=tools/{$tool}"])
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
