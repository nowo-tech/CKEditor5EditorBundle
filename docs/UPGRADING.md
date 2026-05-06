# Upgrade guide

## General

- Follow [`CHANGELOG.md`](CHANGELOG.md) for each release.
- Pin versions in `composer.json` (e.g. `^1.0`) instead of relying only on `dev-main` for production apps.
- After upgrading, run `php bin/console cache:clear` and `php bin/console assets:install public` so Twig and published bundle assets stay in sync.

## To 1.x (first documented stable line)

When upgrading from snapshots without semver tags in your project:

- **Composer**: `composer require nowo-tech/ckeditor5-editor-bundle:^1.0`
- **Configuration**: prefer explicit **`configs`** + **`default_config`** (see [`CONFIGURATION.md`](CONFIGURATION.md)). Legacy flat YAML under `nowo_ckeditor5_editor` is still accepted and normalized.
- **Bootstrap**: ensure your layout loads the bundle script once per page:

  ```twig
  <script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js')) }}"></script>
  ```
