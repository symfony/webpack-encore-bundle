<?php
declare(strict_types=1);

namespace Symfony\WebpackEncoreBundle\Asset;


use Symfony\WebpackEncoreBundle\Asset\NonceProviderInterface;

final class NonceNullProvider implements NonceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getNonceValue(): string
    {
        return '';
    }
}
