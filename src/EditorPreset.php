<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle;

/**
 * Toolbar / plugin presets for the Vite-built CKEditor 5 classic widget.
 *
 * @author Héctor Franco Aceituno <hectorfranco@nowo.tech>
 */
enum EditorPreset: string
{
    /** Rich toolbar: headings, tables, images (URL), alignment, lists, etc. */
    case Standard = 'standard';
    /** Medium toolbar without tables/images/alignment. */
    case Simple = 'simple';
    /** Bold, italic, link, lists only. */
    case Minimal = 'minimal';
    /** Like simple, plus CKEditor Emoji picker and “:” autocomplete (requires Mention). */
    case Emoji = 'emoji';
    /** Font family + font size dropdowns (FontFamily / FontSize plugins). */
    case Typography = 'typography';
    /** Like simple, plus Mention: type @ to insert named placeholders (merge-tag style). */
    case Variables = 'variables';

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

        return self::tryFrom($v) ?? self::Standard;
    }
}
