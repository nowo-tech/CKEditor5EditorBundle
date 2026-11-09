<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\Translation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * MUX-41: every shipped locale translates the heading picker and the toolbar overflow labels.
 */
final class HeadingLabelsTranslationTest extends TestCase
{
    private const KEYS = [
        'ckeditor5_heading_paragraph',
        'ckeditor5_heading_1',
        'ckeditor5_heading_2',
        'ckeditor5_heading_3',
        'ckeditor5_heading',
        'ckeditor5_choose_heading',
        'ckeditor5_toolbar_show_more_items',
    ];

    /** @return iterable<string, array{string, string}> */
    public static function localeProvider(): iterable
    {
        yield 'de' => ['de', 'Absatz'];
        yield 'en' => ['en', 'Paragraph'];
        yield 'es' => ['es', 'Párrafo'];
        yield 'fr' => ['fr', 'Paragraphe'];
        yield 'it' => ['it', 'Paragrafo'];
        yield 'nl' => ['nl', 'Alinea'];
        yield 'pt' => ['pt', 'Parágrafo'];
    }

    #[DataProvider('localeProvider')]
    public function testLocaleTranslatesTheHeadingPicker(string $locale, string $paragraph): void
    {
        $catalogue = Yaml::parseFile(__DIR__ . '/../../../src/Resources/translations/NowoCkeditor5EditorBundle.' . $locale . '.yaml');
        self::assertIsArray($catalogue);

        foreach (self::KEYS as $key) {
            self::assertArrayHasKey($key, $catalogue);
            self::assertIsString($catalogue[$key]);
            self::assertNotSame('', trim($catalogue[$key]));
        }
        self::assertSame($paragraph, $catalogue['ckeditor5_heading_paragraph']);
    }
}
