# Upgrade guide

## Table of contents

- [Unreleased](#unreleased)
- [To 1.10.1](#to-1101)
- [To 1.10.0](#to-1100)
- [To 1.9.1](#to-191)
- [To 1.9.0](#to-190)
- [To 1.8.0](#to-180)
- [To 1.7.0](#to-170)
- [To 1.6.0](#to-160)
- [To 1.5.0](#to-150)
- [To 1.4.12](#to-1412)
- [To 1.4.11](#to-1411)
- [To 1.4.10](#to-1410)
- [To 1.4.9](#to-149)
- [From 1.4.7 to 1.4.8](#from-147-to-148)
- [From 1.4.6 to 1.4.7](#from-146-to-147)
- [From 1.4.5 to 1.4.6](#from-145-to-146)
- [To 1.4.5 from 1.4.4](#to-145-from-144)
- [To 1.4.4 from 1.4.3](#to-144-from-143)
- [To 1.4.3 from 1.4.2](#to-143-from-142)
- [To 1.4.2 from 1.4.1](#to-142-from-141)
- [To 1.4.1 from 1.4.0](#to-141-from-140)
- [General](#general)
- [To 1.4.0 from 1.3.1](#to-140-from-131)
- [To 1.3.1 from 1.3.0](#to-131-from-130)
- [To 1.3.0 from 1.2.3](#to-130-from-123)
- [To 1.0.0 (first Git / Packagist semver tag)](#to-100-first-git--packagist-semver-tag)
- [To 1.0.1 from 1.0.0](#to-101-from-100)
- [To 1.0.2 from 1.0.1](#to-102-from-101)
- [To 1.0.3 from 1.0.2](#to-103-from-102)
- [To 1.1.0 from 1.0.3](#to-110-from-103)
- [To 1.1.1 from 1.1.0](#to-111-from-110)
- [To 1.1.2 from 1.1.1](#to-112-from-111)
- [To 1.1.3 from 1.1.2](#to-113-from-112)
- [To 1.1.4 from 1.1.3](#to-114-from-113)
- [To 1.2.0 from 1.1.4](#to-120-from-114)
- [To 1.2.1 from 1.2.0](#to-121-from-120)
- [To 1.2.2 from 1.2.1](#to-122-from-121)
- [To 1.2.3 from 1.2.2](#to-123-from-122)
- [To 1.x (first documented stable line)](#to-1x-first-documented-stable-line)

## Unreleased

## To 1.10.1

From **1.10.0**:

```bash
composer update nowo-tech/ckeditor5-editor-bundle
```

## To 1.10.0

From **1.9.1**:

```bash
composer update nowo-tech/ckeditor5-editor-bundle
```

## To 1.9.1

From **1.9.0**:

```bash
composer update nowo-tech/ckeditor5-editor-bundle
```

## To 1.9.0

- Run `assets:install` (new public file `ckeditor5-editor.css`). If you render the widget through `nowo_ckeditor5_editor_script()`, nothing else changes. If you print your own `<script src="…/ckeditor5-editor.js">`, also link `nowo_ckeditor5_editor_asset_url('ckeditor5-editor.css')` (the build no longer injects its styles).
- CSP: drop any per-route exception that removed the style nonce for CKEditor pages (for example HttpHardening `nonce_free_style_path_prefixes`) — `style-src-elem 'self' 'nonce-…'` is enough.

## To 1.8.0

From **1.7.0** — widget CSS bundled instead of inline style; Symfony >=7.4; vitest 4.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
php bin/console assets:install
```

- **Symfony 7.4+ required** (`^7.4 || ^8.0`). Apps on Symfony 6.4 or 7.0–7.3 stay on 1.7.x until they upgrade Symfony.
- The form theme no longer prints an inline `<style>` per widget; the styles ship in `ckeditor5-editor.js`. Run `assets:install` after updating and load the editor with `nowo_ckeditor5_editor_script()` (or the published `ckeditor5-editor.js`) as before. If you overrode the `ckeditor5_editor_widget` block, the CSS still applies as long as the build script is loaded.

## To 1.7.0

From **1.6.0** — security: allowlist/strict sanitizer rebuilt on symfony/html-sanitizer; CSRF manager optional.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
```

- **`html_sanitizer: allowlist` / `strict`** now uses `symfony/html-sanitizer` (installed as a dependency). Output is normalised HTML (e.g. `<img … />`, entities in text) and is stricter: attributes outside the allowlist (`id`, `data-*`, `srcset`, …) are dropped, links accept only `http`/`https`/`mailto`/relative URLs, images only `http`/`https`/relative (no `data:`), and an embed iframe must be `https://` on YouTube/Vimeo. Re-check stored content if you relied on other attributes; use a custom `html_sanitizer` service if you need more.
- Apps with `framework.csrf_protection: false` can now boot; profiles with `upload_url` still need CSRF enabled.

## To 1.6.0

From **1.5.0** — bundled CKEditor 5 upgraded to v49.0.0.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
php bin/console assets:install
```

- No bundle API or configuration changes.
- The shipped editor build now embeds CKEditor 5 v49.0.0; if your app extends the editor with custom CKEditor plugins, check the [CKEditor 5 v49 release notes](https://github.com/ckeditor/ckeditor5/releases) for plugin API changes.

## To 1.5.0

From **1.4.12** — opt-in lazy loading of the editor build and versioned asset URLs. No breaking changes; existing script tags keep working.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
php bin/console assets:install public   # publishes the new ckeditor5-editor-loader.js
```

Optional — replace the manual tag with the Twig function (adds a `?v=<content hash>` query, safe for long-lived caching of `/bundles/*`):

```twig
{# before #}
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}" defer></script>
{# after (eager, same behaviour) #}
{{ nowo_ckeditor5_editor_script() }}
```

On pages where the editor is not visible at first (inside a closed `<dialog>`, collapsed panel, below the fold), load it lazily — only the ~2.6 kB loader is downloaded until an editor becomes visible:

```twig
{{ nowo_ckeditor5_editor_script({lazy: true}) }}
{# with a CSP nonce #}
{{ nowo_ckeditor5_editor_script({lazy: true, nonce: csp_nonce('script')}) }}
```

To make lazy the default for every call: `nowo_ckeditor5_editor: { lazy_load: true }`. JavaScript that needs the editor immediately (for example before opening a modal programmatically) can call `await window.NowoCkeditor5EditorLoader.load()`. Code that uses `window.NowoCkeditor5Editor.*` directly must wait for the build to load on lazy pages.

## To 1.4.12

From **1.4.11** — dependency refresh only. No breaking changes. **No application upgrade steps.**

```bash
composer update nowo-tech/ckeditor5-editor-bundle
```

## To 1.4.11

From **1.4.10** — frontend race fix for duplicate ClassicEditor mounts; optional auto-observe opt-out.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
php bin/console assets:install public
# rebuild host assets if you copy/bundle the public JS
```

- No YAML/config changes required for standard form widgets.
- If you dynamically call `NowoCkeditor5Editor.initCkeditor5Root()` **and** keep `data-ckeditor5-root` on the widget, the sync mount lock alone prevents duplicates. Optionally set `data-ckeditor5-auto-observe="0"` on `<html>`/`<body>` when you do not want MutationObserver auto-discovery.

## To 1.4.10

From **1.4.9** — maintainers dependency bumps only. **No application upgrade steps.**

```bash
composer update nowo-tech/ckeditor5-editor-bundle
```

## To 1.4.9

From **1.4.8** — REQ-CS-008 Igor FrankenPHP worker audit (igor-php require-dev, igor.json, make igor).

```bash
composer update nowo-tech/ckeditor5-editor-bundle
php bin/console cache:clear
```

- No application upgrade steps for require-dev Igor tooling (REQ-CS-008). Consumers do not pull `igor-php/igor-php` transitively.

## From 1.4.7 to 1.4.8

FrankenPHP worker hygiene for apps that disable kernel reset between requests (`reset_kernel: false`). **No required application or YAML changes.**

- PHPStan in this package now uses `ruleset-classic` + `ruleset-worker-strict` (dev/CI only; replaces the previous classic + worker pair).
- Custom `html_sanitizer` services must stay **stateless** (or clear per-request state themselves); built-in sanitizers already are.
- See [`FRANKENPHP-WORKER-AUDIT.md`](FRANKENPHP-WORKER-AUDIT.md) for the full scenario B audit.

## From 1.4.6 to 1.4.7

Security patch for the built-in HTML sanitizer. **No required YAML change** if you already use `html_sanitizer: allowlist`.

- **`allowlist`:** unquoted and glued event handlers, unquoted `javascript:` / `data:` / `vbscript:` URLs, and `srcdoc` are stripped. A kept YouTube/Vimeo iframe is rewritten to `<iframe src="…"></iframe>` (other iframe attributes are dropped). Re-check stored embeds after deploy if you relied on extra iframe attributes.
- **`strict`:** same sanitizer, but every `iframe` is dropped. Use it for public HTML that must not embed third-party players (legal pages):

```yaml
nowo_ckeditor5_editor:
    html_sanitizer: strict
```

Editor routes that load CKEditor still need `style-src-elem 'self' 'unsafe-inline'` **without** a nonce on that directive. A nonce makes browsers ignore `unsafe-inline`.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
php bin/console cache:clear
```

## From 1.4.5 to 1.4.6

No breaking changes. **No application upgrade steps.**

```bash
composer update nowo-tech/ckeditor5-editor-bundle
```

## To 1.4.5 from 1.4.4

From **1.4.4** — If production uses `html_sanitizer: allowlist` (Flex recipe `when@prod`, **not** applied automatically to YAML you already copied), submitted HTML is **lossy**.

**Kept tags:** `p`, `br`, `strong`, `b`, `em`, `i`, `u`, `s`, `del`, `h1`–`h6`, `ul`, `ol`, `li`, `blockquote`, `code`, `pre`, `a`, `img`, `table`, `thead`, `tbody`, `tr`, `th`, `td`, `caption`, `hr`, `span`, `div`, `figure`, `figcaption`, `sub`, `sup`, `mark`, `iframe` (YouTube / Vimeo hosts only).

**Stripped:** `<script>`, event handlers, `javascript:` URLs, unknown tags (including custom CKEditor widgets), iframes from other hosts.

1. Re-copying or merging the Flex recipe into an existing app **enables** the sanitizer in `prod` even if you previously had none — review stored HTML after the first prod deploy.
2. If the allowlist still drops markup you need, set `html_sanitizer` to your own service id implementing `Ckeditor5HtmlSanitizerInterface` (do not disable sanitization for untrusted UGC).
3. Trusted-staff-only editors may keep `html_sanitizer: null` (PHP default) — document that choice.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
php bin/console cache:clear --env=prod
```

## To 1.4.4 from 1.4.3

No application upgrade steps (SECURITY re-audit docs only).

```bash
composer update nowo-tech/ckeditor5-editor-bundle
```

## To 1.4.3 from 1.4.2

No application upgrade steps.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
```

## To 1.4.2 from 1.4.1

Review production config if you render editor HTML from untrusted sources. The Flex recipe sets `when@prod`:

```yaml
nowo_ckeditor5_editor:
    html_sanitizer: allowlist
```

Hosts that already trust all editors may keep the default (no sanitizer) or set a custom `PageLayoutHtmlSanitizerInterface` service id.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
php bin/console cache:clear
```

## To 1.4.1 from 1.4.0

No application upgrade steps. Demos only: Hot Reload Bundle `^1.4` (FrankenPHP Mercure/`hot_reload`, `dev`/`test`).

## General

**Supported platforms:** Symfony **6.4**, **7.x** (incl. **7.4**) on PHP **8.2+**; Symfony **8.0** and **8.1** on PHP **8.4+**. See [`INSTALLATION.md`](INSTALLATION.md#requirements).

- Follow [`CHANGELOG.md`](CHANGELOG.md) for each release.
- Pin versions in `composer.json` (e.g. `^1.0`) instead of relying only on `dev-main` for production apps.
- After upgrading, run `php bin/console cache:clear` and `php bin/console assets:install public` so Twig and published bundle assets stay in sync.

## To 1.4.0 from 1.3.1

Minor release: required **Twig Extra** (REQ-TWIG-004) for hosts that render this bundle’s Twig templates.

```bash
composer update nowo-tech/ckeditor5-editor-bundle
php bin/console cache:clear
```

Composer pulls `twig/extra-bundle` `^3.12` and `twig/string-extra` `^3.12`. Register if Flex did not:

```php
Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
```

**Maintainers:** `composer twig:lint` / `composer twig:fix` use `.twig-cs-fixer.php`.

No YAML, form API, or asset path changes.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.4.0**).

## To 1.3.1 from 1.3.0

Patch release: Makefile Compose V2 preference and optional monorepo `update-deps` includes. No bundle API, YAML, or runtime behaviour changes for applications.

- **Composer:** `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.3.0`); with `^1.0` or `^1.3`, **1.3.1** is included on update.
- **Contributors / demos:** Makefiles use `docker compose` when available (fallback `docker-compose`). Standalone clones no longer require `bundles/.scripts` for `make` to load.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.3.1**).

## To 1.3.0 from 1.2.3

Minor release: public backed enum `EditorTheme` for chrome palette values, plus Nowo standards (deprecations helper, PHPStan baseline, pnpm-only frontend, demo PHP 8.5). YAML `theme` string values are unchanged.

- **Config:** `theme` remains `light` / `dark` / `auto` (validated via `EditorTheme`). Invalid values still fall back to `light` on the form type.
- **Composer:** `composer update nowo-tech/ckeditor5-editor-bundle` (with `^1.0` or `^1.3`); then `php bin/console cache:clear`.
- **Frontend contributors:** standardize on **pnpm** (`packageManager` in `package.json`). Prefer `pnpm install` / `pnpm run build` over npm; `package-lock.json` is removed.
- **Demo:** FrankenPHP image is PHP **8.5** (`dunglas/frankenphp:1-php8.5-alpine`).

No Doctrine schema or Twig block renames.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.3.0**).

## To 1.0.0 (first Git / Packagist semver tag)

`v1.0.0` is the first annotated release tag. If you were tracking `dev-main` or a commit hash, switch to a semver constraint so upgrades are predictable:

- **Composer**: `composer require nowo-tech/ckeditor5-editor-bundle:^1.0`

No bundle API breaks are introduced solely by tagging; see [`CHANGELOG.md`](CHANGELOG.md) for the full 1.0.0 notes.

## To 1.0.1 from 1.0.0

Patch release: CI and contributor installs on **PHP 8.2** using the repository lock file. No bundle API or YAML changes.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` if you pin `1.0.0` exactly; with `^1.0` you get 1.0.1 automatically on update.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.0.1**) for details.

## To 1.0.2 from 1.0.1

Patch release: CI (Symfony **8** matrix + Composer platform) and **PHPStan** config only. No bundle API, YAML, or runtime behaviour changes for applications.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.0.1`); with `^1.0`, **1.0.2** is included on update.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.0.2**) for details.

## To 1.0.3 from 1.0.2

Patch release: documentation, CI matrix (**Symfony 7.4** / **8.1**), demo pins, and Makefile `update-deps` targets only. No bundle API, YAML, or runtime behaviour changes for applications.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.0.2`); with `^1.0`, **1.0.3** is included on update.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.0.3**) for details.

## To 1.1.0 from 1.0.3

Minor release: **Symfony asset package** registration and Twig helper changes. **Update your layout** if you load the editor script with the old one-argument `asset()` call.

**Before (≤ 1.0.3):**

```twig
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js')) }}"></script>
```

**After (1.1.0+):**

```twig
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}"></script>
```

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` (with `^1.0` or `^1.1`); then `php bin/console cache:clear` and `php bin/console assets:install public`.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.1.0**) for details.

## To 1.1.1 from 1.1.0

Patch release: repository demo cleanup and docs only. No bundle API, YAML, or runtime behaviour changes for applications.

- **Contributors / local demos**: **`demo/symfony7`** was removed. Use **`demo/symfony8`** (default port **8021**). Symfony **7.x** remains covered by the CI PHPUnit matrix.
- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.1.0`); with `^1.0` or `^1.1`, **1.1.1** is included on update.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.1.1**) and [`DEMO-FRANKENPHP.md`](DEMO-FRANKENPHP.md).

## To 1.1.2 from 1.1.1

Patch release: additional locales for the editor placeholder, documentation files linked from the README, and demo Docker `intl`. No bundle API or YAML configuration changes for applications.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.1.1`); with `^1.0` or `^1.1`, **1.1.2** is included on update.
- **i18n**: if you override `ckeditor5_placeholder`, no change required; new locale files are additive (**de**, **fr**, **it**, **nl**, **pt**).

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.1.2**).

## To 1.1.3 from 1.1.2

Patch release: declare **`symfony/asset`** as a hard dependency (needed since the **1.1.0** asset package registration). Fixes kernel boot / CI when Asset is not already installed transitively.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` (pulls `symfony/asset` if missing). With `^1.0` or `^1.1`, **1.1.3** is included on update.
- No Twig, YAML, or form API changes. Apps that already have `symfony/asset` (typical Flex apps) need no further steps beyond update + `cache:clear` if desired.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.1.3**) and [`INSTALLATION.md`](INSTALLATION.md).

## To 1.1.4 from 1.1.3

Patch release: repository tooling (CodeRabbit, Spec Kit, Cursor rules), CI **`git-hygiene`** (REQ-GIT-001), and Makefile / docs only. No bundle API, YAML, or runtime behaviour changes for applications.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.1.3`); with `^1.0` or `^1.1`, **1.1.4** is included on update.
- **Contributors**: run `make setup-hooks` once per clone; see [`GITHUB_CI.md`](GITHUB_CI.md) and [`CONTRIBUTING.md`](CONTRIBUTING.md).

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.1.4**).

## To 1.2.0 from 1.1.4

Minor release: YAML keys **`default_config`** / **`configs`** renamed to **`default_profile`** / **`profiles`** (AuditKit-style). Prefer the new names in app config.

**Before (≤ 1.1.4):**

```yaml
nowo_ckeditor5_editor:
    default_config: simple
    configs:
        simple:
            preset: simple
```

**After (1.2.0+):**

```yaml
nowo_ckeditor5_editor:
    default_profile: simple
    profiles:
        simple:
            preset: simple
```

- Legacy keys are still accepted via `beforeNormalization` when the new keys are absent.
- Container parameters `nowo_ckeditor5_editor.default_config` / `nowo_ckeditor5_editor.configs` remain as aliases of the new parameters.
- Form option key **`config`** is unchanged.
- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` (with `^1.0` or `^1.2`); then `php bin/console cache:clear`.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.2.0**) and [`CONFIGURATION.md`](CONFIGURATION.md).

## To 1.2.1 from 1.2.0

Patch release: FrankenPHP **demo** runtime switch via **`FRANKENPHP_MODE`** and documentation alignment. No bundle API, YAML, or runtime behaviour changes for applications.

- **Contributors / local demos**: default is **`worker`**. For hot-reload on refresh, set **`FRANKENPHP_MODE=classic`** in `demo/symfony8/.env` and recreate the container (`docker compose up -d` / `make up`). See [`DEMO-FRANKENPHP.md`](DEMO-FRANKENPHP.md).
- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.2.0`); with `^1.0` or `^1.2`, **1.2.1** is included on update.

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.2.1**).

## To 1.2.2 from 1.2.1

Patch release: Nowo standards compliance (PHPStan FrankenPHP rules, FrankenPHP Friendly banner, Twig override docs, coverage percentages, GitHub automation, JSDoc). No bundle API, YAML, or runtime behaviour changes for applications.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.2.1`); with `^1.0` or `^1.2`, **1.2.2** is included on update.
- **Contributors**: Twig override paths are listed in [`CONFIGURATION.md`](CONFIGURATION.md); PHPStan includes FrankenPHP classic + worker rulesets (`composer phpstan` / `make phpstan`).

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.2.2**).

## To 1.2.3 from 1.2.2

Patch release: Nowo full-spec compliance (open-PR gate, demo smoke, USAGE Twig overrides, REQ-SEC-004 audit record) plus dependency bumps. No bundle API, YAML, or runtime behaviour changes for applications.

- **Composer**: `composer update nowo-tech/ckeditor5-editor-bundle` only if you pin an exact patch (e.g. `1.2.2`); with `^1.0` or `^1.2`, **1.2.3** is included on update.
- **Integrators**: keep sanitizing persisted/rendered HTML (see [`SECURITY.md`](SECURITY.md)); Twig override procedure is also in [`USAGE.md`](USAGE.md).
- **Contributors**: `make release-check` runs `check-open-prs` (REQ-REL-003); use `make demo-smoke` (REQ-TEST-011). Rebuild frontend with current lockfiles if you change assets (**Vite 8** / CKEditor **48.3.x**).

See [`CHANGELOG.md`](CHANGELOG.md) (section **1.2.3**).

## To 1.x (first documented stable line)

When upgrading from snapshots without semver tags in your project:

- **Composer**: `composer require nowo-tech/ckeditor5-editor-bundle:^1.0`
- **Configuration**: prefer explicit **`profiles`** + **`default_profile`** (see [`CONFIGURATION.md`](CONFIGURATION.md)). Legacy keys `default_config` / `configs` and flat YAML under `nowo_ckeditor5_editor` are still accepted and normalized.
- **Bootstrap**: ensure your layout loads the bundle script once per page:

  ```twig
  <script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}"></script>
  ```
