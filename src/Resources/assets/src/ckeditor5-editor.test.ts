/**
 * Unit tests for the CKEditor 5 build entry (presets, config, theme, textarea sync, auto-init and observer).
 *
 * `ckeditor5` is replaced by a lightweight double: these tests cover the bundle's own glue code, not CKEditor.
 * The module boots on import, so every test re-imports it after preparing the DOM.
 */

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

type ChangeListener = () => void;

/** Shared state for the `ckeditor5` double (survives `vi.resetModules()`). */
const ck = vi.hoisted(() => {
  class FakeEditor {
    data = '';
    readonly listeners: ChangeListener[] = [];
    readonly ui: { getEditableElement: () => Element | null };
    readonly model = {
      document: {
        on: (event: string, cb: ChangeListener): void => {
          if (event === 'change:data') {
            this.listeners.push(cb);
          }
        },
      },
    };

    constructor(
      readonly mount: HTMLElement,
      readonly config: Record<string, unknown>,
      editable: Element | null,
    ) {
      this.ui = { getEditableElement: () => editable };
    }

    setData(html: string): void {
      this.data = html;
    }

    getData(): string {
      return this.data;
    }

    /** Simulates a user edit. */
    type(html: string): void {
      this.data = html;
      this.listeners.forEach((cb) => cb());
    }
  }

  const state = {
    editors: [] as FakeEditor[],
    /** What `ui.getEditableElement()` returns: inside `.ck-editor__main` (default), bare element, or null. */
    editable: 'main' as 'main' | 'bare' | 'none',
    create: async (mount: HTMLElement, config: Record<string, unknown>): Promise<FakeEditor> => {
      let editable: Element | null = null;
      if (state.editable === 'main') {
        mount.innerHTML = '<div class="ck-editor__main"><div class="ck-editor__editable"></div></div>';
        editable = mount.querySelector('.ck-editor__editable');
      } else if (state.editable === 'bare') {
        editable = document.createElement('div');
      }
      const editor = new FakeEditor(mount, config, editable);
      state.editors.push(editor);
      return editor;
    },
  };

  return state;
});

vi.mock('ckeditor5', () => {
  const names = [
    'Alignment', 'Autoformat', 'BlockQuote', 'Bold', 'Code', 'CodeBlock', 'Essentials', 'Heading', 'Emoji',
    'FontFamily', 'FontSize', 'HorizontalLine', 'Image', 'ImageCaption', 'ImageInsert', 'ImageStyle', 'ImageToolbar',
    'Indent', 'IndentBlock', 'Italic', 'Link', 'LinkImage', 'List', 'Mention', 'Paragraph', 'PasteFromOffice',
    'RemoveFormat', 'SimpleUploadAdapter', 'Strikethrough', 'Table', 'TableCaption', 'TableToolbar', 'TodoList',
    'Underline',
  ];
  const mod: Record<string, unknown> = {
    ClassicEditor: { create: (mount: HTMLElement, config: Record<string, unknown>) => ck.create(mount, config) },
  };
  for (const name of names) {
    mod[name] = { pluginName: name };
  }

  return mod;
});
vi.mock('ckeditor5/ckeditor5.css', () => ({}));
vi.mock('./ckeditor5-editor-widget.css', () => ({}));

type EditorModule = typeof import('./ckeditor5-editor');
type Config = {
  plugins: { pluginName: string }[];
  toolbar: { items: string[]; shouldNotGroupWhenFull?: boolean };
  placeholder?: string;
  licenseKey: string;
  emoji?: { definitionsUrl: string };
  mention?: { feeds: { marker: string; feed: { id: string }[] }[] };
  fontFamily?: { options: string[] };
  fontSize?: { options: (number | string)[] };
  simpleUpload?: { uploadUrl: string; headers?: Record<string, string> };
};

/** Tracks MutationObservers created by the module so each test can disconnect them. */
const observers: MutationObserver[] = [];
const RealMutationObserver = globalThis.MutationObserver;
class TrackedMutationObserver extends RealMutationObserver {
  constructor(cb: MutationCallback) {
    super(cb);
    observers.push(this);
  }
}

type MediaListener = () => void;
let darkScheme = false;
const mediaListeners: MediaListener[] = [];

const tick = (): Promise<void> => new Promise((r) => setTimeout(r, 5));

