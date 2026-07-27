# Demo applications with FrankenPHP (development and production)

This document describes how the **CKEditor 5 Editor Bundle** demos run under **FrankenPHP** in Docker: **`classic`** (no worker, changes on refresh) vs **`worker`** (production-style). Switch with **`FRANKENPHP_MODE`**. Reuse the same pattern in other Symfony bundles with FrankenPHP demos.

## Contents

- [Overview](#overview)
  - [Demo smoke (REQ-TEST-011)](#demo-smoke-req-test-011)
- [What each demo includes](#what-each-demo-includes)
- [Development](#development)
- [Production / worker mode](#production--worker-mode)
- [Ports and URLs](#ports-and-urls)
- [Switching classic vs worker (`FRANKENPHP_MODE`)](#switching-classic-vs-worker-frankenphp_mode)
- [Troubleshooting](#troubleshooting)

## Overview

The **`demo/` folder is not shipped** when you install the bundle via Composer (`archive.exclude` includes `/demo`). Demos exist only in the Git repository for development and QA.

Each demo uses:

- **FrankenPHP** (Caddy + PHP) in one container.
- **Docker Compose** mounting the demo app and the parent bundle at **`/var/ckeditor5-editor-bundle`** for the Composer **path** repository.
- **`Caddyfile`** (production-oriented, worker) and **`Caddyfile.dev`** (development, classic `php_server`).
- An **entrypoint** that selects the Caddyfile from **`FRANKENPHP_MODE`** (`worker` or `classic`). Use **`classic`** so Twig and bundle changes are visible without restarting workers.

There is one demo: **`demo/symfony8`** (Symfony **8.1.***, default HTTP port **8021**). From the bundle root:

```bash
make -C demo up-symfony8
# http://localhost:8021 (see demo README / PORT in .env)
```

### Demo smoke (REQ-TEST-011)

Prove the demo boots and returns **HTTP 200**:

```bash
make demo-smoke
# or: make -C demo demo-smoke
# or: make -C demo release-verify
```

This starts `demo/symfony8`, curls `http://127.0.0.1:$PORT` (default **8021**), expects **200**, then tears the stack down. Included in `make -C demo release-check` / root `make release-check`.

## What each demo includes

In **`APP_ENV=dev`** (default for the demos):

- **Symfony Web Profiler** and **Debug** bundles (`require-dev`) for toolbar and profiling.
- **`nowo-tech/twig-inspector-bundle`** (`require-dev`) where listed in the demo `composer.json`.

The bundle under test is **`nowo-tech/ckeditor5-editor-bundle`**, installed from the path repo **`/var/ckeditor5-editor-bundle`**.

### FrankenPHP worker mode (compatibility)

**FrankenPHP worker mode:** Supported for production-style runs (worker-enabled `Caddyfile`, e.g. `worker /app/public/index.php 2` inside `php_server`). The **bundle itself** is a form widget + static JS; it does not require workers. For local editing with refresh, set **`FRANKENPHP_MODE=classic`** — see each demo’s `docker/frankenphp/` files (`Caddyfile` vs `Caddyfile.dev`).

## Development

Goal: edit PHP, Twig, YAML, or bundle sources and see changes after a browser refresh.

- Set **`FRANKENPHP_MODE=classic`** in the demo `.env` (entrypoint activates **`Caddyfile.dev`**: classic **`php_server`** without **`worker`**).
- Recreate the container after changing `.env` (`docker compose up -d` / `make up`); a plain restart does not reload env.
- **`docker/php-dev.ini`**: short OPcache revalidation interval for dev.
- **`APP_ENV=dev`**, **`APP_DEBUG=1`** in Compose (see each demo’s `docker-compose.yml`) for Profiler / debug tooling.
- **DNS**: Compose sets **`dns: 8.8.8.8` / `8.8.4.4`** so Composer can resolve Packagist inside Docker/WSL.

Start from **`demo/symfony8`** with `make up` (see **`demo/README.md`**).

## Production / worker mode

For production-like behaviour:

- Keep **`FRANKENPHP_MODE=worker`** (default) so the entrypoint uses the worker **`Caddyfile`**.
- Optionally use **`APP_ENV=prod`**, **`APP_DEBUG=0`**, warm Symfony cache, and follow deployment hardening for `var/` and secrets.

Compare **`Caddyfile`** vs **`Caddyfile.dev`** in `demo/symfony8/docker/frankenphp/`.

## Ports and URLs

| Demo     | Symfony | Default `PORT` | URL                    |
| -------- | ------- | -------------- | ---------------------- |
| symfony8 | 8.1.*   | 8021           | http://localhost:8021 |

Override `PORT` in the demo `.env` (from `.env.example`) if ports clash.

## Switching classic vs worker (`FRANKENPHP_MODE`)

Demos select the FrankenPHP runtime via **`FRANKENPHP_MODE`** in `.env` / `.env.example` (not a Dockerfile `ENV`):

| Value | Behaviour |
| --- | --- |
| **`worker`** (default) | Keep the worker Caddyfile (`php_server { worker ... }`) |
| **`classic`** | Entrypoint copies `Caddyfile.dev` (plain `php_server`, hot-reload friendly) |

Compose passes `FRANKENPHP_MODE=${FRANKENPHP_MODE:-worker}` into the PHP service. After changing `.env`, run `docker compose up -d` (or `make up`) so the container is **recreated** — a plain `restart` does not reload env. No image rebuild is required.

## Troubleshooting

- **Composer cannot resolve `repo.packagist.org`**: Ensure Docker DNS is set (this repo’s compose files include public DNS). On corporate networks you may need internal DNS forwarders.
- **Changes not visible**: Set **`FRANKENPHP_MODE=classic`** and recreate the container so **`Caddyfile.dev`** is active (no worker).
- **Bundle not updating**: Run **`make update-bundle`** in the demo or `composer update nowo-tech/ckeditor5-editor-bundle` inside the container after editing the path-mounted bundle.
