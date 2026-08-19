<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\Form\DataTransformer;

use Nowo\Ckeditor5EditorBundle\Form\DataTransformer\Ckeditor5HtmlSanitizeTransformer;
use Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\Form\DataTransformer\Ckeditor5HtmlSanitizeTransformer
 */
final class Ckeditor5HtmlSanitizeTransformerTest extends TestCase
{
    public function testReverseTransformSanitizesHtml(): void
    {
        $transformer = new Ckeditor5HtmlSanitizeTransformer(new AllowlistCkeditor5HtmlSanitizer());

        $out = $transformer->reverseTransform('<p>ok</p><script>alert(1)</script>');

        self::assertSame('<p>ok</p>', $out);
    }

    public function testTransformIsIdentity(): void
    {
        $transformer = new Ckeditor5HtmlSanitizeTransformer(new AllowlistCkeditor5HtmlSanitizer());

        self::assertSame('<p>x</p>', $transformer->transform('<p>x</p>'));
    }

    public function testReverseTransformPassesThroughNonString(): void
    {
        $transformer = new Ckeditor5HtmlSanitizeTransformer(new AllowlistCkeditor5HtmlSanitizer());

        self::assertNull($transformer->reverseTransform(null));
    }
}