function widget(attrs: Record<string, string> = {}, value = ''): HTMLElement {
  const root = document.createElement('div');
  root.setAttribute('data-ckeditor5-root', '1');
  for (const [k, v] of Object.entries(attrs)) {
    root.setAttribute(`data-ckeditor5-${k}`, v);
  }
  const textarea = document.createElement('textarea');
  textarea.value = value;
  const mount = document.createElement('div');
  mount.setAttribute('data-ckeditor5-mount', '');
  root.append(textarea, mount);
  document.body.appendChild(root);

  return root;
}

/** Imports a fresh copy of the module (runs the boot code). Auto-observe is off unless `observe` is true. */
async function load(observe = false): Promise<EditorModule> {
  if (!observe) {
    document.documentElement.setAttribute('data-ckeditor5-auto-observe', '0');
  }
  vi.resetModules();
  const mod = await import('./ckeditor5-editor');
  await tick();

  return mod;
}

function lastConfig(): Config {
  return ck.editors[ck.editors.length - 1].config as unknown as Config;
}

function pluginNames(config: Config): string[] {
  return config.plugins.map((p) => p.pluginName);
}

beforeEach(() => {
  ck.editors = [];
  ck.editable = 'main';
  darkScheme = false;
  mediaListeners.length = 0;
  vi.stubGlobal('MutationObserver', TrackedMutationObserver);
  vi.stubGlobal(
    'matchMedia',
    vi.fn(() => ({
      get matches() {
        return darkScheme;
      },
      addEventListener: (_: string, cb: MediaListener) => mediaListeners.push(cb),
    })),
  );
  vi.spyOn(console, 'log').mockImplementation(() => undefined);
  vi.spyOn(console, 'warn').mockImplementation(() => undefined);
  vi.spyOn(console, 'debug').mockImplementation(() => undefined);
});

afterEach(() => {
  observers.splice(0).forEach((o) => o.disconnect());
  document.body.innerHTML = '';
  document.head.innerHTML = '';
  document.documentElement.removeAttribute('data-ckeditor5-auto-observe');
  delete (window as unknown as { NowoCkeditor5Editor?: unknown }).NowoCkeditor5Editor;
  vi.unstubAllGlobals();
  vi.restoreAllMocks();
});

describe('boot', () => {
  it('initializes existing roots on import and exposes the public API on window', async () => {
    widget({}, '<p>Hello</p>');
    const mod = await load();

    expect(ck.editors).toHaveLength(1);
    const api = (window as unknown as { NowoCkeditor5Editor: Record<string, unknown> }).NowoCkeditor5Editor;
    expect(api.initCkeditor5Root).toBe(mod.initCkeditor5Root);
    expect(api.applyChromeTheme).toBe(mod.applyChromeTheme);
    expect(api.runInit).toBe(mod.runInit);
    expect(api.runInitAndObserve).toBe(mod.runInitAndObserve);
    expect(api.isAutoObserveEnabled).toBe(mod.isAutoObserveEnabled);
    expect(observers).toHaveLength(0);
  });

  it('waits for DOMContentLoaded while the document is loading', async () => {
    widget();
    Object.defineProperty(document, 'readyState', { value: 'loading', configurable: true });
    try {
      await load();
      expect(ck.editors).toHaveLength(0);
      document.dispatchEvent(new Event('DOMContentLoaded'));
      await tick();
      expect(ck.editors).toHaveLength(1);
    } finally {
      delete (document as unknown as { readyState?: string }).readyState;
    }
  });

  it('skips the window API when there is no window object', async () => {
    vi.stubGlobal('window', undefined);
    widget();
    await load();
    vi.unstubAllGlobals();
    expect((window as unknown as { NowoCkeditor5Editor?: unknown }).NowoCkeditor5Editor).toBeUndefined();
    expect(ck.editors).toHaveLength(1);
  });

  it('passes the build time to the logger when the Vite define is present', async () => {
    vi.stubGlobal('__CKEDITOR5_EDITOR_BUILD_TIME__', '2026-10-30T00:00:00Z');
    await load();
    expect(console.log).toHaveBeenCalledWith(
      expect.stringContaining('2026-10-30T00:00:00Z'),
      expect.anything(),
      expect.anything(),
    );
  });
});

