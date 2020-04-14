<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;



interface NonceProviderInterface
{
    /**
     * Returns a nonce attribute value
     *
     * @return string
     */
    public function getNonceValue(): string;
}
