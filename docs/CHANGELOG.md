# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.2...HEAD
[1.0.2]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/nowo-tech/Ckeditor5EditorBundle/releases/tag/v1.0.0
