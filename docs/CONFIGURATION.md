# Configuration reference

Root key: `nowo_ckeditor5_editor`

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
| `theme`       | string | `light`                     | Chrome palette: `light`, `dark`, or `auto`. |
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
| `theme`         | string | Field-level palette (`light` / `dark` / `auto`). |
| `placeholder`   | `string\|bool\|null` | Translation key in domain `NowoCkeditor5EditorBundle`, or `false` to disable. |

Standard Symfony options (`label`, `required`, `translation_domain`, `attr`, …) work as usual.

## Overriding bundle Twig templates

Templates use the **`@NowoCkeditor5EditorBundle`** namespace (e.g. `@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme.html.twig`). Override in your application:

```text
templates/bundles/NowoCkeditor5EditorBundle/Form/<same-file-name>.html.twig
```

Use the logical bundle name `NowoCkeditor5EditorBundle`. Clear cache after changes: `php bin/console cache:clear`.

See Symfony’s [How to Override Templates](https://symfony.com/doc/current/bundles/override.html).

## Translation overrides

Translations use the domain **`NowoCkeditor5EditorBundle`** (`src/Resources/translations/`). Override from your app with YAML/XLF under **`translations/`** using the same domain, e.g. `translations/NowoCkeditor5EditorBundle.en.yaml`.

## Parameters exposed to the container

The DI extension sets `nowo_ckeditor5_editor.default_profile` and `nowo_ckeditor5_editor.profiles`, plus legacy aliases `nowo_ckeditor5_editor.default_config` / `nowo_ckeditor5_editor.configs` (same values), and backward-compatible scalars for the **default** profile. Prefer resolving behaviour through `Ckeditor5EditorType` and YAML profiles.
