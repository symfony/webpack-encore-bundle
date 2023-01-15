<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Dto;

use PHPUnit\Framework\TestCase;
use Symfony\WebpackEncoreBundle\Dto\StimulusTargetsDto;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class StimulusTargetsDtoTest extends TestCase
{
    /**
     * @var StimulusTargetsDto
     */
    private $stimulusTargetsDto;

    protected function setUp(): void
    {
        $this->stimulusTargetsDto = new StimulusTargetsDto(new Environment(new ArrayLoader()));
    }

    public function testToStringEscapingAttributeValues(): void
    {
        $this->stimulusTargetsDto->addTarget('foo', '"');
        $attributesHtml = (string) $this->stimulusTargetsDto;
        self::assertSame('data-foo-target="&quot;"', $attributesHtml);
    }

    public function testToArrayNoEscapingAttributeValues(): void
    {
        $this->stimulusTargetsDto->addTarget('foo', '"');
        $attributesArray = $this->stimulusTargetsDto->toArray();
        self::assertSame(['data-foo-target' => '"'], $attributesArray);
    }
}
