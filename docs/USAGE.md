# Usage

## Form type

```php
use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;

$builder->add('body', Ckeditor5EditorType::class, [
    'label' => 'Content',
    'config' => 'simple',
    'editor_config' => [
        'preset' => 'standard',
        'min_height' => 'min(40vh, 560px)',
    ],
]);
```

Submitted data is an **HTML string** (store in `TEXT` / `LONGTEXT` / similar).

## Frontend script

Include the compiled widget **once** per page (after your layout base):

```twig
<script src="{{ asset(nowo_ckeditor5_editor_asset_path('ckeditor5-editor.js'), nowo_ckeditor5_editor_asset_package()) }}"></script>
```

The script mounts CKEditor 5 on fields rendered by the bundle form theme.

## Presets

YAML **`preset`** selects which OSS CKEditor build variant is used (`standard`, `simple`, `minimal`, `emoji`, `typography`, `variables`, …). See the FrankenPHP demo under `demo/symfony8` for live examples (themes, heights, upload URL).

## Uploads (optional)

If `upload_url` is set in the profile or merged via `editor_config`, the widget may send multipart uploads with CSRF (`Ckeditor5EditorType::CSRF_UPLOAD_TOKEN_ID`). Your application must expose a compatible endpoint (see demo controllers for reference).
