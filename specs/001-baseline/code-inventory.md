# Code inventory — 100% traceability

**Baseline spec**: [`spec.md`](spec.md)  
**Package**: `nowo-tech/ckeditor5-editor-bundle`  
**Last audited**: 2026-10-20

## PHP classes

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `NowoCkeditor5EditorBundle.php` | Bundle entry | FR-BUNDLE-001 |
| `EditorTheme.php` | Editor theme enum | FR-PHP-001 |
| `EditorPreset.php` | Editor preset enum | FR-PRESET-001 |
| `DependencyInjection/Configuration.php` | Config tree | FR-CFG-001 |
| `DependencyInjection/NowoCkeditor5EditorExtension.php` | DI extension | FR-CFG-002 |
| `DependencyInjection/Compiler/TwigPathsPass.php` | Twig namespace path | FR-TWIG-001 |
| `Form/Ckeditor5EditorType.php` | Symfony form type | FR-FORM-001, FR-WORKER-001 |
| `Form/DataTransformer/Ckeditor5HtmlSanitizeTransformer.php` | Submit-time sanitizer | FR-SEC-001 |
| `Security/Ckeditor5HtmlSanitizerInterface.php` | Sanitizer contract | FR-SEC-001 |
| `Security/IdentityCkeditor5HtmlSanitizer.php` | No-op sanitizer (default) | FR-SEC-001 |
| `Security/AllowlistCkeditor5HtmlSanitizer.php` | Allowlist / strict sanitizer | FR-SEC-001, FR-WORKER-001 |
| `Twig/NowoCkeditor5EditorTwigExtension.php` | Asset helpers, script tag (eager / lazy), versioned URLs | FR-TWIG-002, FR-WORKER-001, FR-LAZY-002, FR-LAZY-003 |
| `Security/EmbedIframeSrcAttributeSanitizer.php` | Iframe `src` allowlist (internal, used by allowlist sanitizer) | FR-SEC-001 |

## TypeScript production (`src/Resources/assets/src/`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/assets/src/ckeditor5-editor.ts` | CKEditor 5 initializer | FR-UI-001 |
| `Resources/assets/src/logger.ts` | Client logger | FR-UI-002 |
| `Resources/assets/src/ckeditor5-editor-loader.ts` | Lazy loader (spec 002) | FR-LAZY-001 |
| `Resources/assets/src/ckeditor5-editor-widget.css` | Widget styles bundled into the IIFE (no inline `<style>`) | FR-UI-003 |

## TypeScript tests (co-located Vitest, `FR-TEST-*`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/assets/src/logger.test.ts` | Vitest: logger | FR-TEST-TS-001 |
| `Resources/assets/src/ckeditor5-editor-loader.test.ts` | Vitest: lazy loader | FR-LAZY-005 |

## Symfony config & build output

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/config/services.yaml` | Service wiring | FR-DI-001 |
| `Resources/public/ckeditor5-editor.js` | Vite build output | FR-BUILD-001 |
| `Resources/public/ckeditor5-editor-loader.js` | Vite build output (loader) | FR-BUILD-001, FR-LAZY-001 |
| `Resources/public/ckeditor5-editor.css` | Vite build output (CKEditor 5 + widget styles, CSP-safe stylesheet) | FR-BUILD-001, FR-UI-003 |
| `Resources/public/emoji/16/en.json` | Emoji dataset shipped locally (loaded by `ckeditor5-editor.js`, no CDN) | FR-BUILD-002 |

## Translations (`src/Resources/translations/`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/translations/NowoCkeditor5EditorBundle.de.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.en.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.es.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.fr.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.it.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.nl.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.pt.yaml` | UI strings | FR-I18N-001 |

## Twig form themes (`src/Resources/views/Form/`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/views/Form/ckeditor5_editor_theme.html.twig` | Default form theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap3.html.twig` | Bootstrap 3 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap3_horizontal.html.twig` | Bootstrap 3 horizontal | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap4.html.twig` | Bootstrap 4 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap4_horizontal.html.twig` | Bootstrap 4 horizontal | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap5.html.twig` | Bootstrap 5 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap5_horizontal.html.twig` | Bootstrap 5 horizontal | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_foundation5.html.twig` | Foundation 5 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_foundation6.html.twig` | Foundation 6 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_table.html.twig` | Table layout theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_tailwind2.html.twig` | Tailwind 2 theme | FR-TWIG-003 |

## Coverage summary

| Category | Files | Mapped |
| --- | ---: | ---: |
| PHP classes | 13 | 13 |
| TypeScript / CSS sources | 4 | 4 |
| TypeScript tests | 2 | 2 |
| Config & build outputs | 5 | 5 |
| Translations | 7 | 7 |
| Twig themes | 11 | 11 |
| **Total files under `src/`** | **42** | **42** |

Audit command: `git ls-files src | wc -l` → 42. Co-located Vitest files are included as `FR-TEST-*` / `FR-LAZY-005` rows; `Resources/public/*` files are build outputs of the named TypeScript/CSS sources (`FR-BUILD-*`), not orphan files.
