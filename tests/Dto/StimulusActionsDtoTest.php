<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Dto;

use PHPUnit\Framework\TestCase;
use Symfony\WebpackEncoreBundle\Dto\StimulusActionsDto;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class StimulusActionsDtoTest extends TestCase
{
    /**
     * @var StimulusActionsDto
     */
    private $stimulusActionsDto;

    protected function setUp(): void
    {
        $this->stimulusActionsDto = new StimulusActionsDto(new Environment(new ArrayLoader()));
    }

    public function testToStringEscapingAttributeValues(): void
    {
        $this->stimulusActionsDto->addAction('foo', 'bar', 'baz', ['qux' => '"']);
        $attributesHtml = (string) $this->stimulusActionsDto;
        self::assertSame('data-action="baz->foo#bar" data-foo-qux-param="&quot;"', $attributesHtml);
    }

    public function testToArrayNoEscapingAttributeValues(): void
    {
        $this->stimulusActionsDto->addAction('foo', 'bar', 'baz', ['qux' => '"']);
        $attributesArray = $this->stimulusActionsDto->toArray();
        self::assertSame(['data-action' => 'baz->foo#bar', 'data-foo-qux-param' => '"'], $attributesArray);
    }
}