describe('initCkeditor5Root', () => {
  it('syncs the textarea on creation and on every change', async () => {
    const mod = await load();
    const root = widget({ 'toolbar-value': '1' }, '<p>Initial</p>');
    const textarea = root.querySelector('textarea')!;
    const events: string[] = [];
    textarea.addEventListener('input', () => events.push('input'));
    textarea.addEventListener('change', () => events.push('change'));

    await mod.initCkeditor5Root(root);

    expect(textarea.value).toBe('<p>Initial</p>');
    expect(events).toEqual(['input', 'change']);
    ck.editors[0].type('<p>Edited</p>');
    expect(textarea.value).toBe('<p>Edited</p>');
    expect(events).toHaveLength(4);
    expect(root.dataset.ckeditor5Initialized).toBe('1');
    expect(root.dataset.ckeditor5Mounting).toBeUndefined();
  });

  it('starts from an empty string when the textarea is empty', async () => {
    const mod = await load();
    const root = widget();
    await mod.initCkeditor5Root(root);
    expect(ck.editors[0].getData()).toBe('');
  });

  it('never mounts twice (initialized or concurrent mount)', async () => {
    const mod = await load();
    const root = widget();
    await Promise.all([mod.initCkeditor5Root(root), mod.initCkeditor5Root(root)]);
    await mod.initCkeditor5Root(root);
    expect(ck.editors).toHaveLength(1);
  });

  it('skips roots without textarea or mount (warning once debug is on)', async () => {
    const mod = await load();
    await mod.initCkeditor5Root(widget({ 'debug-value': '1' }));
    const noMount = widget();
    noMount.querySelector('[data-ckeditor5-mount]')!.remove();
    const noTextarea = widget();
    noTextarea.querySelector('textarea')!.remove();

    await mod.initCkeditor5Root(noMount);
    await mod.initCkeditor5Root(noTextarea);

    expect(ck.editors).toHaveLength(1);
    expect(console.warn).toHaveBeenCalledTimes(2);
    expect(noMount.dataset.ckeditor5Mounting).toBeUndefined();
    expect(noMount.dataset.ckeditor5Initialized).toBeUndefined();
  });

  it('releases the mounting lock when the editor fails to start', async () => {
    const mod = await load();
    const root = widget();
    const create = vi.spyOn(ck, 'create').mockRejectedValueOnce(new Error('boom'));

    await expect(mod.initCkeditor5Root(root)).rejects.toThrow('boom');
    expect(root.dataset.ckeditor5Mounting).toBeUndefined();
    expect(root.dataset.ckeditor5Initialized).toBeUndefined();
    create.mockRestore();
  });

  it('applies the min height to the root, the editable and .ck-editor__main', async () => {
    const mod = await load();
    const root = widget({ 'min-height-value': ' 400px ' });
    await mod.initCkeditor5Root(root);

    expect(root.style.getPropertyValue('--ckeditor5-min-height')).toBe('400px');
    const main = root.querySelector<HTMLElement>('.ck-editor__main')!;
    const editable = root.querySelector<HTMLElement>('.ck-editor__editable')!;
    expect(main.style.getPropertyValue('min-height')).toBe('400px');
    expect(editable.style.getPropertyValue('min-height')).toBe('400px');
  });

  it('falls back to the default min height for blank or missing values', async () => {
    const mod = await load();
    const blank = widget({ 'min-height-value': '   ' });
    const missing = widget();
    await mod.initCkeditor5Root(blank);
    await mod.initCkeditor5Root(missing);

    expect(blank.style.getPropertyValue('--ckeditor5-min-height')).toBe('240px');
    expect(missing.style.getPropertyValue('--ckeditor5-min-height')).toBe('240px');
  });

  it('tolerates an editable outside .ck-editor__main and a missing editable', async () => {
    const mod = await load();
    ck.editable = 'bare';
    const bare = widget();
    await mod.initCkeditor5Root(bare);
    expect((ck.editors[0].ui.getEditableElement() as HTMLElement).style.getPropertyValue('min-height')).toBe('240px');

    ck.editable = 'none';
    const none = widget();
    await mod.initCkeditor5Root(none);
    expect(none.dataset.ckeditor5Initialized).toBe('1');
  });

  it('enables debug logging from the dataset', async () => {
    const mod = await load();
    const root = widget({ 'debug-value': 'true' });
    await mod.initCkeditor5Root(root);
    expect(root.dataset.ckeditor5Initialized).toBe('1');
  });
});

