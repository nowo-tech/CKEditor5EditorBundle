# Configuration reference

Root key: `nowo_ckeditor5_editor`

## Top-level

| Option           | Type   | Default   | Description |
| ---------------- | ------ | --------- | ----------- |
| `default_config` | string | `default` | Profile name when a form field omits the `config` option. **Must** exist under `configs`. |
| `configs`        | map    | —         | Named profiles. At least one profile is required (or use [legacy flat](#legacy-flat-yaml) input). |

## Per profile (`configs.<name>`)

| Option        | Type   | Default                     | Description |
| ------------- | ------ | --------------------------- | ----------- |
| `toolbar`     | bool   | `true`                      | Show the CKEditor toolbar (preset still controls available plugins). |
| `min_height`  | string | `240px`                     | CSS min-height of the editable region. |
| `form_theme`  | string | `form_div_layout.html.twig` | Base Symfony form theme; must align with your app `twig.form_themes`. |
| `debug`       | bool   | `false`                     | Verbose browser console logging from the bundle script. |
| `preset`      | string | —                           | Build preset: `standard`, `simple`, `minimal`, `emoji`, `typography`, `variables`, … (see `EditorPreset`). |
| `theme`       | string | `light`                     | Chrome palette: `light`, `dark`, or `auto`. |
| `upload_url`  | string | `null`                      | Optional URL for Simple Upload Adapter (demo/apps provide POST endpoints). |

## Legacy flat YAML

If the root has no `configs` key, the extension maps flat keys into a single profile under `configs` using `default_config` (default `default`):

- `toolbar`, `min_height`, `form_theme`, `debug`, `preset`, `theme`, `upload_url`

## Form type `Ckeditor5EditorType` options

| Option          | Type | Description |
| --------------- | ---- | ----------- |
| `config`        | `string\|null` | Profile name under `nowo_ckeditor5_editor.configs`. `null`/empty uses `default_config`. |
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

The DI extension sets parameters from configuration (including backward-compatible scalars for the **default** profile). Prefer resolving behaviour through `Ckeditor5EditorType` and YAML profiles.
