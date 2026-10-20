# Feature Specification: Ckeditor5EditorBundle baseline (100% code coverage)

**Feature Branch**: `001-baseline`  
**Created**: 2026-07-07  
**Status**: Active  

**Package**: `nowo-tech/ckeditor5-editor-bundle`  
**Configuration root**: `nowo_ckeditor5_editor`  
**Code inventory**: [`code-inventory.md`](code-inventory.md)

---

## Summary

Symfony form type and Twig themes that embed **CKEditor 5** with configurable presets, multi-framework form themes (Bootstrap 3–5, Foundation, Tailwind, table layout), and a TypeScript initializer built via Vite.

---

## User Scenarios

### US-01 — Rich text form field (P1)

**Given** a Symfony form uses `Ckeditor5EditorType`, **When** the page renders, **Then** CKEditor 5 loads with the configured preset and toolbar.

### US-02 — Theme compatibility (P2)

**Given** the application uses Bootstrap or Tailwind form themes, **When** integrator selects the matching bundle Twig theme, **Then** editor markup aligns with the parent form layout.

### US-03 — i18n (P2)

**Given** bundle translation files, **When** locale switches, **Then** editor UI strings follow Symfony translator domain `NowoCkeditor5EditorBundle`.

### US-04 — Server-side HTML sanitizer (P1)

**Given** `html_sanitizer` is `allowlist`, **When** a field is submitted, **Then** scripts, event handlers (quoted, unquoted, or glued), `javascript:` / `data:` / `vbscript:` URLs, and `srcdoc` are stripped, and a YouTube or Vimeo iframe is rewritten to `src` only.

**Given** `html_sanitizer` is `strict`, **When** a field is submitted, **Then** the same allowlist applies and every `iframe` is dropped.

**Given** `html_sanitizer` is `null` (default), **When** a field is submitted, **Then** the identity sanitizer leaves HTML unchanged (backward compatible).

### US-05 — FrankenPHP worker without kernel reset (P1)

**Given** the application runs under FrankenPHP worker with `reset_kernel: false` (shared container, no `services_resetter`), **When** two consecutive requests render `Ckeditor5EditorType` on the same service instance, **Then** upload CSRF tokens and form vars do not leak from the first request to the second, and built-in sanitizers remain stateless.

---

## Requirements

- **FR-BUNDLE-001**: `NowoCkeditor5EditorBundle` + alias `nowo_ckeditor5_editor`.
- **FR-CFG-001 / FR-CFG-002**: Editor defaults, presets, and DI wiring.
- **FR-FORM-001**: `Ckeditor5EditorType` exposes options documented in [`docs/USAGE.md`](../../docs/USAGE.md).
- **FR-SEC-001**: `Ckeditor5HtmlSanitizerInterface` plus identity, allowlist, and `html_sanitizer: strict` (allowlist with embeds disabled). Submit path uses `Ckeditor5HtmlSanitizeTransformer`.
- **FR-WORKER-001**: Bundle services are safe under FrankenPHP worker scenario B (`reset_kernel: false`); documented in [`docs/FRANKENPHP-WORKER-AUDIT.md`](../../docs/FRANKENPHP-WORKER-AUDIT.md). PHPStan includes `ruleset-classic` + `ruleset-worker-strict` (worker rules + request-superglobal flags).
- **FR-TWIG-001–003**: Twig path pass, extension, and form theme variants.
- **FR-UI-001 / FR-UI-002**: TypeScript initializer and logger under `Resources/assets/src/`.
- **FR-UI-003**: Widget styles (`ckeditor5-editor-widget.css`: min-height, dark theme tokens) are bundled by Vite and injected once per page instead of an inline `<style>` per widget (CSP-safe).
- **FR-BUILD-001**: `ckeditor5-editor.js` / `ckeditor5-editor-loader.js` / `ckeditor5-editor.css` build outputs consumed by form themes.
- **FR-BUILD-002**: Emoji dataset `Resources/public/emoji/16/en.json` shipped locally and resolved next to the build (no CDN fetch).
- **FR-I18N-001**: Seven locale YAML files with key parity.
- **FR-TEST-TS-001**: Vitest coverage for client logger and lazy loader.
- **FR-LAZY-001–005**: Lazy loading of the editor build — see [`../002-lazy-loader/spec.md`](../002-lazy-loader/spec.md).

---

## Success Criteria

- **SC-001**: **42/42** files under `src/` mapped in [`code-inventory.md`](code-inventory.md) (13 PHP, 4 TS/CSS sources, 2 co-located Vitest files, 5 config/build outputs, 7 translations, 11 Twig themes; audited 2026-10-20).
- **SC-002**: Config keys match `Configuration.php` and [`docs/CONFIGURATION.md`](../../docs/CONFIGURATION.md), including `html_sanitizer` (`null` / `allowlist` / `strict` / custom service id) and `lazy_load`.
- **SC-003**: `composer qa` passes; PHPUnit line coverage on `src/` is **100%**; Vitest runs when TS changes.

## Non-goals

- Premium / commercial CKEditor 5 plugins (only GPL open-source plugins are bundled).
- Server-side storage of uploads or images: hosts own upload endpoints (auth, CSRF, validation, storage); the demo upload is illustrative.
- Sanitizing HTML when `html_sanitizer` is `null` (identity, backward compatible) — hosts with user-generated content must opt into `allowlist` / `strict` or a custom service.
- `demo/` trees are illustrative and not part of the Packagist API.

## Assumptions

- The host renders the field through one of the shipped form themes and serves `Resources/public/` via `assets:install`.
- The browser loads the Vite IIFE build; no host bundler is required.

---

## Validation

`composer qa` (PHP-CS-Fixer, PHPStan, PHPUnit with 100% line coverage on `src/`), `pnpm test` (Vitest, when TypeScript changes), `make release-check`. Inventory check: `git ls-files src | wc -l` must equal the inventory total (42).
