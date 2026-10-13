<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Security;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

use function in_array;
use function is_string;
use function parse_url;
use function str_starts_with;
use function strtolower;
use function trim;

use const PHP_URL_HOST;

/**
 * Keeps an iframe `src` only when it is an absolute `https` URL on an allowlisted embed host
 * (relative, protocol-relative and any other scheme are rejected).
 *
 * @internal used by {@see AllowlistCkeditor5HtmlSanitizer}
 */
final readonly class EmbedIframeSrcAttributeSanitizer implements AttributeSanitizerInterface
{
    /**
     * @param list<string> $allowedHosts lower-case host names
     */
    public function __construct(
        private array $allowedHosts,
    ) {
    }

    public function getSupportedElements(): array
    {
        return ['iframe'];
    }

    public function getSupportedAttributes(): array
    {
        return ['src'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        $src = trim($value);
        if (!str_starts_with(strtolower($src), 'https://')) {
            return null;
        }

        $host = parse_url($src, PHP_URL_HOST);
        if (!is_string($host) || !in_array(strtolower($host), $this->allowedHosts, true)) {
            return null;
        }

        return $src;
    }
}
