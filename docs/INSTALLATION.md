# Installation

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
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js')) }}"></script>
```

When developing the bundle from a git clone, rebuild the JS with `pnpm run build` in the bundle root, then re-run `assets:install` in the app.

Persisted HTML may require sanitization — see [SECURITY.md](SECURITY.md). To override Twig themes or translations, see [CONFIGURATION.md](CONFIGURATION.md#overriding-bundle-twig-templates).
