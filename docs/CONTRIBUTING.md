# Contributing Guide

Thank you for contributing to **CKEditor 5 Editor Bundle**.

## How to contribute

### Reporting bugs

1. Check [existing issues](https://github.com/nowo-tech/Ckeditor5EditorBundle/issues).
2. Open a new issue with steps to reproduce, expected vs actual behaviour, and versions (`composer info nowo-tech/ckeditor5-editor-bundle`, `php -v`, Symfony version).

### Suggesting enhancements

Open an issue describing the use case, expected behaviour, and optional implementation ideas.

### Contributing code

1. Fork and clone the repository.
2. Install PHP dependencies: `composer install` (or Docker: `make up`).
3. Build frontend assets when touching TS: `make assets` or `pnpm install && pnpm run build`.
4. Run quality checks: `make release-check` or at minimum `composer qa`, `composer phpstan`, `composer test`, and `pnpm run test:coverage` when TS changes.
5. Update [`docs/CHANGELOG.md`](CHANGELOG.md) under `[Unreleased]` for user-visible changes.
6. Open a pull request against `main` using [`.github/PULL_REQUEST_TEMPLATE.md`](../.github/PULL_REQUEST_TEMPLATE.md).

## Project layout

- `src/` — Bundle code (DI extension, form type, Twig extension, compiler passes).
- `src/Resources/` — Twig themes, translations, Vite sources, published `public/` JS.
- `tests/` — PHPUnit (`Unit`, `Integration`).
- `demo/` — FrankenPHP demos: **`symfony7`** (Symfony **7.4**, port **8020**) and **`symfony8`** (Symfony **8.1**, port **8021**); not shipped in the Composer package.

CI runs PHPUnit on **PHP 8.2–8.5** × **Symfony 7.0 / 7.4 / 8.0 / 8.1** (Symfony **8.x** jobs use PHP **≥8.4**).

## Code style

- PHP: PSR-12 via PHP-CS-Fixer (`composer cs-check` / `composer cs-fix`).
- PHPDoc and comments in **English**.
- TypeScript: strict settings where used; English JSDoc for shared helpers.

## Questions

Open a support issue from the issue templates or use repository Discussions if enabled.
