/**
 * Unit tests for the lazy loader (visibility detection, single injection, late roots, load() escape hatch).
 */

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import {
  boot,
  CSS_ATTRIBUTE,
  createLoader,
  isRendered,
  readOptions,
  ROOT_SELECTOR,
  SRC_ATTRIBUTE,
  type Ckeditor5EditorLoader,
} from './ckeditor5-editor-loader';

const BUILD_URL = '/bundles/nowockeditor5editor/ckeditor5-editor.js?v=abc';
const CSS_URL = '/bundles/nowockeditor5editor/ckeditor5-editor.css?v=abc';

type FakeEntry = { target: Element; isIntersecting: boolean };

/** Minimal IntersectionObserver double driven by the tests. */
class FakeIntersectionObserver {
  static instances: FakeIntersectionObserver[] = [];
  readonly observed = new Set<Element>();
  constructor(private readonly callback: (entries: FakeEntry[]) => void) {
    FakeIntersectionObserver.instances.push(this);
  }
  observe(el: Element): void {
    this.observed.add(el);
  }
  unobserve(el: Element): void {
    this.observed.delete(el);
  }
  disconnect(): void {
    this.observed.clear();
  }
  fire(target: Element, isIntersecting: boolean): void {
    this.callback([{ target, isIntersecting }]);
  }
}

function root(html = ''): HTMLElement {
  const wrapper = document.createElement('div');
  wrapper.innerHTML = `<div data-ckeditor5-root="1"><textarea></textarea><div data-ckeditor5-mount></div></div>${html}`;
  return wrapper.querySelector<HTMLElement>(ROOT_SELECTOR)!;
}

function injectedScripts(): HTMLScriptElement[] {
  return Array.from(document.querySelectorAll<HTMLScriptElement>('script')).filter(
    (s) => s.getAttribute('src') === BUILD_URL,
  );
}

const tick = (): Promise<void> => new Promise((r) => setTimeout(r, 5));

describe('isRendered', () => {
  afterEach(() => {
    document.body.innerHTML = '';
  });

  it('is false for detached elements', () => {
    expect(isRendered(document.createElement('div'))).toBe(false);
  });

  it('is true for a plain visible element', () => {
    const el = root();
    document.body.appendChild(el.parentElement!);
    expect(isRendered(el)).toBe(true);
  });

  it('is false inside a closed dialog and true once open', () => {
    document.body.innerHTML = `<dialog><div data-ckeditor5-root="1"></div></dialog>`;
    const el = document.querySelector(ROOT_SELECTOR)!;
    expect(isRendered(el)).toBe(false);
    document.querySelector('dialog')!.setAttribute('open', '');
    expect(isRendered(el)).toBe(true);
  });

  it('handles details (collapsed body hidden, summary visible)', () => {
    document.body.innerHTML = `<details><summary><span id="s"></span></summary><div data-ckeditor5-root="1"></div></details>`;
    const el = document.querySelector(ROOT_SELECTOR)!;
    expect(isRendered(el)).toBe(false);
    expect(isRendered(document.getElementById('s')!)).toBe(true);
    document.querySelector('details')!.setAttribute('open', '');
    expect(isRendered(el)).toBe(true);
  });

  it('is false under hidden / display:none / content-visibility ancestors and for visibility:hidden', () => {
    document.body.innerHTML = `
      <div hidden><i id="a"></i></div>
      <div style="display:none"><i id="b"></i></div>
      <i id="c" style="visibility:hidden"></i>
      <i id="d" style="visibility:collapse"></i>
      <div id="cv"><i id="e"></i></div>`;
    const cv = document.getElementById('cv')!;
    const real = window.getComputedStyle.bind(window);
    const spy = vi
      .spyOn(window, 'getComputedStyle')
      .mockImplementation((el: Element) =>
        el === cv ? ({ display: 'block', contentVisibility: 'hidden', visibility: 'visible' } as CSSStyleDeclaration) : real(el),
      );
    for (const id of ['a', 'b', 'c', 'd', 'e']) {
      expect(isRendered(document.getElementById(id)!)).toBe(false);
    }
    spy.mockRestore();
  });
});

