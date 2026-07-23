## AI contribution guidelines (Nowo Symfony bundle)

Use this when suggesting code, tests, documentation, or CI changes for this repository.

### Scope

- This is a **Symfony bundle** published under `nowo-tech/*` on Packagist (`nowo-tech/ckeditor5-editor-bundle`).
- Respect the **PHP** and **Symfony** version ranges declared in `composer.json`.
- Prefer **PHP 8 attributes** for configuration and metadata. Do not introduce `doctrine/annotations` for new code.

### Code

- Follow **PSR-12** and project conventions in `.php-cs-fixer.dist.php` (including `declare_strict_types`).
- Use **strict comparison** (`===`) where appropriate.
- Keep changes **minimal** and consistent with existing patterns in `src/` and `tests/`.
- Align with `composer cs-check`, `composer phpstan`, and `composer test` expectations.
- Frontend assets: kebab-case TypeScript filenames under `src/Resources/assets/src/`; English JSDoc on exported helpers; rebuild with `pnpm run build` when sources change.

### Git

- **Never** add `Co-authored-by: Cursor` or similar Cursor agent trailers to commit messages (REQ-GIT-001).

### Documentation

- User-facing documentation is **English** under `docs/` per Nowo bundle standards.
- Only `README.md` at repository root (no extra root markdown files).

### Tests

- Add or update tests for new behaviour; keep coverage in line with README and CI (PHP 100% on `src/`; Vitest on `logger.ts`).