describe('editor config', () => {
  async function configFor(attrs: Record<string, string>): Promise<{ config: Config; root: HTMLElement }> {
    const mod = await load();
    const root = widget(attrs);
    await mod.initCkeditor5Root(root);

    return { config: lastConfig(), root };
  }

  it('defaults to the standard preset (missing or unknown values, case-insensitive)', async () => {
    for (const preset of [undefined, 'nope', 'STANDARD']) {
      const { config, root } = await configFor(preset === undefined ? { 'toolbar-value': '1' } : { 'preset-value': preset, 'toolbar-value': '1' });
      expect(root.classList.contains('ckeditor5-preset-standard')).toBe(true);
      expect(config.licenseKey).toBe('GPL');
      expect(pluginNames(config)).toEqual(expect.arrayContaining(['Image', 'Table', 'TodoList', 'Alignment']));
      expect(pluginNames(config)).not.toContain('SimpleUploadAdapter');
      expect(config.toolbar.items).toEqual(expect.arrayContaining(['insertImage', 'insertTable', 'todoList']));
      expect(config.simpleUpload).toBeUndefined();
    }
  });

  it('builds the minimal preset', async () => {
    const { config } = await configFor({ 'preset-value': 'minimal', 'toolbar-value': '1' });
    expect(pluginNames(config)).toEqual(['Essentials', 'Paragraph', 'Bold', 'Italic', 'Link', 'List']);
    expect(config.toolbar.items).toEqual(['bold', 'italic', '|', 'link', 'bulletedList', 'numberedList']);
  });

  it('builds the simple preset', async () => {
    const { config } = await configFor({ 'preset-value': 'simple', 'toolbar-value': '1' });
    expect(pluginNames(config)).toEqual(expect.arrayContaining(['Underline', 'Heading', 'CodeBlock', 'PasteFromOffice']));
    expect(pluginNames(config)).not.toContain('Mention');
    expect(config.toolbar.items).toContain('horizontalLine');
    expect(config.toolbar.items).not.toContain('emoji');
  });

  it('builds the emoji preset with a self-hosted dataset resolved from the page URL', async () => {
    const { config } = await configFor({ 'preset-value': 'emoji', 'toolbar-value': '1' });
    expect(pluginNames(config)).toEqual(expect.arrayContaining(['Mention', 'Emoji']));
    expect(config.toolbar.items).toContain('emoji');
    expect(config.emoji?.definitionsUrl).toBe(new URL('emoji/16/en.json', location.href).href);
  });

  it('resolves the emoji dataset next to the loaded build script', async () => {
    const tag = document.createElement('script');
    tag.src = 'https://cdn.example.test/bundles/nowockeditor5editor/ckeditor5-editor.js?v=1';
    document.head.appendChild(tag);
    const { config } = await configFor({ 'preset-value': 'emoji' });
    expect(config.emoji?.definitionsUrl).toBe('https://cdn.example.test/bundles/nowockeditor5editor/emoji/16/en.json');
  });

  it('prefers document.currentScript for the build URL', async () => {
    Object.defineProperty(document, 'currentScript', {
      value: { src: 'https://assets.example.test/build/ckeditor5-editor.js' },
      configurable: true,
    });
    try {
      const { config } = await configFor({ 'preset-value': 'emoji' });
      expect(config.emoji?.definitionsUrl).toBe('https://assets.example.test/build/emoji/16/en.json');
    } finally {
      delete (document as unknown as { currentScript?: unknown }).currentScript;
    }
  });

  it('builds the variables preset with the mention feed', async () => {
    const { config } = await configFor({ 'preset-value': 'variables', 'toolbar-value': '1' });
    expect(pluginNames(config)).toContain('Mention');
    expect(pluginNames(config)).not.toContain('Emoji');
    expect(config.toolbar.items).not.toContain('emoji');
    expect(config.mention?.feeds[0].marker).toBe('@');
    expect(config.mention?.feeds[0].feed.map((f) => f.id)).toContain('@email');
  });

  it('builds the typography preset with font options', async () => {
    const { config } = await configFor({ 'preset-value': 'typography', 'toolbar-value': '1' });
    expect(pluginNames(config)).toEqual(expect.arrayContaining(['FontFamily', 'FontSize', 'RemoveFormat']));
    expect(config.toolbar.items).toEqual(expect.arrayContaining(['fontFamily', 'fontSize', 'removeFormat']));
    expect(config.fontFamily?.options[0]).toBe('default');
    expect(config.fontSize?.options).toContain('default');
  });

  it('hides the toolbar when disabled', async () => {
    const { config } = await configFor({ 'preset-value': 'simple', 'toolbar-value': '0' });
    expect(config.toolbar).toEqual({ items: [] });
  });

  it('passes a non-blank placeholder only', async () => {
    expect((await configFor({ 'placeholder-value': 'Write…' })).config.placeholder).toBe('Write…');
    expect((await configFor({ 'placeholder-value': '   ' })).config.placeholder).toBeUndefined();
  });

  it('enables the upload adapter with the CSRF header on the standard preset', async () => {
    const { config } = await configFor({ 'upload-url-value': ' /upload ', 'upload-csrf-value': ' tok ' });
    expect(pluginNames(config)).toContain('SimpleUploadAdapter');
    expect(config.simpleUpload).toEqual({ uploadUrl: '/upload', headers: { 'X-CSRF-TOKEN': 'tok' } });
  });

  it('omits the CSRF header when no token is provided', async () => {
    for (const csrf of [undefined, '  ']) {
      const attrs: Record<string, string> = { 'upload-url-value': '/upload' };
      if (csrf !== undefined) {
        attrs['upload-csrf-value'] = csrf;
      }
      const { config } = await configFor(attrs);
      expect(config.simpleUpload).toEqual({ uploadUrl: '/upload' });
    }
  });

  it('ignores blank upload URLs and non-standard presets', async () => {
    expect((await configFor({ 'upload-url-value': '   ' })).config.simpleUpload).toBeUndefined();
    const simple = (await configFor({ 'preset-value': 'simple', 'upload-url-value': '/upload' })).config;
    expect(simple.simpleUpload).toBeUndefined();
    expect(pluginNames(simple)).not.toContain('SimpleUploadAdapter');
  });
});

