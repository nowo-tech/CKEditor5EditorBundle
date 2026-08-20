# Security Policy

## Table of contents

- [Security considerations for integrators](#security-considerations-for-integrators)
- [Bundle responsibility](#bundle-responsibility)
- [AI security audit](#ai-security-audit)
- [Supported versions](#supported-versions)
- [Reporting a vulnerability](#reporting-a-vulnerability)
- [Release security checklist (12.4.1)](#release-security-checklist-1241)

## Security considerations for integrators

- **HTML and XSS**: This bundle exposes a form field with **HTML** produced by CKEditor 5. When `nowo_ckeditor5_editor.html_sanitizer` is set (Flex recipe enables `allowlist` in `when@prod`), submitted HTML is sanitized server-side via `Ckeditor5HtmlSanitizeTransformer`. With `html_sanitizer: null` (default in dev/test), sanitization is disabled for BC — **your application** must still sanitize or allowlist content before persisting or rendering UGC.
- **HTML sanitizer services**: The bundle registers `Ckeditor5HtmlSanitizerInterface` (aliased by default to `IdentityCkeditor5HtmlSanitizer`, a no-op). Enable built-in allowlist sanitization:

  ```yaml
  # config/packages/prod/nowo_ckeditor5_editor.yaml (also shipped in Flex recipe when@prod)
  when@prod:
      nowo_ckeditor5_editor:
          html_sanitizer: allowlist
  ```

  Or re-alias the interface / inject into custom listeners:

  ```yaml
  # config/services.yaml (example)
  App\Listener\SanitizeEditorHtmlListener:
      arguments:
          $htmlSanitizer: '@Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer'
  ```

  Or re-alias the interface in your app:

  ```yaml
  Nowo\Ckeditor5EditorBundle\Security\Ckeditor5HtmlSanitizerInterface:
      alias: Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer
  ```

  Client-side filtering alone is not sufficient for UGC.
- **Script tags**: The widget loads `ckeditor5-editor.js` from published bundle assets. Use `assets:install` / AssetMapper hygiene and trusted builds only.
- **CSP**: Prefer loading the published IIFE via `asset(...)` (no inline scripts). The widget uses a long-lived `MutationObserver` for Turbo/AJAX remounts; it does not use `eval`, `document.write`, or `innerHTML` with unsanitized HTML.
- **Upload endpoints**: If you configure `upload_url`, your endpoint must validate MIME types (prefer magic-byte checks), size limits, and authentication/authorization; the demos are examples only — do not copy them to production unchanged.
- **CSRF**: Upload flows may use CSRF tokens (`Ckeditor5EditorType::CSRF_UPLOAD_TOKEN_ID`) — ensure your routes validate them consistently.

## Bundle responsibility

The bundle provides a Symfony form type, Twig themes, translations, and a static JS bundle. It does not execute persisted HTML on the server beyond normal form handling. Twig form themes escape dataset attributes with `|e('html_attr')`.

## AI security audit

| Field | Value |
| --- | --- |
| Date | 2026-08-20 (re-audit; prior 2026-07-27) |
| Method | Cursor security-review (`src/`, Twig, assets, SECURITY docs, demo, Flex recipe `when@prod` allowlist) |
| Grade | **Pass (good)** — overall **Low** |
| Open residuals | Host must not disable prod recipe allowlist for UGC; production upload endpoints must enforce auth, CSRF, content validation, and safe storage; demo upload is not production-ready |

See also the monorepo record in [`BUNDLES_SECURITY_ANALYSIS.md`](https://github.com/nowo-tech/bundles/blob/master/BUNDLES_SECURITY_ANALYSIS.md) (Ckeditor5EditorBundle entry).

## Supported versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a vulnerability

If you discover a security vulnerability in this project, please report it responsibly:

1. **Do not** open a public GitHub issue for security-sensitive bugs.
2. Send details to **[hectorfranco@nowo.tech](mailto:hectorfranco@nowo.tech)** (or the maintainers listed in [`composer.json`](../composer.json)).
3. Include a clear description, steps to reproduce, and impact if possible.
4. We will acknowledge receipt and work on a fix. We may ask for more information.
5. After a fix is released, we can coordinate on disclosure.

We appreciate responsible disclosure so users can update before details are public.

## Release security checklist (12.4.1)

Before tagging a release, confirm:

| Item | Notes |
|------|--------|
| **SECURITY.md** | This document is current and linked from the README where applicable. |
| **`.gitignore` and `.env`** | `.env` and local env files are ignored; no committed secrets. |
| **No secrets in repo** | No API keys, passwords, or tokens in tracked files. |
| **HTML / XSS** | Sanitizer interface + Allowlist/Identity services documented; apps must sanitize on persist. |
| **Input / output** | Form options validated; user HTML is not executed server-side by the bundle. |
| **Dependencies** | `composer audit` run; issues triaged. |
| **Logging** | Logs do not print secrets or session identifiers unnecessarily. |
| **Assets** | Built `ckeditor5-editor.js` is reproducible from source (`pnpm run build`). |
| **AI security audit** | REQ-SEC-004 Pass (good/conditional) recorded (this document + monorepo analysis). |

Record confirmation in the release PR or tag notes.
