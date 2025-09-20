<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\DefaultPath;
use Dagger\Attribute\Doc;
use Dagger\Attribute\ReturnsListOfType;
use Dagger\Container;
use Dagger\Directory;
use function Amp\async;
use function Amp\Future\await;

#[DaggerObject]
#[Doc('A generated module for WebpackEncoreBundle functions')]
class WebpackEncoreBundle
{
    #[DaggerFunction]
    #[Doc('Access to all tools for static code analysis.')]
    public function static(
        #[DefaultPath('.')]
        Directory $source,

        string $phpVersion = '8.1',
        string $symfonyVersion = '>=5.4',
        ?Container $symfonyContainer = null,
    ): StaticObject {
        $symfonyContainer ??= (new ContainerObject())->symfonyWithVendor($source, $phpVersion, $symfonyVersion);

        return new StaticObject($symfonyContainer);
    }

    #[DaggerFunction]
    #[Doc('Access to all functions for tests.')]
    public function test(
        #[DefaultPath('.')]
        Directory $source,

        string $phpVersion = '8.1',
        string $symfonyVersion = '>=5.4',
        ?Container $symfonyContainer = null,
    ): TestObject {
        $symfonyContainer ??= (new ContainerObject())->symfony($source, $phpVersion, $symfonyVersion);

        return new TestObject($symfonyContainer);
    }

    #[DaggerFunction]
    #[Doc('Matrix tests')]
    #[ReturnsListOfType(TestObject::class)]
    public function testMatrix(
        #[DefaultPath('.')]
        Directory $source,
    ): array {
        $matrix = require __DIR__.'/../matrix-tests.php';

        $tests = [];
        foreach ($matrix as $job) {
            $tests[] = async(function () use ($source, $job) {
                $symfonyArgs = array_filter([
                    'phpVersion' => $job['php-version'] ?? null,
                    'symfonyVersion' => $job['symfony-version'] ?? null,
                ], fn ($value) => null !== $value);

                $testObject = $this->test($source, ...$symfonyArgs);

                if ($job['minimum-stability'] ?? false) {
                    $testObject->setMinimumStability($job['minimum-stability']);
                }

                if ($job['dependency-version'] ?? false) {
                    $testObject->setDependencyVersion($job['dependency-version']);
                }

                return $testObject;
            });
        }

        return await($tests);
    }

    #[DaggerFunction]
    #[Doc('Get test matrix as json.')]
    public function testMatrixJson(): string
    {
        return json_encode(require __DIR__.'/../matrix-tests.php');
    }
}
