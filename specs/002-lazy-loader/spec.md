# Feature Specification: Lazy loading of the CKEditor build

**Feature Branch**: `002-lazy-loader`  
**Created**: 2026-10-13  
**Status**: Implemented (1.5.0)  

**Package**: `nowo-tech/ckeditor5-editor-bundle`  
**Configuration root**: `nowo_ckeditor5_editor`  
**Origin**: Symfony Beacon spec 134 (kit upstream improvements), FR-001 — dashboard pages pay 1.2 MB per view for an editor that lives in a closed `<dialog>`.

---

## Summary

Ship a small loader script that injects the full editor build (`ckeditor5-editor.js`, ~1.2 MB) only when an editor root is present **and** visible, expose it through a Twig function, and version asset URLs so hosts can cache `/bundles/*` long-term. Eager loading stays the default (backwards compatible, minor release).

---

## User Scenarios

### US-01 — Editor in a closed modal (P1)

**Given** a page renders `{{ nowo_ckeditor5_editor_script({lazy: true}) }}` and the only editor sits in a closed `<dialog>`, **When** the page loads, **Then** only `ckeditor5-editor-loader.js` is downloaded. **When** the dialog opens, **Then** the full build is injected once and the editor mounts.

### US-02 — Visible editor (P1)

**Given** the lazy loader and an editor visible near the viewport, **When** the page loads, **Then** the build is injected immediately and the editor mounts (same result as eager loading).

### US-03 — Late roots (P2)

**Given** the lazy loader and no editor at load, **When** an editor root is inserted later (Turbo, AJAX, modal body) and is visible, **Then** the build is injected; roots added after the build has loaded are mounted by the build's own `MutationObserver`.

### US-04 — Programmatic preload (P2)

**Given** the lazy loader, **When** host JavaScript calls `window.NowoCkeditor5EditorLoader.load()`, **Then** the build is injected (once) and the returned promise resolves when it has loaded.

### US-05 — Cache-safe URLs (P2)

**Given** `nowo_ckeditor5_editor_script()` or `nowo_ckeditor5_editor_asset_url()`, **When** the build changes between releases, **Then** the URL changes (`?v=<content hash>`), so `/bundles/*` can be served with an immutable `Cache-Control`.

### US-06 — Existing apps (P1)

**Given** an app keeps `asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package())`, **When** it upgrades to 1.5.0, **Then** nothing changes.

---

## Requirements

- **FR-LAZY-001 (loader)**: `ckeditor5-editor-loader.js` (separate Vite IIFE entry `vite.loader.config.ts`, a few KB) watches `[data-ckeditor5-root="1"]` (same selector as the build). It injects the build URL read from `data-ckeditor5-src` on its own `<script>` tag exactly once, when a root is rendered (not `hidden`, not in a closed `<dialog>` / collapsed `<details>`, no `display: none` / `content-visibility: hidden` ancestor, not `visibility: hidden`) **and** intersects the viewport (`IntersectionObserver`, 200px margin). A `MutationObserver` (childList + `open` / `hidden` / `class` / `style` attributes) tracks new / removed roots and re-checks visibility (the only signal when `IntersectionObserver` is unavailable). The loader copies its `nonce` onto the injected script, skips injection when the build is already present, and disconnects its observers after injecting. Global API: `window.NowoCkeditor5EditorLoader.{load, refresh, isLoaded, disconnect}`; `load()` is idempotent and returns a promise.
- **FR-LAZY-002 (Twig script)**: `nowo_ckeditor5_editor_script(options = null, nonce = null)` renders `<script src="…ckeditor5-editor.js?v=…" defer>` (eager) or `<script src="…ckeditor5-editor-loader.js?v=…" defer data-ckeditor5-src="…ckeditor5-editor.js?v=…">` (lazy). `options` is a bool (lazy) or a hash `{lazy, nonce}`; the nonce is HTML-escaped and optional.
- **FR-LAZY-003 (versioned URLs)**: `nowo_ckeditor5_editor_asset_url(filename = 'ckeditor5-editor.js')` returns the `nowo_ckeditor5_editor` asset-package URL (fallback `/bundles/nowockeditor5editor/<file>` without the Asset service) plus `v=<first 12 hex chars of xxh3(file)>`, appended with `&` when the URL already has a query. Stateless (worker-safe).
- **FR-LAZY-004 (config)**: `nowo_ckeditor5_editor.lazy_load` (bool, default `false`) is the default of the `lazy` option; parameter `nowo_ckeditor5_editor.lazy_load`. Accepted in flat and `profiles` layouts.
- **FR-LAZY-005 (tests)**: PHPUnit keeps 100% statement coverage (Twig function, config, integration render through the asset package). Vitest keeps 100% coverage on the loader: visible root, root in a closed dialog then opened, single injection, late-added root, `load()` escape hatch, nonce, failed load, missing URL, boot before / after `DOMContentLoaded`.
- **FR-LAZY-006 (docs & demo)**: CHANGELOG, UPGRADING, USAGE, CONFIGURATION, INSTALLATION, SECURITY, README; demo page *Lazy loading* with the editor inside a closed `<dialog>`.

---

## Success Criteria

- **SC-001**: A page whose only editor is in a closed `<dialog>` downloads ~2.6 kB instead of ~1.2 MB until the dialog opens.
- **SC-002**: The build is injected at most once per page.
- **SC-003**: No change for apps that do not opt in.
- **SC-004**: `make release-check` gates pass (PHP 100% coverage, Vitest 100% thresholds, PHPStan, CS, Rector, Igor).

---

## Validation

`make test-coverage`, `make test-ts`, `make phpstan`, `make cs-check`, `make rector-dry`, `make igor`, `pnpm run build`.
