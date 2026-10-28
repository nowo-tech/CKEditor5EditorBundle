# CKEditor 5 Editor Bundle

[![CI](https://github.com/nowo-tech/CKEditor5EditorBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/nowo-tech/CKEditor5EditorBundle/actions/workflows/ci.yml) [![Packagist Version](https://img.shields.io/packagist/v/nowo-tech/ckeditor5-editor-bundle.svg?style=flat)](https://packagist.org/packages/nowo-tech/ckeditor5-editor-bundle) [![Packagist Downloads](https://img.shields.io/packagist/dt/nowo-tech/ckeditor5-editor-bundle.svg)](https://packagist.org/packages/nowo-tech/ckeditor5-editor-bundle) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE) [![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net) [![Symfony](https://img.shields.io/badge/Symfony-7.4%20%7C%208.0%20%7C%208.1%2B-000000?logo=symfony)](https://symfony.com) [![GitHub stars](https://img.shields.io/github/stars/nowo-tech/CKEditor5EditorBundle.svg?style=social&label=Star)](https://github.com/nowo-tech/CKEditor5EditorBundle) [![Coverage](https://img.shields.io/badge/Coverage-100%25-brightgreen)](#tests-and-coverage)

> **Found this useful?** [Install from Packagist](https://packagist.org/packages/nowo-tech/ckeditor5-editor-bundle) · Star the repo on [GitHub](https://github.com/nowo-tech/CKEditor5EditorBundle).

Symfony bundle: **`Ckeditor5EditorType`** stores HTML in a textarea while **CKEditor 5 classic** (GPL open-source plugins only) runs in the browser. YAML profiles (FOS-style), **Vite** IIFE build (`ckeditor5-editor.js`) under `src/Resources/public/`.
**FrankenPHP demos:** runtime is selected with **`FRANKENPHP_MODE`** (`worker` default, or `classic` for per-request PHP / hot-reload). See [docs/DEMO-FRANKENPHP.md](docs/DEMO-FRANKENPHP.md).

![FrankenPHP Friendly Worker Mode](docs/images/frankenphp-friendly.png)

This bundle is **FrankenPHP worker mode friendly** (including **`reset_kernel: false`** / no kernel reset between requests). See [FRANKENPHP-WORKER-AUDIT.md](docs/FRANKENPHP-WORKER-AUDIT.md).

## Features

- Named YAML profiles (`toolbar`, `min_height`, `form_theme`, `preset`, `theme`, optional `upload_url`).
- Twig themes for common layouts (Bootstrap 3–5, Foundation, Tailwind 2, table layout).
- `nowo_ckeditor5_editor_script()` Twig function: eager build or **lazy loader** (injects the 1 MB build only when an editor becomes visible — dialog opened, panel expanded, scrolled into view), editor CSS as a linked file (`ckeditor5-editor.css`, no injected `<style>` — strict CSP), CSP nonce, content-hash versioned URLs for long-lived caching.
- `nowo_ckeditor5_editor_asset_url()`, `nowo_ckeditor5_editor_asset_path()` and `nowo_ckeditor5_editor_asset_package()` Twig helpers for published assets (Symfony asset package).
- **pnpm + Vite** frontend; **Vitest** coverage on `logger.ts` and the lazy loader.
- **Dockerfile + Makefile** aligned with other Nowo bundles.
- **Demos**: Symfony **8.1** FrankenPHP app under `demo/symfony8` (port **8021**).

## Quick start

```bash
composer require nowo-tech/ckeditor5-editor-bundle:^1.0
php bin/console assets:install public
```

```yaml
# config/packages/nowo_ckeditor5_editor.yaml
nowo_ckeditor5_editor:
    default_profile: simple
    profiles:
        simple:
            preset: simple
            toolbar: true
            min_height: 240px
            form_theme: form_div_layout.html.twig
            debug: false
            theme: light
```

```php
use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;

$builder->add('body', Ckeditor5EditorType::class, [
    'label' => 'Article body',
]);
```

```twig
{{ nowo_ckeditor5_editor_script() }}
{# or, when the editor is not visible at first (modal, collapsed panel): #}
{{ nowo_ckeditor5_editor_script({lazy: true}) }}
```

## Requirements

| Symfony | PHP (minimum) | Notes |
| ------- | ------------- | ----- |
| **7.4** | **8.2+** | Tested in CI on **7.4** |
| **8.0**, **8.1** | **8.4+** | Symfony 8 requirement; tested in CI on **8.0** and **8.1** |

Composer constraints: `^7.4 \|\| ^8.0` on Symfony components. See [INSTALLATION.md](docs/INSTALLATION.md).

## Development

Requirements: Docker (recommended), or PHP 8.2+ with Composer + pnpm locally.

```bash
make up && make install   # Docker PHP + composer + pnpm
make assets               # vite → src/Resources/public/ckeditor5-editor.js + ckeditor5-editor-loader.js
make test                 # PHPUnit
make test-ts              # Vitest + coverage script
make qa                   # cs-check + phpunit
```

Demos:

```bash
make -C demo up-symfony8
# http://localhost:8021 (see demo/README.md and PORT in .env)
```

Presets include **`standard`**, **`simple`**, **`minimal`**, **`emoji`**, **`typography`**, **`variables`** — see [USAGE.md](docs/USAGE.md).

## Documentation

- [Installation](docs/INSTALLATION.md)
- [Configuration](docs/CONFIGURATION.md)
- [Usage](docs/USAGE.md)
- [Contributing](docs/CONTRIBUTING.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Changelog](docs/CHANGELOG.md)
- [Upgrading](docs/UPGRADING.md)
- [Release](docs/RELEASE.md)
- [Security](docs/SECURITY.md)
- [Engram](docs/ENGRAM.md)
- [Spec-driven development](docs/SPEC-DRIVEN-DEVELOPMENT.md)
- [GitHub Spec Kit](docs/SPEC-KIT.md)

### Additional documentation

- [PSR evaluation (REQ-CS-007)](docs/PSR.md)
- [Demo with FrankenPHP (development and production)](docs/DEMO-FRANKENPHP.md)
- [FrankenPHP worker audit (`reset_kernel: false`)](docs/FRANKENPHP-WORKER-AUDIT.md)
- [GitHub Actions CI requirements](docs/GITHUB_CI.md)

## Tests and coverage

- Tests: PHPUnit (unit + integration) and Vitest (`logger.ts`, lazy loader). Run `make test` / `composer test` and `make test-ts`.
- PHP: **100%**
- TS/JS: **100%** (Vitest on `src/Resources/assets/src/logger.ts` and `ckeditor5-editor-loader.ts`; see `vitest.config.ts`). The CKEditor build entry `ckeditor5-editor.ts` is excluded from the gate: it boots the real CKEditor 5 classic editor and is exercised through the demo instead (`make demo-smoke` and the `/demo/variants` pages).
- Python: N/A

CI runs PHPUnit (matrix **PHP 8.2–8.5** × **Symfony 7.4 / 8.0 / 8.1**), PHPStan, PHP-CS-Fixer dry-run, and Vitest coverage on pushes and pull requests.

## License

MIT (bundle code). CKEditor 5 is used under its [GPL / LGPL / commercial terms](https://ckeditor.com/legal/ckeditor-licensing-options/) — this build uses OSS plugins suitable for GPL-compatible apps.
