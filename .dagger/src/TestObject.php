<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\Container;
use DaggerModule\Enum\DependencyVersionEnum;
use DaggerModule\Enum\MinimumStabilityEnum;
use function Dagger\dag;

#[DaggerObject]
#[Doc('Declaration of functions to run bundle tests.')]
class TestObject
{
    private string $minimumStability = 'stable';
    private string $dependencyVersion = 'locked';

    public function __construct(
        private readonly Container $symfonyContainer,
    ) {
    }

    public function setMinimumStability(string $minimumStability): self
    {
        $this->minimumStability = $minimumStability;

        return $this;
    }

    public function setDependencyVersion(string $dependencyVersion): self
    {
        $this->dependencyVersion = $dependencyVersion;

        return $this;
    }

    #[DaggerFunction]
    #[Doc('Run PHPUnit')]
    public function phpunit(
        string $minimumStability = null,
        string $dependencyVersion = null,
//        MinimumStabilityEnum $minimumStability = MinimumStabilityEnum::STABLE,
//        DependencyVersionEnum $dependencyVersion = DependencyVersionEnum::LOCKED,
    ): Container {
        $minimumStability = MinimumStabilityEnum::from($minimumStability ?? $this->minimumStability);
        $dependencyVersion = DependencyVersionEnum::from($dependencyVersion ?? $this->dependencyVersion);

        $composerCommand = [
            'composer',
            DependencyVersionEnum::LOCKED === $dependencyVersion ? 'install' : 'update',
            '--prefer-dist',
            '--no-progress',
        ];

        if (DependencyVersionEnum::LOWEST === $dependencyVersion) {
            $composerCommand = [
                ...$composerCommand,
                '--prefer-lowest',
                '--prefer-stable',
            ];
        }
        $phpVersion = $this->symfonyContainer->envVariable('PHP_VERSION');
        $symfonyVersion = $this->symfonyContainer->envVariable('SYMFONY_REQUIRE');

        $vendorCache = dag()->cacheVolume(sprintf('php-%s-symfony-%s-phpunit-vendor-cache', $phpVersion, $symfonyVersion));

        return $this->symfonyContainer
            ->withMountedCache('/bundle/vendor', $vendorCache)
            ->withExec(['composer', 'config', 'minimum-stability', $minimumStability->value])
            ->withExec($composerCommand)
            ->withExec(['/bundle/vendor/bin/simple-phpunit'])
        ;
    }
}
