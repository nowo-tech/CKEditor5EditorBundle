/**
 * CKEditor 5 (GPL OSS plugins): mounts classic editors on `[data-ckeditor5-root]`, syncs HTML to Symfony textarea fields.
 * Presets mirror bundle YAML `preset`: standard | simple | minimal | emoji | typography | variables.
 */

import 'ckeditor5/ckeditor5.css';

import {
  Alignment,
  Autoformat,
  BlockQuote,
  Bold,
  ClassicEditor,
  Code,
  CodeBlock,
  Essentials,
  Heading,
  Emoji,
  FontFamily,
  FontSize,
  HorizontalLine,
  Image,
  ImageCaption,
  ImageInsert,
  ImageStyle,
  ImageToolbar,
  Indent,
  IndentBlock,
  Italic,
  Link,
  LinkImage,
  List,
  Mention,
  Paragraph,
  PasteFromOffice,
  RemoveFormat,
  SimpleUploadAdapter,
  Strikethrough,
  Table,
  TableCaption,
  TableToolbar,
  TodoList,
  Underline,
} from 'ckeditor5';

import type { EditorConfig } from 'ckeditor5';

import { createBundleLogger } from './logger';

declare const __CKEDITOR5_EDITOR_BUILD_TIME__: string;

const log = createBundleLogger('ckeditor5-editor', {
  buildTime: typeof __CKEDITOR5_EDITOR_BUILD_TIME__ !== 'undefined' ? __CKEDITOR5_EDITOR_BUILD_TIME__ : undefined,
});
log.scriptLoaded();

const ROOT_SELECTOR = '[data-ckeditor5-root="1"]';

export type EditorPreset =
  | 'standard'
  | 'simple'
  | 'minimal'
  | 'emoji'
  | 'typography'
  | 'variables';

const PRESET_SET = new Set<string>(['standard', 'simple', 'minimal', 'emoji', 'typography', 'variables']);

function parseBool(value: string | undefined): boolean {
  return value === '1' || value === 'true';
}

function parsePreset(raw: string | undefined): EditorPreset {
  const v = (raw ?? 'standard').toLowerCase();
  return PRESET_SET.has(v) ? (v as EditorPreset) : 'standard';
}

function syncTextarea(textarea: HTMLTextAreaElement, html: string): void {
  textarea.value = html;
  textarea.dispatchEvent(new Event('input', { bubbles: true }));
  textarea.dispatchEvent(new Event('change', { bubbles: true }));
}

const DEFAULT_MIN_HEIGHT = '240px';

/** Empty YAML/Twig min_height yields ""; CSS var(--x, fallback) fails when --x is blank — normalize always. */
function normalizeMinHeight(raw: string | undefined): string {
  const t = raw?.trim() ?? '';

  return t !== '' ? t : DEFAULT_MIN_HEIGHT;
}

/** Applies YAML min_height on real CK DOM (classic layout can collapse with CSS-only rules). */
function applyMinHeightToClassicEditor(editor: ClassicEditor, minHeight: string): void {
  const el = editor.ui.getEditableElement();
  if (el instanceof HTMLElement) {
    el.style.setProperty('min-height', minHeight, 'important');
  }

  const main = el?.closest('.ck-editor__main');
  if (main instanceof HTMLElement) {
    main.style.setProperty('min-height', minHeight, 'important');
  }
}

function pluginsForPreset(preset: EditorPreset): NonNullable<EditorConfig['plugins']> {
  const minimal = [Essentials, Paragraph, Bold, Italic, Link, List];
  if (preset === 'minimal') {
    return minimal;
  }

  const simpleExtra = [
    Underline,
    Heading,
    BlockQuote,
    Code,
    CodeBlock,
    HorizontalLine,
    Autoformat,
    PasteFromOffice,
  ];

  if (preset === 'simple') {
    return [...minimal, ...simpleExtra];
  }

  if (preset === 'emoji') {
    return [...minimal, ...simpleExtra, Mention, Emoji];
  }

  if (preset === 'variables') {
    return [...minimal, ...simpleExtra, Mention];
  }

  if (preset === 'typography') {
    return [...minimal, ...simpleExtra, FontFamily, FontSize, RemoveFormat];
  }

  return [
    ...minimal,
    ...simpleExtra,
    Strikethrough,
    TodoList,
    Indent,
    IndentBlock,
    Alignment,
    RemoveFormat,
    Image,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageInsert,
    LinkImage,
    Table,
    TableToolbar,
    TableCaption,
  ];
}