describe('readOptions', () => {
  afterEach(() => {
    document.head.innerHTML = '';
  });

  it('returns null src without a loader tag', () => {
    expect(readOptions(document, null)).toEqual({ src: null });
  });

  it('prefers the current script and reads its nonce', () => {
    const tag = document.createElement('script');
    tag.setAttribute(SRC_ATTRIBUTE, BUILD_URL);
    tag.setAttribute('nonce', 'n0nce');
    tag.setAttribute(CSS_ATTRIBUTE, CSS_URL);
    expect(readOptions(document, tag)).toEqual({ src: BUILD_URL, nonce: 'n0nce', css: CSS_URL });
  });

  it('falls back to querying the document (no nonce)', () => {
    const tag = document.createElement('script');
    tag.setAttribute(SRC_ATTRIBUTE, BUILD_URL);
    document.head.appendChild(tag);
    expect(readOptions(document, document.createElement('script'))).toEqual({ src: BUILD_URL, nonce: undefined, css: null });
  });
});

describe('createLoader', () => {
  let loader: Ckeditor5EditorLoader | null = null;

  beforeEach(() => {
    FakeIntersectionObserver.instances = [];
    delete (window as unknown as { NowoCkeditor5Editor?: unknown }).NowoCkeditor5Editor;
  });

  afterEach(() => {
    loader?.disconnect();
    loader = null;
    vi.unstubAllGlobals();
    document.body.innerHTML = '';
    document.head.innerHTML = '';
    vi.restoreAllMocks();
  });

  describe('without IntersectionObserver', () => {
    beforeEach(() => {
      vi.stubGlobal('IntersectionObserver', undefined);
    });

    it('injects the build at once when a root is visible', () => {
      document.body.appendChild(root().parentElement!);
      loader = createLoader(document, { src: BUILD_URL, nonce: 'abc' });
      expect(loader.isLoaded()).toBe(true);
      const scripts = injectedScripts();
      expect(scripts).toHaveLength(1);
      expect(scripts[0].getAttribute('nonce')).toBe('abc');
      expect(scripts[0].async).toBe(true);
    });

    it('links the editor stylesheet once, with the nonce, before the build', () => {
      document.body.appendChild(root().parentElement!);
      loader = createLoader(document, { src: BUILD_URL, nonce: 'abc', css: CSS_URL });
      const links = Array.from(document.querySelectorAll<HTMLLinkElement>('link[rel="stylesheet"]'));
      expect(links).toHaveLength(1);
      expect(links[0].getAttribute('href')).toBe(CSS_URL);
      expect(links[0].getAttribute('nonce')).toBe('abc');
      expect(document.head.lastElementChild?.tagName).toBe('SCRIPT');
      expect(document.querySelectorAll('style')).toHaveLength(0);
    });

    it('links the stylesheet without a nonce when the loader has none', () => {
      document.body.appendChild(root().parentElement!);
      loader = createLoader(document, { src: BUILD_URL, css: CSS_URL });
      const link = document.querySelector<HTMLLinkElement>('link[rel="stylesheet"]');
      expect(link?.getAttribute('href')).toBe(CSS_URL);
      expect(link?.hasAttribute('nonce')).toBe(false);
    });

    it('does not duplicate an already linked stylesheet', () => {
      const existing = document.createElement('link');
      existing.rel = 'stylesheet';
      existing.href = CSS_URL;
      existing.setAttribute('href', CSS_URL);
      document.head.appendChild(existing);
      document.body.appendChild(root().parentElement!);
      loader = createLoader(document, { src: BUILD_URL, css: CSS_URL });
      expect(document.querySelectorAll('link[rel="stylesheet"]')).toHaveLength(1);
      expect(injectedScripts()[0].hasAttribute('nonce')).toBe(false);
    });

    it('does nothing without roots', async () => {
      loader = createLoader(document, { src: BUILD_URL });
      await tick();
      expect(loader.isLoaded()).toBe(false);
      expect(injectedScripts()).toHaveLength(0);
    });

    it('waits until a closed dialog opens, then injects exactly once', async () => {
      document.body.innerHTML = `<dialog id="dlg"><div data-ckeditor5-root="1"></div></dialog><div data-ckeditor5-root="1" hidden></div>`;
      loader = createLoader(document, { src: BUILD_URL });
      expect(loader.isLoaded()).toBe(false);

      document.getElementById('dlg')!.setAttribute('open', '');
      document.querySelector<HTMLElement>('[hidden]')!.removeAttribute('hidden');
      await tick();
      expect(loader.isLoaded()).toBe(true);
      expect(injectedScripts()).toHaveLength(1);

      loader.refresh();
      await loader.load();
      expect(injectedScripts()).toHaveLength(1);
    });

    it('picks up roots added to the DOM later and forgets removed ones', async () => {
      document.body.innerHTML = `<dialog><div data-ckeditor5-root="1" id="gone"></div></dialog>`;
      loader = createLoader(document, { src: BUILD_URL });
      document.getElementById('gone')!.remove();
      await tick();
      expect(loader.isLoaded()).toBe(false);

      document.body.appendChild(root().parentElement!);
      await tick();
      expect(loader.isLoaded()).toBe(true);
      expect(injectedScripts()).toHaveLength(1);
    });

    it('coalesces bursts of mutations into one refresh', async () => {
      document.body.innerHTML = `<dialog><div data-ckeditor5-root="1"></div></dialog>`;
      loader = createLoader(document, { src: BUILD_URL });
      const dialog = document.querySelector('dialog')!;
      dialog.setAttribute('class', 'a');
      dialog.setAttribute('class', 'b');
      await Promise.resolve();
      dialog.setAttribute('class', 'c');
      await tick();
      expect(loader.isLoaded()).toBe(false);
      loader.disconnect();
    });

    it('disconnect() cancels a scheduled refresh', async () => {
      document.body.innerHTML = `<dialog><div data-ckeditor5-root="1"></div></dialog>`;
      loader = createLoader(document, { src: BUILD_URL });
      document.querySelector('dialog')!.setAttribute('open', '');
      await Promise.resolve();
      loader.disconnect();
      await tick();
      expect(loader.isLoaded()).toBe(false);
    });
  });

  describe('with IntersectionObserver', () => {
    beforeEach(() => {
      vi.stubGlobal('IntersectionObserver', FakeIntersectionObserver);
    });

    it('injects only when a rendered root intersects the viewport', () => {
      document.body.appendChild(root().parentElement!);
      loader = createLoader(document, { src: BUILD_URL });
      const io = FakeIntersectionObserver.instances[0];
      const el = document.querySelector(ROOT_SELECTOR)!;
      expect(io.observed.has(el)).toBe(true);
      expect(loader.isLoaded()).toBe(false);

      io.fire(el, false);
      expect(loader.isLoaded()).toBe(false);
      io.fire(el, true);
      expect(loader.isLoaded()).toBe(true);
      expect(injectedScripts()).toHaveLength(1);
      io.fire(el, true);
      expect(injectedScripts()).toHaveLength(1);
    });

    it('root inside a closed dialog loads once the dialog opens and the root intersects', async () => {
      document.body.innerHTML = `<dialog><div data-ckeditor5-root="1" style="visibility:hidden"></div></dialog>`;
      loader = createLoader(document, { src: BUILD_URL });
      const io = FakeIntersectionObserver.instances[0];
      const el = document.querySelector<HTMLElement>(ROOT_SELECTOR)!;
      io.fire(el, true);
      expect(loader.isLoaded()).toBe(false);

      document.querySelector('dialog')!.setAttribute('open', '');
      el.style.visibility = 'visible';
      await tick();
      expect(loader.isLoaded()).toBe(true);
    });

    it('unobserves roots removed from the DOM', async () => {
      document.body.appendChild(root().parentElement!);
      loader = createLoader(document, { src: BUILD_URL });
      const io = FakeIntersectionObserver.instances[0];
      const el = document.querySelector(ROOT_SELECTOR)!;
      el.remove();
      await tick();
      expect(io.observed.has(el)).toBe(false);
    });
  });

  describe('load()', () => {
    beforeEach(() => {
      vi.stubGlobal('IntersectionObserver', undefined);
    });

    it('escape hatch injects without any visible root and resolves on script load', async () => {
      loader = createLoader(document, { src: BUILD_URL });
      const promise = loader.load();
      expect(loader.load()).toBe(promise);
      const [script] = injectedScripts();
      expect(script.hasAttribute('nonce')).toBe(false);
      await expect(promise).resolves.toBeUndefined();
    });

    it('rejects when the build fails to load', async () => {
      let captured: HTMLScriptElement | null = null;
      vi.spyOn(document.head, 'appendChild').mockImplementation(<T extends Node>(node: T): T => {
        captured = node as unknown as HTMLScriptElement;
        return node;
      });
      loader = createLoader(document, { src: BUILD_URL });
      const promise = loader.load();
      captured!.dispatchEvent(new Event('error'));
      await expect(promise).rejects.toThrow('failed to load');
    });

    it('logs (does not throw) when an automatic load fails', async () => {
      const error = vi.spyOn(console, 'error').mockImplementation(() => undefined);
      let captured: HTMLScriptElement | null = null;
      vi.spyOn(document.head, 'appendChild').mockImplementation(<T extends Node>(node: T): T => {
        captured = node as unknown as HTMLScriptElement;
        return node;
      });
      document.body.appendChild(root().parentElement!);
      loader = createLoader(document, { src: BUILD_URL });
      captured!.dispatchEvent(new Event('error'));
      await tick();
      expect(error).toHaveBeenCalledOnce();
    });

    it('skips injection when the full build is already on the page', async () => {
      (window as unknown as { NowoCkeditor5Editor?: unknown }).NowoCkeditor5Editor = {};
      loader = createLoader(document, { src: BUILD_URL });
      await loader.load();
      expect(injectedScripts()).toHaveLength(0);
    });

    it('skips injection when a script with the same src exists', async () => {
      const existing = document.createElement('script');
      existing.setAttribute('type', 'text/plain');
      existing.setAttribute('src', BUILD_URL);
      document.head.appendChild(existing);
      loader = createLoader(document, { src: BUILD_URL });
      await loader.load();
      expect(injectedScripts()).toHaveLength(1);
    });

    it('warns and resolves when the build URL is missing', async () => {
      const warn = vi.spyOn(console, 'warn').mockImplementation(() => undefined);
      loader = createLoader(document, { src: null });
      await loader.load();
      expect(warn).toHaveBeenCalledOnce();
      expect(loader.isLoaded()).toBe(true);
      loader = createLoader(document, { src: '' });
      await loader.load();
      expect(warn).toHaveBeenCalledTimes(2);
    });
  });
});

