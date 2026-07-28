<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle;

/**
 * Chrome palette for the editor wrapper ({@code light}, {@code dark}, {@code auto}).
 *
 * @author Héctor Franco Aceituno <hectorfranco@nowo.tech>
 */
enum EditorTheme: string
{
    case Light = 'light';
    case Dark = 'dark';
    case Auto = 'auto';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromString(string $value): self
    {
        $v = strtolower(trim($value));

        return self::tryFrom($v) ?? self::Light;
    }
}
