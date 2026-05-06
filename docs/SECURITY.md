# Security Policy

## Table of contents

- [Security considerations for integrators](#security-considerations-for-integrators)
- [Bundle responsibility](#bundle-responsibility)
- [Supported versions](#supported-versions)
- [Reporting a vulnerability](#reporting-a-vulnerability)
- [Release security checklist (12.4.1)](#release-security-checklist-1241)

## Security considerations for integrators

- **HTML and XSS**: This bundle stores **HTML** produced by CKEditor 5 in a form field. The bundle does **not** enforce HTML sanitization. **Your application** must sanitize or allowlist content before persisting or rendering it (e.g. HTML Purifier, DOMPurify on the client, or server-side filtering), especially for user-generated content.
- **Script tags**: The widget loads `ckeditor5-editor.js` from published bundle assets. Use `assets:install` / AssetMapper hygiene and trusted builds only.
- **Upload endpoints**: If you configure `upload_url`, your endpoint must validate MIME types, size limits, and authentication/authorization; the demos are examples only.
- **CSRF**: Upload flows may use CSRF tokens (`Ckeditor5EditorType::CSRF_UPLOAD_TOKEN_ID`) — ensure your routes validate them consistently.

## Bundle responsibility

The bundle provides a Symfony form type, Twig themes, translations, and a static JS bundle. It does not execute persisted HTML on the server beyond normal form handling.

## Supported versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a vulnerability

If you discover a security vulnerability in this project, please report it responsibly:

1. **Do not** open a public GitHub issue for security-sensitive bugs.
2. Send details to **[hectorfranco@nowo.tech](mailto:hectorfranco@nowo.tech)** (or the maintainers listed in [`composer.json`](../composer.json)).
3. Include a clear description, steps to reproduce, and impact if possible.
4. We will acknowledge receipt and work on a fix. We may ask for more information.
5. After a fix is released, we can coordinate on disclosure.

We appreciate responsible disclosure so users can update before details are public.

## Release security checklist (12.4.1)

Before tagging a release, confirm:

| Item | Notes |
|------|--------|
| **SECURITY.md** | This document is current and linked from the README where applicable. |
| **`.gitignore` and `.env`** | `.env` and local env files are ignored; no committed secrets. |
| **No secrets in repo** | No API keys, passwords, or tokens in tracked files. |
| **HTML / XSS** | Documentation reminds integrators to sanitize persisted HTML. |
| **Input / output** | Form options validated; user HTML is not executed server-side by the bundle. |
| **Dependencies** | `composer audit` run; issues triaged. |
| **Logging** | Logs do not print secrets or session identifiers unnecessarily. |
| **Assets** | Built `ckeditor5-editor.js` is reproducible from source (`pnpm run build`). |

Record confirmation in the release PR or tag notes.
