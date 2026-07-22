# CKEditor 5 Editor Bundle

[![CI](https://github.com/nowo-tech/Ckeditor5EditorBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/nowo-tech/Ckeditor5EditorBundle/actions/workflows/ci.yml) [![Packagist Version](https://img.shields.io/packagist/v/nowo-tech/ckeditor5-editor-bundle.svg?style=flat)](https://packagist.org/packages/nowo-tech/ckeditor5-editor-bundle) [![Packagist Downloads](https://img.shields.io/packagist/dt/nowo-tech/ckeditor5-editor-bundle.svg)](https://packagist.org/packages/nowo-tech/ckeditor5-editor-bundle) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE) [![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net) [![Symfony](https://img.shields.io/badge/Symfony-6.4%20%7C%207.4%2B%20%7C%208.0%20%7C%208.1%2B-000000?logo=symfony)](https://symfony.com) [![Coverage](https://img.shields.io/badge/Coverage-target%20100%25%20PHP-brightgreen)](#tests-and-coverage)

> **Found this useful?** [Install from Packagist](https://packagist.org/packages/nowo-tech/ckeditor5-editor-bundle) · Star the repo on [GitHub](https://github.com/nowo-tech/Ckeditor5EditorBundle).

Symfony bundle: **`Ckeditor5EditorType`** stores HTML in a textarea while **CKEditor 5 classic** (GPL open-source plugins only) runs in the browser. YAML profiles (FOS-style), **Vite** IIFE build (`ckeditor5-editor.js`) under `src/Resources/public/`.

**FrankenPHP:** Demo runtime is selected with **`FRANKENPHP_MODE`** (`worker` default, or `classic` for per-request PHP / hot-reload). See [docs/DEMO-FRANKENPHP.md](docs/DEMO-FRANKENPHP.md).

## Features

- Named YAML profiles (`toolbar`, `min_height`, `form_theme`, `preset`, `theme`, optional `upload_url`).
- Twig themes for common layouts (Bootstrap 3–5, Foundation, Tailwind 2, table layout).
- `nowo_ckeditor5_editor_asset_path()` and `nowo_ckeditor5_editor_asset_package()` Twig helpers for published assets (Symfony asset package).
- **pnpm + Vite** frontend; **Vitest** coverage on shared utilities (`logger.ts`).
- **Dockerfile + Makefile** aligned with other Nowo bundles.
- **Demos**: Symfony **8.1** FrankenPHP app under `demo/symfony8` (port **8021**).

## Requirements

| Symfony | PHP (minimum) | Notes |
| ------- | ------------- | ----- |
| **6.4**, **7.x** (incl. **7.4**) | **8.2+** | Tested in CI on **7.0** and **7.4** |
| **8.0**, **8.1** | **8.4+** | Symfony 8 requirement; tested in CI on **8.0** and **8.1** |

Composer constraints: `^6.4 \|\| ^7.0 \|\| ^8.0` on Symfony components. See [INSTALLATION.md](docs/INSTALLATION.md).

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
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}"></script>
```

## Documentation


- [GitHub Actions CI requirements](docs/GITHUB_CI.md)
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

- [Demo with FrankenPHP (development and production)](docs/DEMO-FRANKENPHP.md)

## Development

Requirements: Docker (recommended), or PHP 8.2+ with Composer + pnpm locally.

```bash
make up && make install   # Docker PHP + composer + pnpm
make assets               # vite → src/Resources/public/ckeditor5-editor.js
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

## Tests and coverage

| Layer | Target / notes |
| ----- | ---------------- |
| **PHP** | **100%** statement coverage on bundle `src/` (PHPUnit + Clover); enforced by `scripts/verify-clover-100.php`. Run `composer test-coverage` or `make test-coverage`. |
| **TypeScript** | Vitest thresholds on `src/Resources/assets/src/logger.ts` (see `vitest.config.ts`). Run `pnpm run test:coverage` or `make test-ts`. |

CI runs PHPUnit (matrix **PHP 8.2–8.5** × **Symfony 7.0 / 7.4 / 8.0 / 8.1**), PHPStan, PHP-CS-Fixer dry-run, and Vitest coverage on pushes and pull requests.

## License

MIT (bundle code). CKEditor 5 is used under its [GPL / LGPL / commercial terms](https://ckeditor.com/legal/ckeditor-licensing-options/) — this build uses OSS plugins suitable for GPL-compatible apps.
