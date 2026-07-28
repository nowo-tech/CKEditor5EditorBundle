<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit;

use Nowo\Ckeditor5EditorBundle\EditorTheme;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\EditorTheme
 */
final class EditorThemeTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame(['light', 'dark', 'auto'], EditorTheme::values());
    }

    public function testFromString(): void
    {
        self::assertSame(EditorTheme::Light, EditorTheme::fromString('light'));
        self::assertSame(EditorTheme::Dark, EditorTheme::fromString('DARK'));
        self::assertSame(EditorTheme::Auto, EditorTheme::fromString('auto'));
        self::assertSame(EditorTheme::Light, EditorTheme::fromString('unknown'));
    }
}
