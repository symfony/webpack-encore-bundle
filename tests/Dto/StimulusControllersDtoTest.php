<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Dto;

use PHPUnit\Framework\TestCase;
use Symfony\WebpackEncoreBundle\Dto\StimulusControllersDto;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class StimulusControllersDtoTest extends TestCase
{
    /**
     * @var StimulusControllersDto
     */
    private $stimulusControllersDto;

    protected function setUp(): void
    {
        $this->stimulusControllersDto = new StimulusControllersDto(new Environment(new ArrayLoader()));
    }

    public function testToStringEscapingAttributeValues(): void
    {
        $this->stimulusControllersDto->addController('foo', ['bar' => '"'], ['baz' => '"']);
        $attributesHtml = (string) $this->stimulusControllersDto;
        self::assertSame(
            'data-controller="foo" '.
            'data-foo-bar-value="&quot;" '.
            'data-foo-baz-class="&quot;"',
            $attributesHtml
        );
    }

    public function testToArrayNoEscapingAttributeValues(): void
    {
        $this->stimulusControllersDto->addController('foo', ['bar' => '"'], ['baz' => '"']);
        $attributesArray = $this->stimulusControllersDto->toArray();
        self::assertSame(
            [
                'data-controller' => 'foo',
                'data-foo-bar-value' => '"',
                'data-foo-baz-class' => '"',
            ],
            $attributesArray
        );
    }
}