describe('boot', () => {
  afterEach(() => {
    (window as unknown as { NowoCkeditor5EditorLoader?: Ckeditor5EditorLoader }).NowoCkeditor5EditorLoader?.disconnect();
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
    document.body.innerHTML = '';
    document.head.innerHTML = '';
  });

  it('exposes window.NowoCkeditor5EditorLoader and starts immediately when the DOM is ready', () => {
    vi.stubGlobal('IntersectionObserver', undefined);
    delete (window as unknown as { NowoCkeditor5Editor?: unknown }).NowoCkeditor5Editor;
    const tag = document.createElement('script');
    tag.setAttribute(SRC_ATTRIBUTE, BUILD_URL);
    const api = boot(document, tag);
    expect((window as unknown as { NowoCkeditor5EditorLoader?: unknown }).NowoCkeditor5EditorLoader).toBe(api);
    expect(api.isLoaded()).toBe(false);
    api.refresh();
    void api.load();
    expect(api.isLoaded()).toBe(true);
    expect(injectedScripts()).toHaveLength(1);
  });

  it('defers until DOMContentLoaded while the document is loading; load() works before that', () => {
    vi.stubGlobal('IntersectionObserver', undefined);
    delete (window as unknown as { NowoCkeditor5Editor?: unknown }).NowoCkeditor5Editor;
    vi.spyOn(document, 'readyState', 'get').mockReturnValue('loading');
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => undefined);
    const api = boot(document, null);
    expect(api.isLoaded()).toBe(false);
    api.refresh();
    api.disconnect();
    document.dispatchEvent(new Event('DOMContentLoaded'));
    expect(api.isLoaded()).toBe(false);

    const early = boot(document, null);
    void early.load();
    expect(early.isLoaded()).toBe(true);
    expect(warn).toHaveBeenCalledOnce();
    document.dispatchEvent(new Event('DOMContentLoaded'));
    expect(early.isLoaded()).toBe(true);
  });
});
