<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\WebpackEncoreBundle\DependencyInjection\Compiler\RemoveStimulusServicesPass;

final class WebpackEncoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        // run before TwigEnvironmentPass to remove the twig extension before it's used
        $container->addCompilerPass(new RemoveStimulusServicesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
    }
}
