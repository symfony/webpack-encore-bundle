<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Tests\Dto;

use PHPUnit\Framework\TestCase;
use Symfony\WebpackEncoreBundle\Dto\StimulusOutletsDto;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class StimulusOutletsDtoTest extends TestCase
{
    /**
     * @var StimulusOutletsDto
     */
    private $stimulusOutletsDto;

    protected function setUp(): void
    {
        $this->stimulusOutletsDto = new StimulusOutletsDto(new Environment(new ArrayLoader()));
    }

    public function testToStringEscapingAttributeValues(): void
    {
        $this->stimulusOutletsDto->addOutlet('foo', '"');
        $attributesHtml = (string) $this->stimulusOutletsDto;
        self::assertSame('data-foo-outlet="&quot;"', $attributesHtml);
    }

    public function testToArrayNoEscapingAttributeValues(): void
    {
        $this->stimulusOutletsDto->addOutlet('foo', '"');
        $attributesArray = $this->stimulusOutletsDto->toArray();
        self::assertSame(['data-foo-outlet' => '"'], $attributesArray);
    }
}
