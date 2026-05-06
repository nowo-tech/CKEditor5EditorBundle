# CKEditor 5 Editor Bundle — demos

Two Symfony apps (7.x and 8.x) served with **FrankenPHP**. Each mounts this repository at `/var/ckeditor5-editor-bundle` so Composer can install `nowo-tech/ckeditor5-editor-bundle` from the path repository.

## Ports

| Demo    | Default URL            |
|---------|------------------------|
| Symfony 7 | http://localhost:8020 |
| Symfony 8 | http://localhost:8021 |

Override with `PORT` in each demo’s `.env`.

## Commands (from `demo/`)

```bash
make help
make up-symfony8    # or up-symfony7
make verify-symfony8
make down-symfony8
```

Inside `demo/symfony8` or `demo/symfony7`:

```bash
make up          # build image, composer install, assets:install, start FrankenPHP
make install
make update-bundle
```

After changing the bundle PHP/Twig, run `make update-bundle` in the demo or `composer update nowo-tech/ckeditor5-editor-bundle` in the container.

## Assets

The demo loads `ckeditor5-editor.js` via `assets:install` from the bundle’s `Resources/public`. Re-run after rebuilding frontend assets in the bundle root (`npm run build` or `pnpm run build`).

## Pages

- `/` — home with links to the demo routes.
- `/{_locale}/demo` — default profile editor.
- `/{_locale}/demo/configs` — three named profiles (full / simple / minimal).
- `/{_locale}/demo/inline-config` — **`editor_config`** merges over a named YAML profile from PHP (`config` + same keys as YAML: `preset`, `min_height`, `upload_url`, …).
- `/{_locale}/demo/variants` — compact & folio heights (parameters + `DEMO_CKEDITOR_FOLIO_MIN_HEIGHT` in `.env`), dark & auto themes, reading mode without toolbar. See `config/packages/nowo_ckeditor5_editor.yaml` and `demo_ckeditor_parameters.yaml`.
- `/{_locale}/demo/emoji` — **Emoji** preset (toolbar picker + `:` suggestions); emoji definitions load from CKEditor CDN.
- `/{_locale}/demo/typography` — **typography** preset: **font family** and **font size** dropdowns (inline `font-family` / `font-size` in stored HTML).
- `/{_locale}/demo/variables` — **variables** preset: **Mention** with `@` — merge-style placeholders (`@first_name`, `@email`, …); feed is defined in `ckeditor5-editor.ts`.
- `/{_locale}/demo/upload-images` — **standard** preset + **`upload_url`**: toolbar image upload via **SimpleUploadAdapter**. `POST /upload/ckeditor` stores files under `public/uploads/ckeditor/` (JPEG/PNG/GIF/WebP, max 5 MB), validates **CSRF** (`X-CSRF-TOKEN`, token id `ckeditor_upload`), returns JSON `{ "url": "…" }`. After submit, the page shows escaped HTML and a rendered preview so uploaded images load from public URLs.
