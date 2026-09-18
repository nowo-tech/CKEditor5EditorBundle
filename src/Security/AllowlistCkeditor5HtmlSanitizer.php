<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Security;

use function htmlspecialchars;
use function in_array;
use function is_string;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function str_replace;
use function strtolower;
use function trim;

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;
use const PHP_URL_HOST;

/**
 * Allowlist-based HTML sanitizer for CKEditor 5 field values (mitigates stored XSS).
 *
 * When `$allowEmbeds` is true (config `html_sanitizer: allowlist`), a YouTube or Vimeo
 * iframe is kept only as `src` when the host is on the embed list. `html_sanitizer: strict`
 * sets `$allowEmbeds` to false and drops every iframe (legal pages and other public HTML).
 * Both modes strip scripts, event handlers (quoted, unquoted, or glued to the previous
 * attribute), `javascript:` / `data:` / `vbscript:` URLs, and `srcdoc`.
 */
final class AllowlistCkeditor5HtmlSanitizer implements Ckeditor5HtmlSanitizerInterface
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><s><del><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><code><pre><a><img><table><thead><tbody><tr><th><td><caption><hr><span><div><figure><figcaption><sub><sup><mark><iframe>';

    /** @var list<string> */
    private const ALLOWED_EMBED_HOSTS = [
        'www.youtube.com',
        'youtube.com',
        'www.youtube-nocookie.com',
        'player.vimeo.com',
    ];

    public function __construct(
        private readonly bool $allowEmbeds = true,
    ) {
    }

    public function sanitize(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/\son[a-z0-9_-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>"\']+)/i', '', $html) ?? $html;
        $html = preg_replace('/(["\'>])on[a-z0-9_-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>"\']+)/i', '$1', $html) ?? $html;
        $html = preg_replace(
            '/\s(?:href|src)\s*=\s*(?:"\s*(?:javascript|data|vbscript):[^"]*"|\'\s*(?:javascript|data|vbscript):[^\']*\'|(?:javascript|data|vbscript):[^\s>]*)/i',
            '',
            $html,
        ) ?? $html;
        $html = preg_replace('/\ssrcdoc\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;

        $allowed = self::ALLOWED_TAGS;
        if (!$this->allowEmbeds) {
            $html    = preg_replace('/<iframe\b[^>]*>.*?<\/iframe>/is', '', $html) ?? $html;
            $html    = preg_replace('/<iframe\b[^>]*\/?>/is', '', $html) ?? $html;
            $allowed = str_replace('<iframe>', '', $allowed);
        }

        $html = strip_tags($html, $allowed);

        if (!$this->allowEmbeds) {
            return $html;
        }

        return $this->stripUnsafeIframes($html);
    }

    private function stripUnsafeIframes(string $html): string
    {
        return preg_replace_callback(
            '/<iframe\b[^>]*>.*?<\/iframe>|<iframe\b[^>]*\/?>/is',
            function (array $matches): string {
                if (preg_match('/\ssrc=(["\'])([^"\']+)\1/i', $matches[0], $srcMatch) !== 1) {
                    return '';
                }

                $src  = trim($srcMatch[2]);
                $host = parse_url($src, PHP_URL_HOST);
                if (!is_string($host) || !in_array(strtolower($host), self::ALLOWED_EMBED_HOSTS, true)) {
                    return '';
                }

                return '<iframe src="' . htmlspecialchars($src, ENT_QUOTES | ENT_SUBSTITUTE) . '"></iframe>';
            },
            $html,
        ) ?? $html;
    }
}
