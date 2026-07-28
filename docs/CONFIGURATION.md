# Configuration reference

Root key: `nowo_ckeditor5_editor`

## Contents

- [Top-level](#top-level)
- [Per profile (`profiles.<name>`)](#per-profile-profilesname)
- [Legacy keys and flat YAML](#legacy-keys-and-flat-yaml)
- [Form type `Ckeditor5EditorType` options](#form-type-ckeditor5editortype-options)
- [Twig overrides](#twig-overrides)
  - [Procedure](#procedure)
  - [Overridable templates](#overridable-templates)
- [Translation overrides](#translation-overrides)
- [Parameters exposed to the container](#parameters-exposed-to-the-container)

## Top-level

| Option            | Type   | Default   | Description |
| ----------------- | ------ | --------- | ----------- |
| `default_profile` | string | `default` | Profile name when a form field omits the `config` option. **Must** exist under `profiles`. |
| `profiles`        | map    | —         | Named profiles. At least one profile is required (or use [legacy keys and flat YAML](#legacy-keys-and-flat-yaml) input). |

## Per profile (`profiles.<name>`)

| Option        | Type   | Default                     | Description |
| ------------- | ------ | --------------------------- | ----------- |
| `toolbar`     | bool   | `true`                      | Show the CKEditor toolbar (preset still controls available plugins). |
| `min_height`  | string | `240px`                     | CSS min-height of the editable region. |
| `form_theme`  | string | `form_div_layout.html.twig` | Base Symfony form theme; must align with your app `twig.form_themes`. |
| `debug`       | bool   | `false`                     | Verbose browser console logging from the bundle script. |
| `preset`      | string | —                           | Build preset: `standard`, `simple`, `minimal`, `emoji`, `typography`, `variables`, … (see `EditorPreset`). |
| `theme`       | string | `light`                     | Chrome palette: `light`, `dark`, or `auto` (`EditorTheme` backed enum). |
| `upload_url`  | string | `null`                      | Optional URL for Simple Upload Adapter (demo/apps provide POST endpoints). |

## Legacy keys and flat YAML

- **Renamed keys:** `default_config` / `configs` were renamed to `default_profile` / `profiles`. Legacy keys are still accepted via normalization (mapped when the new keys are absent).
- **Flat layout:** If the root has no `profiles` (nor legacy `configs`) key, the extension maps flat keys into a single profile under `profiles` using `default_profile` (default `default`):

- `toolbar`, `min_height`, `form_theme`, `debug`, `preset`, `theme`, `upload_url`

## Form type `Ckeditor5EditorType` options

| Option          | Type | Description |
| --------------- | ---- | ----------- |
| `config`        | `string\|null` | Profile name under `nowo_ckeditor5_editor.profiles`. `null`/empty uses `default_profile`. (Form option key remains `config` for BC.) |
| `editor_config` | `array` | Keys merged over the YAML profile: `toolbar`, `min_height`, `form_theme`, `debug`, `preset`, `theme`, `upload_url`. |
| `toolbar`       | bool | Field-level override. |
| `min_height`    | string | Field-level override. |
| `height`        | `string\|null` | Alias for min-height when `min_height` is not set. |
| `theme`         | string | Field-level palette (`light` / `dark` / `auto`; `EditorTheme`). |
| `placeholder`   | `string\|bool\|null` | Translation key in domain `NowoCkeditor5EditorBundle`, or `false` to disable. |

Standard Symfony options (`label`, `required`, `translation_domain`, `attr`, …) work as usual.

## Twig overrides

**REQ-TWIG-001.** Application templates under `templates/bundles/NowoCkeditor5EditorBundle/` **always win** over the copies inside the package. The bundle registers paths via `TwigPathsPass` so Symfony resolves app overrides first.

### Procedure

1. Identify the `<subpath>` from the table below (path relative to `src/Resources/views/` inside the bundle).
2. Create in your application: `templates/bundles/NowoCkeditor5EditorBundle/<subpath>` (same relative path and filename).
3. Clear the cache in dev if needed: `php bin/console cache:clear`.

Example — override the default form theme:

```text
templates/bundles/NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme.html.twig
```

Controllers and Twig use logical names such as `@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme.html.twig`, never absolute filesystem paths.

### Overridable templates

| Subpath | Purpose |
| --- | --- |
| `Form/ckeditor5_editor_theme.html.twig` | Default form theme (`form_div_layout`) |
| `Form/ckeditor5_editor_theme_table.html.twig` | Table form layout |
| `Form/ckeditor5_editor_theme_bootstrap3.html.twig` | Bootstrap 3 |
| `Form/ckeditor5_editor_theme_bootstrap3_horizontal.html.twig` | Bootstrap 3 horizontal |
| `Form/ckeditor5_editor_theme_bootstrap4.html.twig` | Bootstrap 4 |
| `Form/ckeditor5_editor_theme_bootstrap4_horizontal.html.twig` | Bootstrap 4 horizontal |
| `Form/ckeditor5_editor_theme_bootstrap5.html.twig` | Bootstrap 5 |
| `Form/ckeditor5_editor_theme_bootstrap5_horizontal.html.twig` | Bootstrap 5 horizontal |
| `Form/ckeditor5_editor_theme_foundation5.html.twig` | Foundation 5 |
| `Form/ckeditor5_editor_theme_foundation6.html.twig` | Foundation 6 |
| `Form/ckeditor5_editor_theme_tailwind2.html.twig` | Tailwind 2 |

Pick the row that matches the profile `form_theme` (or your app `twig.form_themes`). See Symfony’s [How to Override Templates](https://symfony.com/doc/current/bundles/override.html).

## Translation overrides

Translations use the domain **`NowoCkeditor5EditorBundle`** (`src/Resources/translations/`). Override from your app with YAML/XLF under **`translations/`** using the same domain, e.g. `translations/NowoCkeditor5EditorBundle.en.yaml`.

## Parameters exposed to the container

The DI extension sets `nowo_ckeditor5_editor.default_profile` and `nowo_ckeditor5_editor.profiles`, plus legacy aliases `nowo_ckeditor5_editor.default_config` / `nowo_ckeditor5_editor.configs` (same values), and backward-compatible scalars for the **default** profile. Prefer resolving behaviour through `Ckeditor5EditorType` and YAML profiles.
