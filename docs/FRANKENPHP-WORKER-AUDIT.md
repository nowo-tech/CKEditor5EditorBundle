# FrankenPHP worker mode audit (kernel not reset between requests)

| Field | Value |
|-------|-------|
| Package | `nowo-tech/ckeditor5-editor-bundle` (`symfony-bundle`) |
| Audited revision | `v1.4.8` (pre-tag) |
| Audit date | 2026-09-24 |
| Method | Manual review of every file under `src/` (form type, data transformer, sanitizers, Twig extension, DI extension, compiler pass, `Resources/config/services.yaml`, form theme templates); PHPStan with `ruleset-classic` + `ruleset-worker` + `ruleset-worker-strict`; regression test for consecutive `buildView()` on the same form type instance |
| **Verdict** | ✅ **Viable under scenario B** — 100% compatible with FrankenPHP worker mode when the kernel is **not** reset between requests (`reset_kernel: false` / no `services_resetter`). Safe under A and classic PHP-FPM as well |

## Execution model assumed

FrankenPHP worker mode boots the Symfony kernel once per worker and serves many requests with the same container. This audit assumes the **strict** variant: the kernel is **not** rebooted between requests, so every shared service, static property and PHP global survives from one request to the next. Two scenarios are evaluated:

- **A — kernel not rebooted, `services_resetter` still runs:** services tagged `kernel.reset` (or implementing `ResetInterface`) are reset between requests.
- **B — no reset at all (`reset_kernel: false`):** nothing is reset; any per-request state kept in a service leaks into the next request.

A bundle that is safe under **B** is safe under **A** and under classic mode / PHP-FPM.

## Summary

| Area | Status | Notes |
|------|--------|-------|
| Mutable state in shared services | ✅ | `Ckeditor5EditorType` only has `readonly` properties; sanitizers and the Twig extension have no mutable properties |
| Static properties / `static` locals | ✅ | None; only enum helpers (`EditorPreset`, `EditorTheme`) and static closures |
| `ResetInterface` / `kernel.reset` coverage | ✅ N/A | Nothing to reset; no per-request memos |
| Request / user / locale captured in services | ✅ | The upload CSRF token is read in `buildView()` on each render, not at construction |
| Superglobals, `$_ENV`, `putenv`, `ini_set`, `setlocale`, timezone | ✅ | None used; config is compiled into container parameters |
| Doctrine / EntityManager | ✅ N/A | No persistence |
| Output, headers, `exit`, shutdown functions | ✅ | None |
| Resources (files, sockets, cURL) held open | ✅ | None |
| Memory growth across requests | ✅ | No caches or accumulating arrays |
| Blocking I/O and timeouts | ✅ N/A | No network or process calls on the server side (the upload endpoint is provided by the application) |
| Third-party static state | ✅ | Only Symfony Form / OptionsResolver / Security CSRF / Twig |
| PHPStan FrankenPHP rulesets | ✅ | `ruleset-classic.neon` + `ruleset-worker-strict.neon` (includes worker + request-superglobal flags) in `phpstan.neon.dist` |

## Services reviewed

| Service | Shared | Mutable state | Scenario A | Scenario B |
|---------|--------|---------------|------------|------------|
| `Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType` | yes (`form.type`) | none (`readonly` profiles, default profile name, CSRF token manager, optional sanitizer) | ✅ | ✅ |
| `Nowo\Ckeditor5EditorBundle\Security\IdentityCkeditor5HtmlSanitizer` | yes (public, default alias) | none | ✅ | ✅ |
| `Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer` (and `.strict` variant) | yes (public) | none (`readonly` `$allowEmbeds`, `private const` allowlists) | ✅ | ✅ |
| `Nowo\Ckeditor5EditorBundle\Twig\NowoCkeditor5EditorTwigExtension` | yes (`twig.extension`) | none | ✅ | ✅ |

`Ckeditor5HtmlSanitizeTransformer` is created per form build in `Ckeditor5EditorType::buildForm()` (`src/Form/Ckeditor5EditorType.php:55-60`) and only holds a `readonly` reference to the sanitizer. `NowoCkeditor5EditorExtension` and `TwigPathsPass` only run at container compile time.

## Findings

No findings above Info.

### W-01 — Upload CSRF token is fetched per render (Info, good pattern)

- **Where:** `src/Form/Ckeditor5EditorType.php:72-77` calls `$this->csrfTokenManager->getToken(self::CSRF_UPLOAD_TOKEN_ID)` inside `buildView()`; the token manager is injected (`src/Resources/config/services.yaml:25`), not the token itself.
- **Worker impact:** the token is resolved against the current request's session every time the field is rendered, so one user's token is never reused for another user. This is correct for worker mode with `reset_kernel: false`. It would break if the token value were ever cached in a property of the form type.
- **Regression:** `Ckeditor5EditorTypeTest::testConsecutiveBuildViewOnSameInstanceDoesNotLeakCsrf` asserts two consecutive `buildView()` calls on the same instance return distinct CSRF values without calling `reset()`.
- **Recommendation:** keep reading the token in `buildView()`; do not memoize it.

### W-02 — Custom sanitizer services are shared (Info)

- **Where:** `src/DependencyInjection/NowoCkeditor5EditorExtension.php:67-95` lets `html_sanitizer` point to any service id, aliased to `Ckeditor5HtmlSanitizerInterface` and injected into the shared form type.
- **Worker impact:** the built-in sanitizers are stateless. A custom implementation that keeps per-request data (for example the current user's allowed tags, or a buffer of removed fragments) would keep it across requests under scenario B.
- **Recommendation:** custom sanitizers must be stateless, or implement `ResetInterface` **and** clear state at the start of every request (ResetInterface alone is insufficient when `reset_kernel: false` if the app disables resetters). Prefer reading request context at call time.

## Usage recommendations in worker mode

- No special configuration or reset hook is needed for this bundle under `reset_kernel: false`.
- Keep any custom `html_sanitizer` service stateless (W-02).
- The image upload endpoint configured in `upload_url` belongs to the application; audit it separately (validate the `X-CSRF-TOKEN` header per request and do not keep uploaded files or user data in service properties).
- The demo (`demo/symfony8/docker/frankenphp/Caddyfile`) runs FrankenPHP with a `worker` block.

## Re-audit triggers

Re-run this audit when a change adds: properties to `Ckeditor5EditorType`, the sanitizers or the Twig extension; a server-side upload controller or Messenger handler; a cache of profiles or tokens; an event listener; or any use of `RequestStack`, `$_SERVER` or `$_ENV` at runtime.
