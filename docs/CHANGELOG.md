# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `make check-open-prs` (REQ-REL-003) wired into `release-check` via `.scripts/check-open-prs.sh`.
- `make demo-smoke` / `make -C demo demo-smoke` (REQ-TEST-011) — alias of demo `release-verify` (boot + HTTP 200).
- [`USAGE.md`](USAGE.md): Twig template override procedure and link to the overridable templates table (REQ-TWIG-001).
- [`SECURITY.md`](SECURITY.md): AI security audit record (REQ-SEC-004) — **Pass (conditional)**, 2026-07-27.

### Changed

- Dependabot: merged open dependency/CI bumps (php-cs-fixer, rector, phpstan, actions/cache, action-gh-release, ckeditor5, vite).
- Security docs: CSP / MutationObserver notes; explicit HTML sanitization reminder from [`USAGE.md`](USAGE.md).

## [1.2.2] - 2026-07-23

### Added

- Dev QA: `nowo-tech/phpstan-frankenphp` (`require-dev`) with classic + worker rulesets in `phpstan.neon.dist` (REQ-CS-005).
- GitHub automation: [`.github/copilot-instructions.md`](../.github/copilot-instructions.md), [`.github/dependabot.yml`](../.github/dependabot.yml), [`.github/workflows/pr-lint.yml`](../.github/workflows/pr-lint.yml), [`.github/workflows/stale.yml`](../.github/workflows/stale.yml) (REQ-GH-001/002/004/005).
- README: FrankenPHP Friendly Worker Mode banner ([`docs/images/frankenphp-friendly.png`](images/frankenphp-friendly.png)) after PHPStan FrankenPHP rules (REQ-DOCS-017).

### Changed

- [`CONFIGURATION.md`](CONFIGURATION.md): Twig override procedure + overridable templates table (REQ-TWIG-001); table of contents.
- README: explicit numeric coverage for PHP / TS/JS / Python (REQ-TEST-007).
- TypeScript: English JSDoc on `logger.ts` and exported entry helpers (REQ-ASSETS-002).
- Demo `.gitignore`: ignore `/.pnpm-store` (REQ-GITIGNORE-003).
- PHP-CS-Fixer: enable `fully_qualified_strict_types.import_symbols` (aligns with Nowo CS baseline).

## [1.2.1] - 2026-07-22

### Changed

- Demo FrankenPHP: select runtime with **`FRANKENPHP_MODE`** (`worker` default / `classic`) via `.env` + Compose; extract `docker/entrypoint.sh` (no longer inline in the Dockerfile).
- Documentation: [`DEMO-FRANKENPHP.md`](DEMO-FRANKENPHP.md), demo README, and root README aligned with `FRANKENPHP_MODE` (no longer implies `APP_ENV=dev` alone disables workers).

## [1.2.0] - 2026-07-18

### Changed

- Configuration: renamed YAML keys **`default_config`** / **`configs`** to **`default_profile`** / **`profiles`** (AuditKit-style). Legacy keys still accepted via normalization; container parameters keep legacy aliases. Form option `config` unchanged. See [`UPGRADING.md`](UPGRADING.md) and [`CONFIGURATION.md`](CONFIGURATION.md).
- Documentation, Flex recipe, demo, and fixtures updated to the new key names.

## [1.1.4] - 2026-07-16

### Added

- CI: **`git-hygiene`** job enforcing **REQ-GIT-001** (no Cursor co-author trailers) with full-history checkout.
- CodeRabbit: [`.coderabbit.yaml`](../.coderabbit.yaml) and [`.github/workflows/coderabbit.yml`](../.github/workflows/coderabbit.yml).
- Maintainer tooling: Spec Kit (`.specify/`), Cursor skills/rules, `.githooks/commit-msg`, and `.scripts/check-no-cursor-coauthor.sh` / `strip-cursor-coauthor-from-history.sh`.
- Makefile targets: **`check-no-cursor-coauthor`**, **`strip-cursor-coauthor-from-history`**, **`setup-hooks`** (wired into **`release-check`**).

### Changed

- [`RELEASE.md`](RELEASE.md): re-run the co-author check after the release commit and before `git push`.
- `.gitignore`: ignore Cursor local sandbox (`.cursor/sandbox.json`).

### Fixed

- PHP-CS-Fixer: exclude auto-generated `demo/symfony8/config/reference.php` (same approach as the integration fixture).

## [1.1.3] - 2026-07-16

### Fixed

- Composer: require **`symfony/asset`** so registering the `nowo_ckeditor5_editor` asset package (since **1.1.0**) does not fail when FrameworkBundle enables assets during kernel boot / integration tests.

## [1.1.2] - 2026-07-16

### Added

- Translations: placeholder string for **de**, **fr**, **it**, **nl**, and **pt** (`NowoCkeditor5EditorBundle.*.yaml`).
- Documentation: [`GITHUB_CI.md`](GITHUB_CI.md), [`SPEC-KIT.md`](SPEC-KIT.md), and root [`CODE_OF_CONDUCT.md`](../CODE_OF_CONDUCT.md) (linked from the README).

### Changed

- Demo Symfony 8 Docker image: enable PHP **`intl`** extension (alongside `zip`).

## [1.1.1] - 2026-07-16

### Removed

- Demo: **`demo/symfony7`** (Symfony **7.4** FrankenPHP app). Use **`demo/symfony8`** for local demos; Symfony **7.x** remains covered by the CI PHPUnit matrix.

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

[Unreleased]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.2.2...HEAD
[1.2.2]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.1.4...v1.2.0
[1.1.4]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.1.3...v1.1.4
[1.1.3]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.1.2...v1.1.3
[1.1.2]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.3...v1.1.0
[1.0.3]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/nowo-tech/Ckeditor5EditorBundle/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/nowo-tech/Ckeditor5EditorBundle/releases/tag/v1.0.0
