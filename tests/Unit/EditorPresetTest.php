<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit;

use Nowo\Ckeditor5EditorBundle\EditorPreset;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\EditorPreset
 */
final class EditorPresetTest extends TestCase
{
    public function testValuesContainsAllCases(): void
    {
        self::assertContains('standard', EditorPreset::values());
        self::assertContains('simple', EditorPreset::values());
        self::assertContains('minimal', EditorPreset::values());
        self::assertContains('emoji', EditorPreset::values());
        self::assertContains('typography', EditorPreset::values());
        self::assertContains('variables', EditorPreset::values());
    }

    public function testFromStringMapsKnown(): void
    {
        self::assertSame(EditorPreset::Standard, EditorPreset::fromString('standard'));
        self::assertSame(EditorPreset::Simple, EditorPreset::fromString('SIMPLE'));
        self::assertSame(EditorPreset::Emoji, EditorPreset::fromString('emoji'));
        self::assertSame(EditorPreset::Typography, EditorPreset::fromString('typography'));
        self::assertSame(EditorPreset::Variables, EditorPreset::fromString('variables'));
    }

    public function testFromStringUnknownReturnsStandard(): void
    {
        self::assertSame(EditorPreset::Standard, EditorPreset::fromString('unknown'));
    }
}
