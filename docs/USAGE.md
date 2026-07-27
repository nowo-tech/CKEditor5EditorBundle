# Usage

## Contents

- [Form type](#form-type)
- [Frontend script](#frontend-script)
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

Include the compiled widget **once** per page (after your layout base):

```twig
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}"></script>
```

The script mounts CKEditor 5 on fields rendered by the bundle form theme.

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