function toolbarItemsForPreset(preset: EditorPreset, toolbarEnabled: boolean): EditorConfig['toolbar'] {
  if (!toolbarEnabled) {
    return { items: [] };
  }

  if (preset === 'minimal') {
    return {
      shouldNotGroupWhenFull: true,
      items: ['bold', 'italic', '|', 'link', 'bulletedList', 'numberedList'],
    };
  }

  if (preset === 'emoji') {
    return {
      shouldNotGroupWhenFull: true,
      items: [
        'undo',
        'redo',
        '|',
        'heading',
        '|',
        'bold',
        'italic',
        'underline',
        '|',
        'link',
        'emoji',
        '|',
        'bulletedList',
        'numberedList',
        '|',
        'blockQuote',
        'code',
        'codeBlock',
        'horizontalLine',
      ],
    };
  }

  if (preset === 'variables') {
    return {
      shouldNotGroupWhenFull: true,
      items: [
        'undo',
        'redo',
        '|',
        'heading',
        '|',
        'bold',
        'italic',
        'underline',
        '|',
        'link',
        '|',
        'bulletedList',
        'numberedList',
        '|',
        'blockQuote',
        'code',
        'codeBlock',
        'horizontalLine',
      ],
    };
  }

  if (preset === 'typography') {
    return {
      shouldNotGroupWhenFull: true,
      items: [
        'undo',
        'redo',
        '|',
        'heading',
        '|',
        'fontFamily',
        'fontSize',
        '|',
        'bold',
        'italic',
        'underline',
        '|',
        'link',
        '|',
        'bulletedList',
        'numberedList',
        '|',
        'blockQuote',
        '|',
        'removeFormat',
      ],
    };
  }

  if (preset === 'simple') {
    return {
      shouldNotGroupWhenFull: true,
      items: [
        'undo',
        'redo',
        '|',
        'heading',
        '|',
        'bold',
        'italic',
        'underline',
        '|',
        'link',
        'bulletedList',
        'numberedList',
        '|',
        'blockQuote',
        'code',
        'codeBlock',
        'horizontalLine',
      ],
    };
  }

  return {
    shouldNotGroupWhenFull: true,
    items: [
      'undo',
      'redo',
      '|',
      'heading',
      '|',
      'bold',
      'italic',
      'underline',
      'strikethrough',
      '|',
      'link',
      'insertImage',
      'insertTable',
      'blockQuote',
      'codeBlock',
      '|',
      'alignment',
      '|',
      'bulletedList',
      'numberedList',
      'todoList',
      'outdent',
      'indent',
      '|',
      'removeFormat',
    ],
  };
}

function buildConfig(
  preset: EditorPreset,
  toolbarEnabled: boolean,
  placeholder: string,
  upload?: { url: string; csrf?: string },
): EditorConfig {
  const placeholderCfg = placeholder.trim() === '' ? undefined : placeholder;

  let pluginList = pluginsForPreset(preset);
  if (preset === 'standard' && upload?.url) {
    pluginList = [...pluginList, SimpleUploadAdapter];
  }

  const config: EditorConfig = {
    // Required from CKEditor 5.44+ for self-hosted npm builds under GPL-2+ (OSS plugins only).
    licenseKey: 'GPL',
    plugins: pluginList,
    toolbar: toolbarItemsForPreset(preset, toolbarEnabled),
    placeholder: placeholderCfg,
    heading: {
      options: [
        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
      ],
    },
    image: {
      toolbar: ['imageTextAlternative', 'toggleImageCaption', '|', 'linkImage'],
    },
    table: {
      contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
    },
    ui: {
      viewportOffset: { top: 0, bottom: 0, left: 0, right: 0 },
    },
  };

  if (preset === 'variables') {
    config.mention = {
      feeds: [
        {
          marker: '@',
          minimumCharacters: 0,
          feed: [
            { id: '@first_name', text: 'First name' },
            { id: '@last_name', text: 'Last name' },
            { id: '@email', text: 'Email' },
            { id: '@company', text: 'Company' },
            { id: '@policy_number', text: 'Policy number' },
            { id: '@current_date', text: 'Current date' },
          ],
        },
      ],
    };
  }

  if (preset === 'typography') {
    config.fontFamily = {
      options: [
        'default',
        'Georgia, "Times New Roman", Times, serif',
        'system-ui, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
        '"Courier New", Courier, monospace',
      ],
    };
    config.fontSize = {
      options: [10, 11, 12, 'default', 14, 16, 18, 22, 28],
    };
  }

  if (preset === 'standard' && upload?.url) {
    const u = upload.url.trim();
    const c = upload.csrf?.trim();
    config.simpleUpload = {
      uploadUrl: u,
      ...(c ? { headers: { 'X-CSRF-TOKEN': c } } : {}),
    };
  }

  return config;
}