describe('applyChromeTheme', () => {
  it('applies light by default and for unknown values, dark when requested', async () => {
    const mod = await load();
    const root = document.createElement('div');
    mod.applyChromeTheme(root);
    expect(root.className).toBe('ckeditor5-theme-light');

    root.dataset.ckeditor5ThemeValue = 'DARK';
    mod.applyChromeTheme(root);
    expect(root.className).toBe('ckeditor5-theme-dark');

    root.dataset.ckeditor5ThemeValue = 'sepia';
    mod.applyChromeTheme(root);
    expect(root.className).toBe('ckeditor5-theme-light');
  });

  it('follows the OS scheme in auto mode and binds the change listener once', async () => {
    const mod = await load();
    const root = document.createElement('div');
    root.dataset.ckeditor5ThemeValue = 'auto';

    darkScheme = true;
    mod.applyChromeTheme(root);
    expect(root.classList.contains('ckeditor5-theme-auto')).toBe(true);
    expect(root.classList.contains('ckeditor5-theme-dark')).toBe(true);
    expect(mediaListeners).toHaveLength(1);

    darkScheme = false;
    mediaListeners[0]();
    expect(root.classList.contains('ckeditor5-theme-light')).toBe(true);
    expect(root.classList.contains('ckeditor5-theme-dark')).toBe(false);
    expect(mediaListeners).toHaveLength(1);
  });
});

describe('isAutoObserveEnabled', () => {
  it('is on by default and off with "0"/"false" on <html> or <body>', async () => {
    const mod = await load();
    const doc = document.implementation.createHTMLDocument('t');
    expect(mod.isAutoObserveEnabled(doc)).toBe(true);

    doc.body.setAttribute('data-ckeditor5-auto-observe', 'false');
    expect(mod.isAutoObserveEnabled(doc)).toBe(false);

    doc.documentElement.setAttribute('data-ckeditor5-auto-observe', '1');
    expect(mod.isAutoObserveEnabled(doc)).toBe(true);

    doc.documentElement.setAttribute('data-ckeditor5-auto-observe', '0');
    expect(mod.isAutoObserveEnabled(doc)).toBe(false);
  });

  it('handles documents without a body and defaults to the global document', async () => {
    const mod = await load();
    const doc = document.implementation.createHTMLDocument('t');
    doc.body.remove();
    expect(mod.isAutoObserveEnabled(doc)).toBe(true);
    expect(mod.isAutoObserveEnabled()).toBe(false);
  });
});

describe('runInitAndObserve', () => {
  it('initializes roots added later (debounced)', async () => {
    await load(true);
    expect(observers).toHaveLength(1);

    widget();
    widget();
    await tick();
    expect(ck.editors).toHaveLength(2);

    widget();
    await tick();
    expect(ck.editors).toHaveLength(3);
  });

  it('does not observe when auto-observe is disabled', async () => {
    const mod = await load();
    mod.runInitAndObserve();
    await tick();
    expect(observers).toHaveLength(0);
  });
});
