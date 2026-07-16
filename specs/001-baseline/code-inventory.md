# Code inventory — 100% traceability

**Baseline spec**: [`spec.md`](spec.md)  
**Package**: `nowo-tech/ckeditor5-editor-bundle`  
**Last audited**: 2026-07-07

## PHP classes (`src/**/*.php`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `NowoCkeditor5EditorBundle.php` | Bundle entry | FR-BUNDLE-001 |
| `EditorPreset.php` | Editor preset enum | FR-PRESET-001 |
| `DependencyInjection/Configuration.php` | Config tree | FR-CFG-001 |
| `DependencyInjection/NowoCkeditor5EditorExtension.php` | DI extension | FR-CFG-002 |
| `DependencyInjection/Compiler/TwigPathsPass.php` | Twig namespace path | FR-TWIG-001 |
| `Form/Ckeditor5EditorType.php` | Symfony form type | FR-FORM-001 |
| `Twig/NowoCkeditor5EditorTwigExtension.php` | Form theme helpers | FR-TWIG-002 |

## TypeScript production (`src/Resources/assets/src/`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/assets/src/ckeditor5-editor.ts` | CKEditor 5 initializer | FR-UI-001 |
| `Resources/assets/src/logger.ts` | Client logger | FR-UI-002 |

## TypeScript tests (`src/Resources/assets/src/*.test.ts`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/assets/src/logger.test.ts` | Vitest: logger | FR-TEST-TS-001 |

## Symfony config & build output

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/config/services.yaml` | Service wiring | FR-DI-001 |
| `Resources/public/ckeditor5-editor.js` | Vite build output | FR-BUILD-001 |

## Translations (`src/Resources/translations/`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/translations/NowoCkeditor5EditorBundle.de.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.en.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.es.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.fr.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.it.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.nl.yaml` | UI strings | FR-I18N-001 |
| `Resources/translations/NowoCkeditor5EditorBundle.pt.yaml` | UI strings | FR-I18N-001 |

## Twig form themes (`src/Resources/views/Form/`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Resources/views/Form/ckeditor5_editor_theme.html.twig` | Default form theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap3.html.twig` | Bootstrap 3 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap3_horizontal.html.twig` | Bootstrap 3 horizontal | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap4.html.twig` | Bootstrap 4 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap4_horizontal.html.twig` | Bootstrap 4 horizontal | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap5.html.twig` | Bootstrap 5 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_bootstrap5_horizontal.html.twig` | Bootstrap 5 horizontal | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_foundation5.html.twig` | Foundation 5 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_foundation6.html.twig` | Foundation 6 theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_table.html.twig` | Table layout theme | FR-TWIG-003 |
| `Resources/views/Form/ckeditor5_editor_theme_tailwind2.html.twig` | Tailwind 2 theme | FR-TWIG-003 |

## Coverage summary

| Category | Files | Mapped |
| --- | ---: | ---: |
| PHP classes | 7 | 7 |
| TypeScript production | 2 | 2 |
| TypeScript tests | 1 | 1 |
| Config & build | 2 | 2 |
| Translations | 7 | 7 |
| Twig themes | 11 | 11 |
| **Total production sources** | **30** | **30** |
