# Installation

## Requirements

| Symfony | PHP (minimum) | Composer constraint |
| ------- | ------------- | ------------------- |
| **6.4**, **7.x** (incl. **7.4**) | **8.2+** | `^6.4 \|\| ^7.0` on Symfony components |
| **8.0**, **8.1** | **8.4+** | `^8.0` on Symfony components |

The bundle declares `^6.4 \|\| ^7.0 \|\| ^8.0` on required Symfony packages (including **`symfony/asset`** for the `nowo_ckeditor5_editor` asset package). Symfony **8** needs PHP **≥8.4** (upstream requirement). CI verifies **7.0**, **7.4**, **8.0**, and **8.1** — see [`.github/workflows/ci.yml`](../.github/workflows/ci.yml).

```bash
composer require nowo-tech/ckeditor5-editor-bundle:^1.0
```

Symfony Flex registers the bundle when available. The Flex recipe lives under [`.symfony/recipe/nowo-tech/ckeditor5-editor-bundle`](../.symfony/recipe/nowo-tech/ckeditor5-editor-bundle) in this repository (copies default package config). If you install without Flex or registration fails:

```php
// config/bundles.php
return [
    // ...
    Nowo\Ckeditor5EditorBundle\NowoCkeditor5EditorBundle::class => ['all' => true],
];
```

Create configuration (recommended — named profiles):

```yaml
# config/packages/nowo_ckeditor5_editor.yaml
nowo_ckeditor5_editor:
    default_config: simple
    configs:
        simple:
            preset: simple
            toolbar: true
            min_height: 240px
            form_theme: form_div_layout.html.twig
            debug: false
            theme: light
```

You may use **legacy flat** keys at the root (without `configs`): they are normalized into `configs.default`. Prefer explicit `configs` for multiple profiles.

See [CONFIGURATION.md](CONFIGURATION.md) for the full reference.

Install static assets into your `public/` tree:

```bash
php bin/console assets:install public
```

In your base layout, load the bundle script **once** per page (see [USAGE.md](USAGE.md)):

```twig
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}"></script>
```

When developing the bundle from a git clone, rebuild the JS with `pnpm run build` in the bundle root, then re-run `assets:install` in the app.

### AssetMapper

If your app uses [Symfony AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html), the bundle registers the `nowo_ckeditor5_editor` asset package. Run `assets:install` once so `ckeditor5-editor.js` is published to `public/bundles/nowockeditor5editor/`, or copy the built file into your own asset pipeline. The layout loads it via `asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package())`.

Persisted HTML may require sanitization — see [SECURITY.md](SECURITY.md). To override Twig themes or translations, see [CONFIGURATION.md](CONFIGURATION.md#overriding-bundle-twig-templates).
