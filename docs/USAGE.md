# Usage

## Contents

- [Form type](#form-type)
- [Frontend script](#frontend-script)
  - [Lazy loading (1.5.0+)](#lazy-loading-150)
  - [Versioned URLs and caching](#versioned-urls-and-caching)
- [Presets](#presets)
- [Uploads (optional)](#uploads-optional)
- [Overriding Twig templates (REQ-TWIG-001)](#overriding-twig-templates-req-twig-001)
- [Translation overrides](#translation-overrides)

## Form type

```php
use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;

$builder->add('body', Ckeditor5EditorType::class, [
    'label' => 'Content',
    'config' => 'simple',
    'editor_config' => [
        'preset' => 'standard',
        'min_height' => 'min(40vh, 560px)',
    ],
]);
```

Submitted data is an **HTML string** (store in `TEXT` / `LONGTEXT` / similar). **Sanitize** before persist and before render — see [SECURITY.md](SECURITY.md).

## Frontend script

Include the compiled widget **once** per page (after your layout base). Since **1.5.0** the simplest way is the Twig function:

```twig
{{ nowo_ckeditor5_editor_script() }}
{# → <script src="/bundles/nowockeditor5editor/ckeditor5-editor.js?v=0123456789ab" defer></script> #}
```

The manual tag still works (no version query):

```twig
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}" defer></script>
```

The script mounts CKEditor 5 on fields rendered by the bundle form theme.

`nowo_ckeditor5_editor_script(options = null, nonce = null)`:

| Argument / option | Type | Default | Description |
| ----------------- | ---- | ------- | ----------- |
| `true` / `false` or `{lazy: bool}` | bool | config `lazy_load` (`false`) | `false`: full build (~1.2 MB). `true`: lazy loader (a few KB) — see below. |
| `{nonce: '…'}` or `nonce=` | string\|null | `null` | CSP nonce added to the tag. With the lazy loader the nonce is copied onto the injected build script. |

With a CSP nonce helper (for example NelmioSecurityBundle `csp_nonce('script')`, or a host `csp_nonce()`):

```twig
{{ nowo_ckeditor5_editor_script({lazy: true, nonce: csp_nonce('script')}) }}
```

### Lazy loading (1.5.0+)

Pages where the editor is not visible at first (inside a closed `<dialog>`, a collapsed `<details>` / tab / accordion, below the fold) do not need to download the 1.2 MB build on every view:

```twig
{{ nowo_ckeditor5_editor_script({lazy: true}) }}
{# → <script src="/bundles/nowockeditor5editor/ckeditor5-editor-loader.js?v=…" defer
           data-ckeditor5-src="/bundles/nowockeditor5editor/ckeditor5-editor.js?v=…"></script> #}
```

The loader (`ckeditor5-editor-loader.js`) watches `[data-ckeditor5-root="1"]` widgets and injects the full build **once**, the first time a widget is rendered and near the viewport:

- visible when the page loads (and within 200px of the viewport);
- revealed later: `<dialog>` opened (`open` attribute / `showModal()`), `<details>` expanded, `hidden` removed, `display: none` class/style toggled off, scrolled into view (`IntersectionObserver`);
- inserted into the DOM later (Turbo, AJAX, modal bodies rendered on demand).

Once the build has loaded it boots as usual (`runInit()` + `MutationObserver`), so every widget on the page — including ones still hidden or added afterwards — is mounted. Without `IntersectionObserver` the loader falls back to the visibility check on DOM/attribute mutations.

Escape hatch (for example right before opening a modal from JavaScript, to start the download early):

```js
await window.NowoCkeditor5EditorLoader.load(); // idempotent; resolves when the build has loaded
```

`window.NowoCkeditor5EditorLoader` also exposes `refresh()` (re-scan now), `isLoaded()` and `disconnect()`.

To make lazy loading the default for every `nowo_ckeditor5_editor_script()` call, set `nowo_ckeditor5_editor.lazy_load: true` ([CONFIGURATION.md](CONFIGURATION.md#top-level)); pass `{lazy: false}` where the editor must be ready immediately.

### Versioned URLs and caching

`nowo_ckeditor5_editor_script()` and `nowo_ckeditor5_editor_asset_url(filename = 'ckeditor5-editor.js')` append `?v=<content hash>` (xxh3 of the shipped file, 12 hex chars) to the asset-package URL. The URL changes whenever the build changes, so you can serve `/bundles/*` with a long-lived `Cache-Control` (for example `public, max-age=31536000, immutable`).


## Dynamic mounts / MutationObserver

The published `ckeditor5-editor.js` boots with `runInit()` for existing `[data-ckeditor5-root]` widgets and, by default, a `MutationObserver` (`runInitAndObserve`) for Turbo/AJAX inserts.

- Prefer marking Symfony-rendered widgets with `data-ckeditor5-root="1"` (form theme) and letting auto-init run.
- When the host builds the widget DOM and calls `NowoCkeditor5Editor.initCkeditor5Root(root)` itself, either omit `data-ckeditor5-root` **or** set `data-ckeditor5-auto-observe="0"` on `<html>`/`<body>` so the observer does not race a second `ClassicEditor.create` on the same mount. As of **1.4.11**, `initCkeditor5Root` also uses a synchronous mounting lock as a safety net.


## Presets

YAML **`preset`** selects which OSS CKEditor build variant is used (`standard`, `simple`, `minimal`, `emoji`, `typography`, `variables`, …). See the FrankenPHP demo under `demo/symfony8` for live examples (themes, heights, upload URL).

## Uploads (optional)

If `upload_url` is set in the profile or merged via `editor_config`, the widget may send multipart uploads with CSRF (`Ckeditor5EditorType::CSRF_UPLOAD_TOKEN_ID`). Your application must expose a compatible endpoint (see demo controllers for reference).

## Overriding Twig templates (REQ-TWIG-001)

Application templates under `templates/bundles/NowoCkeditor5EditorBundle/` **always win** over the copies inside the package (`TwigPathsPass` registers the `@NowoCkeditor5EditorBundle` namespace so app overrides are resolved first).

**Procedure**

1. Pick the `<subpath>` from the [overridable templates table](CONFIGURATION.md#overridable-templates) (path relative to `src/Resources/views/`).
2. Create `templates/bundles/NowoCkeditor5EditorBundle/<subpath>` in your application (same relative path and filename).
3. Clear cache if needed: `php bin/console cache:clear`.

Example:

```text
templates/bundles/NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme.html.twig
```

Full procedure, logical names (`@NowoCkeditor5EditorBundle/...`), and the complete subpath list: [CONFIGURATION.md — Twig overrides](CONFIGURATION.md#twig-overrides).

## Translation overrides

Translations use the domain **`NowoCkeditor5EditorBundle`**. Override from your app with files under `translations/` using the same domain (e.g. `translations/NowoCkeditor5EditorBundle.en.yaml`). See [CONFIGURATION.md — Translation overrides](CONFIGURATION.md#translation-overrides).
