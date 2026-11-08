<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle;

/**
 * How the editor frame is painted (1.x CKE-1, profile key / form option `chrome`): {@code utilities} (default: the
 * Bootstrap-style utility classes `border rounded bg-white shadow-sm` and `p-1`, today's markup) or {@code tokens}
 * (`ckeditor5-editor-chrome--tokens`: frame, toolbar, panels and content typography from the UiKit `--nowo-ui-*`
 * tokens, with fallbacks for hosts without UiKit, dark mode included).
 *
 * @author Héctor Franco Aceituno <hectorfranco@nowo.tech>
 */
enum EditorChrome: string
{
    case Utilities = 'utilities';
    case Tokens    = 'tokens';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Resolves a chrome from its configuration value (unknown values: {@see self::Utilities}). */
    public static function fromString(string $value): self
    {
        return self::tryFrom(strtolower(trim($value))) ?? self::Utilities;
    }
}
