# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-07-13

### Added

- Symfony asset package **`nowo_ckeditor5_editor`** (auto-registered via `framework.assets.packages`) for correct URLs with [AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html) and multi-package apps.
- Twig helper **`nowo_ckeditor5_editor_asset_package()`**.

### Changed

- **`nowo_ckeditor5_editor_asset_path()`** returns a relative filename (e.g. `ckeditor5-editor.js`) for use with `asset(..., nowo_ckeditor5_editor_asset_package())` instead of a hard-coded `bundles/nowockeditor5editor/...` path.
- Documentation, README, and demos: bootstrap `<script>` tag updated to the two-argument `asset()` form.
- [`INSTALLATION.md`](INSTALLATION.md): new **AssetMapper** subsection.

## [1.0.3] - 2026-06-11

### Changed

- Documentation: explicit Symfony compatibility (**6.4**, **7.4+**, **8.0**, **8.1+**) and PHP minimums in README, [`INSTALLATION.md`](INSTALLATION.md), [`CONTRIBUTING.md`](CONTRIBUTING.md), [`UPGRADING.md`](UPGRADING.md), [`DEMO-FRANKENPHP.md`](DEMO-FRANKENPHP.md), and demo READMEs; fixed demo `symfony7` / `symfony8` READMEs (were copied from another bundle).
- CI: PHPUnit matrix adds **Symfony 7.4** and **8.1**; Composer `platform.php` override applies to all **Symfony 8.x** jobs (`8.*` pattern).
- Demos: **`demo/symfony7`** pins **Symfony 7.4.***; **`demo/symfony8`** pins **Symfony 8.1.*** (incl. `symfony/translation`); lock files and Symfony Flex scaffolding refreshed.
- Makefiles: **`REQ-MAKE-008`** `update-deps` targets (bundle root + demo aggregate + per-demo).
- Added [`SPEC-DRIVEN-DEVELOPMENT.md`](SPEC-DRIVEN-DEVELOPMENT.md); cross-link from [`ENGRAM.md`](ENGRAM.md) and README.

### Fixed

- `demo/symfony8/Makefile`: missing closing parenthesis in `update-deps` include.

## [1.0.2] - 2026-05-06

### Fixed

- CI: PHPUnit matrix for **Symfony 8.0** overrides Composer `platform.php` to match the job PHP version so dependency resolution satisfies Symfony 8’s **PHP ≥8.4** requirement while the repository keeps a **8.2.x** platform pin for `composer install` / PHP **8.2** jobs.
- PHPStan: `tests/Fixtures/app/var (?)` optional exclude so `composer phpstan` succeeds when the integration fixture cache directory does not exist yet (e.g. fresh CI checkout).

## [1.0.1] - 2026-05-06

### Fixed

- Composer: set `config.platform.php` to `8.2.30` and regenerate `composer.lock` (Symfony **7.4.x**) so `composer install` works on PHP **8.2** CI jobs; Symfony **8** needs PHP **≥8.4** (matrix jobs still upgrade to Symfony 8 on 8.4+).

## [1.0.0] - 2026-05-06

First semver release (documented stable line, CI and Packagist aligned with tag `v1.0.0`).

### Added

- Full Nowo bundle documentation (`docs/`), GitHub issue/PR templates, `CODEOWNERS`, `.github/SECURITY.md`, release workflows (`release.yml`, `sync-releases.yml`).
- Cursor baseline (`.cursor/mcp.json`, `.cursorignore`, `.cursor/rules`) and [ENGRAM.md](ENGRAM.md).
- Integration tests booting a minimal Symfony kernel (`tests/Integration/`, `tests/Fixtures/app/`).
- [DEMO-FRANKENPHP.md](DEMO-FRANKENPHP.md) for FrankenPHP demos and worker vs development behaviour.
- Canonical README (badges, **Found this useful?**, **Tests and coverage**, FrankenPHP worker note).
- PHPUnit: integration suite + unit test for `upload_url` CSRF branch (**100%** PHP Clover on `src/`).
- PHP-CS-Fixer finder excludes generated integration fixture cache and `tests/Fixtures/app/config/reference.php`.

[Unreleased]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.3...v1.1.0
[1.0.3]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/nowo-tech/Ckeditor5EditorBundle/releases/tag/v1.0.0