/** Applies light/dark classes from `data-ckeditor5-theme-value` (handles auto + OS changes). */
export function applyChromeTheme(root: HTMLElement): void {
  const mode = (root.dataset.ckeditor5ThemeValue ?? 'light').toLowerCase();
  root.classList.remove('ckeditor5-theme-light', 'ckeditor5-theme-dark', 'ckeditor5-theme-auto');

  let effective: 'light' | 'dark';
  if (mode === 'auto') {
    root.classList.add('ckeditor5-theme-auto');
    effective = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  } else if (mode === 'dark') {
    effective = 'dark';
  } else {
    effective = 'light';
  }

  root.classList.add(effective === 'dark' ? 'ckeditor5-theme-dark' : 'ckeditor5-theme-light');

  if (mode === 'auto' && root.dataset.ckeditor5ThemeAutoBound !== '1') {
    root.dataset.ckeditor5ThemeAutoBound = '1';
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => applyChromeTheme(root));
  }
}

/**
 * Initializes one widget root (textarea + mount).
 */
export async function initCkeditor5Root(root: HTMLElement): Promise<void> {
  if (root.dataset.ckeditor5Initialized === '1') {
    return;
  }

  const textarea = root.querySelector('textarea');
  const mount = root.querySelector<HTMLElement>('[data-ckeditor5-mount]');
  if (!(textarea instanceof HTMLTextAreaElement) || !mount) {
    log.warn('skipped: textarea or mount missing');
    return;
  }

  const preset = parsePreset(root.dataset.ckeditor5PresetValue);
  applyChromeTheme(root);
  root.classList.add(`ckeditor5-preset-${preset}`);

  const debug = parseBool(root.dataset.ckeditor5DebugValue);
  log.setDebug(debug);

  const toolbar = parseBool(root.dataset.ckeditor5ToolbarValue);
  const minHeight = normalizeMinHeight(root.dataset.ckeditor5MinHeightValue);
  root.style.setProperty('--ckeditor5-min-height', minHeight);

  const placeholder = root.dataset.ckeditor5PlaceholderValue ?? '';

  const uploadUrl = root.dataset.ckeditor5UploadUrlValue?.trim();
  const uploadCsrf = root.dataset.ckeditor5UploadCsrfValue?.trim();
  const upload =
    uploadUrl && uploadUrl !== '' ? { url: uploadUrl, csrf: uploadCsrf || undefined } : undefined;

  const config = buildConfig(preset, toolbar, placeholder, upload);

  const editor = await ClassicEditor.create(mount, config);

  applyMinHeightToClassicEditor(editor, minHeight);

  editor.setData(textarea.value || '');
  syncTextarea(textarea, editor.getData());

  editor.model.document.on('change:data', () => {
    syncTextarea(textarea, editor.getData());
  });

  root.dataset.ckeditor5Initialized = '1';
}

function discoverRoots(doc: Document | HTMLElement): HTMLElement[] {
  return Array.from(doc.querySelectorAll<HTMLElement>(ROOT_SELECTOR));
}

export async function runInit(): Promise<void> {
  const roots = discoverRoots(document);
  for (const r of roots) {
    await initCkeditor5Root(r);
  }
}

export function runInitAndObserve(): void {
  void runInit().then(() => {
    const observer = new MutationObserver(() => {
      void runInit();
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
  });
}

if (typeof window !== 'undefined') {
  (
    window as unknown as {
      NowoCkeditor5Editor?: {
        initCkeditor5Root: typeof initCkeditor5Root;
        applyChromeTheme: typeof applyChromeTheme;
        runInit: typeof runInit;
        runInitAndObserve: typeof runInitAndObserve;
      };
    }
  ).NowoCkeditor5Editor = {
    initCkeditor5Root,
    applyChromeTheme,
    runInit,
    runInitAndObserve,
  };
}

if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runInitAndObserve);
  } else {
    runInitAndObserve();
  }
}
