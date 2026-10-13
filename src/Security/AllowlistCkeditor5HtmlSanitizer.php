<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Security;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

use function array_merge;
use function preg_replace;

/**
 * Allowlist-based HTML sanitizer for CKEditor 5 field values (mitigates stored XSS).
 *
 * Built on symfony/html-sanitizer: the input is parsed into a DOM and only allowlisted
 * elements and attributes are kept, so no `<script>`, `on*` event handler, `srcdoc` or
 * other unknown attribute survives, whatever the quoting or spacing. URL attributes
 * are checked after entity decoding: links accept `http`, `https`, `mailto` and relative
 * URLs; images accept `http`, `https` and relative URLs (no `javascript:`, `data:`, `vbscript:`).
 *
 * When `$allowEmbeds` is true (config `html_sanitizer: allowlist`), a YouTube or Vimeo
 * iframe is kept only as an `https` `src` whose host is on the embed list. `html_sanitizer: strict`
 * sets `$allowEmbeds` to false and drops every iframe (legal pages and other public HTML).
 */
final readonly class AllowlistCkeditor5HtmlSanitizer implements Ckeditor5HtmlSanitizerInterface
{
    /** Attributes allowed on every allowlisted element. */
    private const GLOBAL_ATTRIBUTES = ['class', 'style', 'title', 'lang', 'dir'];

    /** @var array<string, list<string>> element => extra allowed attributes */
    private const ALLOWED_ELEMENTS = [
        'p'          => [],
        'br'         => [],
        'strong'     => [],
        'b'          => [],
        'em'         => [],
        'i'          => [],
        'u'          => [],
        's'          => [],
        'del'        => [],
        'h1'         => [],
        'h2'         => [],
        'h3'         => [],
        'h4'         => [],
        'h5'         => [],
        'h6'         => [],
        'ul'         => [],
        'ol'         => ['start', 'reversed', 'type'],
        'li'         => ['value'],
        'blockquote' => [],
        'code'       => [],
        'pre'        => [],
        'a'          => ['href', 'target', 'rel'],
        'img'        => ['src', 'alt', 'width', 'height'],
        'table'      => [],
        'thead'      => [],
        'tbody'      => [],
        'tr'         => [],
        'th'         => ['colspan', 'rowspan', 'scope'],
        'td'         => ['colspan', 'rowspan'],
        'caption'    => [],
        'hr'         => [],
        'span'       => [],
        'div'        => [],
        'figure'     => [],
        'figcaption' => [],
        'sub'        => [],
        'sup'        => [],
        'mark'       => [],
    ];

    /**
     * Unlisted wrappers whose text is kept (like strip_tags did); any other unlisted element
     * (script, style, object, svg, math, form…) is dropped with its content.
     *
     * @var list<string>
     */
    private const BLOCKED_ELEMENTS = [
        'section', 'article', 'header', 'footer', 'main', 'aside', 'nav', 'font', 'center',
        'small', 'big', 'dl', 'dt', 'dd', 'abbr', 'cite', 'q', 'ins', 'kbd', 'samp', 'var',
        'time', 'address', 'tfoot', 'label',
    ];

    /** @var list<string> */
    private const ALLOWED_EMBED_HOSTS = [
        'www.youtube.com',
        'youtube.com',
        'www.youtube-nocookie.com',
        'player.vimeo.com',
    ];

    private HtmlSanitizerInterface $sanitizer;

    public function __construct(
        private bool $allowEmbeds = true,
    ) {
        $config = (new HtmlSanitizerConfig())
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias()
            ->withMaxInputLength(-1);

        foreach (self::ALLOWED_ELEMENTS as $element => $attributes) {
            $config = $config->allowElement($element, array_merge(self::GLOBAL_ATTRIBUTES, $attributes));
        }
        foreach (self::BLOCKED_ELEMENTS as $element) {
            $config = $config->blockElement($element);
        }
        if ($this->allowEmbeds) {
            $config = $config
                ->allowElement('iframe', ['src'])
                ->withAttributeSanitizer(new EmbedIframeSrcAttributeSanitizer(self::ALLOWED_EMBED_HOSTS));
        }

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function sanitize(string $html): string
    {
        $html = $this->sanitizer->sanitize($html);

        if (!$this->allowEmbeds) {
            return $html;
        }

        // An iframe whose src was rejected (or missing) is useless: remove the empty shell.
        return preg_replace('/<iframe><\/iframe>/i', '', $html) ?? $html;
    }
}
