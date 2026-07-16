# Upgrade guide

## General

**Supported platforms:** Symfony **6.4**, **7.x** (incl. **7.4**) on PHP **8.2+**; Symfony **8.0** and **8.1** on PHP **8.4+**. See [`INSTALLATION.md`](INSTALLATION.md#requirements).

- Follow [`CHANGELOG.md`](CHANGELOG.md) for each release.
- Pin versions in `composer.json` (e.g. `^1.0`) instead of relying only on `dev-main` for production apps.
- After upgrading, run `php bin/console cache:clear` and `php bin/console assets:install public` so Twig and published bundle assets stay in sync.

## To 1.0.0 (first Git / Packagist semver tag)

`v1.0.0` is the first annotated release tag. If you were tracking `dev-main` or a commit hash, switch to a semver constraint so upgrades are predictable:

- **Composer**: `composer require nowo-tech/ckeditor5-editor-bundle:^1.0`

No bundle API breaks are introduced solely by tagging; see [`CHANGELOG.md`](CHANGELOG.md) for the full 1.0.0 notes.

## To 1.0.1 from 1.0.0

Patch release: CI and contributor installs on **PHP 8.2** using the repository lock file. No bundle API or YAML changes.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` if you pin `1.0.0` exactly; with `^1.0` you get 1.0.1 automatically on update.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.0.1**) for details.

## To 1.0.2 from 1.0.1

Patch release: CI (Symfony **8** matrix + Composer platform) and **PHPStan** config only. No bundle API, YAML, or runtime behaviour changes for applications.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.0.1`); with `^1.0`, **1.0.2** is included on update.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.0.2**) for details.

## To 1.0.3 from 1.0.2

Patch release: documentation, CI matrix (**Symfony 7.4** / **8.1**), demo pins, and Makefile `update-deps` targets only. No bundle API, YAML, or runtime behaviour changes for applications.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.0.2`); with `^1.0`, **1.0.3** is included on update.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.0.3**) for details.

## To 1.1.0 from 1.0.3

Minor release: **Symfony asset package** registration and Twig helper changes. **Update your layout** if you load the editor script with the old one-argument `asset()` call.

**Before (≤ 1.0.3):**

```twig
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js')) }}"></script>
```

**After (1.1.0+):**

```twig
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}"></script>
```

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` (with `^1.0` or `^1.1`); then `php bin/console cache:clear` and `php bin/console assets:install public`.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.1.0**) for details.

## To 1.1.1 from 1.1.0

Patch release: repository demo cleanup and docs only. No bundle API, YAML, or runtime behaviour changes for applications.

- **Contributors / local demos**: **`demo/symfony7`** was removed. Use **`demo/symfony8`** (default port **8021**). Symfony **7.x** remains covered by the CI PHPUnit matrix.
- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.1.0`); with `^1.0` or `^1.1`, **1.1.1** is included on update.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.1.1**) and [`DEMO-FRANKENPHP.md`](DEMO-FRANKENPHP.md).

## To 1.x (first documented stable line)

When upgrading from snapshots without semver tags in your project:

- **Composer**: `composer require nowo-tech/ckeditor5-editor-bundle:^1.0`
- **Configuration**: prefer explicit **`configs`** + **`default_config`** (see [`CONFIGURATION.md`](CONFIGURATION.md)). Legacy flat YAML under `nowo_ckeditor5_editor` is still accepted and normalized.
- **Bootstrap**: ensure your layout loads the bundle script once per page:

  ```twig
  <script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}"></script>
  ```
