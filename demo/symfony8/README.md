# CKEditor 5 Editor Bundle — Demo (Symfony 8.1)

This demo runs with **FrankenPHP** (Caddy, HTTP on port 80 inside the container). In **dev** (`APP_ENV=dev`), worker mode is disabled so each request runs in a new PHP process and **code/template changes are visible on refresh** without restarting the container.

The app pins **Symfony 8.1.*** via `extra.symfony.require` in `composer.json`. **Symfony 8** requires **PHP ≥8.4** (see the demo Dockerfile / Compose PHP image). Symfony **6.4** / **7.x** compatibility is covered by the CI PHPUnit matrix.

## Quick start

```bash
make up
make install
# Open http://localhost:8021 (or set PORT in .env)
```

Language is switched via URL (locale prefix). Supported: `en`, `es`. Use the language dropdown in the navbar.

## Demo routes

Replace `{locale}` with `en` or `es`:

- `/` — redirects to `/en/`
- `/{locale}/` — home with links to all demos
- `/{locale}/demo` — default profile editor
- `/{locale}/demo/configs` — three named profiles (full / simple / minimal)
- `/{locale}/demo/inline-config` — `editor_config` merges over a named YAML profile from PHP
- `/{locale}/demo/variants` — compact & folio heights, dark & auto themes, reading mode
- `/{locale}/demo/emoji` — Emoji preset (toolbar picker + `:` suggestions)
- `/{locale}/demo/typography` — font family and font size dropdowns
- `/{locale}/demo/variables` — Mention preset with `@` placeholders
- `/{locale}/demo/upload-images` — standard preset + image upload via SimpleUploadAdapter

See [`../README.md`](../README.md) for full details.

## Web Profiler toolbar

The demo has **Web Profiler** and **Nowo Twig Inspector** enabled in `dev`. The toolbar is shown at the bottom of the page when:

- `APP_ENV=dev` and `APP_DEBUG=1` (default in `.env`)
- You have run `make install` and the dev routes are loaded

If the toolbar does not appear, clear the cache inside the container:

```bash
docker compose exec php php bin/console cache:clear --env=dev
```

Then reload the page. You can also open `/_profiler` to see the latest requests.

## Commands

- `make up` — build and start the container (FrankenPHP). After changing Dockerfile or Caddyfile, run `make build` or `docker compose build` then `make up`.
- `make down` — stop the container
- `make install` — Composer install (and cache:clear)
- `make update-bundle` — refresh the path-mounted bundle after editing PHP/Twig in the parent repo
- `make shell` — open a shell in the container

See also [`../../docs/DEMO-FRANKENPHP.md`](../../docs/DEMO-FRANKENPHP.md).
