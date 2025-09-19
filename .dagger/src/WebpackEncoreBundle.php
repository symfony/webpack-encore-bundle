<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\DefaultPath;
use Dagger\Attribute\Doc;
use Dagger\Container;
use Dagger\Directory;

use function Dagger\dag;

#[DaggerObject]
#[Doc('A generated module for WebpackEncoreBundle functions')]
class WebpackEncoreBundle
{
    #[DaggerFunction]
    #[Doc('Access to all tools for static code analysis.')]
    public function static(
        #[DefaultPath('.')]
        Directory $source,

        string $phpVersion = '8.4',
        string $symfonyVersion = '7.3',
        ?Container $symfonyContainer = null,
    ): StaticObject {
        $symfonyContainer ??= (new ContainerObject())->symfony($source, $phpVersion, $symfonyVersion);

        return new StaticObject($symfonyContainer);
    }
}
