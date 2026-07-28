# Feature Specification: Ckeditor5EditorBundle baseline (100% code coverage)

**Feature Branch**: `001-baseline`  
**Created**: 2026-07-07  
**Status**: Active  

**Package**: `nowo-tech/ckeditor5-editor-bundle`  
**Configuration root**: `nowo_ckeditor5_editor`  
**Code inventory**: [`code-inventory.md`](code-inventory.md)

---

## Summary

Symfony form type and Twig themes that embed **CKEditor 5** with configurable presets, multi-framework form themes (Bootstrap 3–5, Foundation, Tailwind, table layout), and a TypeScript initializer built via Vite.

---

## User Scenarios

### US-01 — Rich text form field (P1)

**Given** a Symfony form uses `Ckeditor5EditorType`, **When** the page renders, **Then** CKEditor 5 loads with the configured preset and toolbar.

### US-02 — Theme compatibility (P2)

**Given** the application uses Bootstrap or Tailwind form themes, **When** integrator selects the matching bundle Twig theme, **Then** editor markup aligns with the parent form layout.

### US-03 — i18n (P2)

**Given** bundle translation files, **When** locale switches, **Then** editor UI strings follow Symfony translator domain `NowoCkeditor5EditorBundle`.

---

## Requirements

- **FR-BUNDLE-001**: `NowoCkeditor5EditorBundle` + alias `nowo_ckeditor5_editor`.
- **FR-CFG-001 / FR-CFG-002**: Editor defaults, presets, and DI wiring.
- **FR-FORM-001**: `Ckeditor5EditorType` exposes options documented in [`docs/USAGE.md`](../../docs/USAGE.md).
- **FR-TWIG-001–003**: Twig path pass, extension, and form theme variants.
- **FR-UI-001 / FR-UI-002**: TypeScript initializer and logger under `Resources/assets/src/`.
- **FR-BUILD-001**: `ckeditor5-editor.js` build output consumed by form themes.
- **FR-I18N-001**: Seven locale YAML files with key parity.
- **FR-TEST-TS-001**: Vitest coverage for client logger.

---

## Success Criteria

- **SC-001**: **31/31** production files mapped in inventory.
- **SC-002**: Config keys match `Configuration.php` and [`docs/CONFIGURATION.md`](../../docs/CONFIGURATION.md).
- **SC-003**: `composer qa` passes; Vitest runs when TS changes.

---

## Validation

`composer qa`, `pnpm test` (when applicable), PHPUnit, PHPStan.
