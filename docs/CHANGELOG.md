# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/nowo-tech/Ckeditor5EditorBundle/releases/tag/v1.0.0
