/**
 * Lazy loader for the CKEditor 5 widget build (a few KB).
 *
 * Watches `[data-ckeditor5-root="1"]` widgets and injects the full build (`ckeditor5-editor.js`, ~1.2 MB)
 * once, the first time a widget is rendered and on screen: visible at load, or later revealed
 * (`<dialog>` opened, `<details>` toggled, hidden ancestor shown, scrolled into view, root added to the DOM).
 * After injection the full build boots normally (runInit + MutationObserver) and mounts every widget.
 *
 * The full build URL comes from the `data-ckeditor5-src` attribute of the loader `<script>` tag
 * (rendered by the Twig function `nowo_ckeditor5_editor_script({ lazy: true })`). Its `nonce` is copied
 * onto the injected script. Escape hatch: `window.NowoCkeditor5EditorLoader.load()`.
 */

/** Same selector as the full build (`ckeditor5-editor.ts`). */
export const ROOT_SELECTOR = '[data-ckeditor5-root="1"]';

/** Attribute on the loader `<script>` tag holding the full build URL. */
export const SRC_ATTRIBUTE = 'data-ckeditor5-src';

/** Attributes whose change can reveal a widget (dialog/details `open`, `hidden`, class/style toggles). */
const WATCHED_ATTRIBUTES = ['open', 'hidden', 'class', 'style'];

/** Pre-load widgets slightly before they scroll into view. */
const ROOT_MARGIN = '200px';

export type LoaderOptions = {
  /** Full build URL (`ckeditor5-editor.js`, versioned). */
  src: string | null;
  /** CSP nonce copied onto the injected `<script>`. */
  nonce?: string;
};

export type Ckeditor5EditorLoader = {
  /** Inject the full build now (idempotent). Resolves when the script has loaded. */
  load: () => Promise<void>;
  /** Re-scan the document for widget roots and inject the build if one is visible. */
  refresh: () => void;
  /** Whether the full build has been injected (or was already present). */
  isLoaded: () => boolean;
  /** Stop observing without loading. */
  disconnect: () => void;
};

/**
 * Whether the element (and its ancestors) is rendered: not `hidden`, not inside a closed `<dialog>`
 * or collapsed `<details>`, no `display: none` / `visibility: hidden` / `content-visibility: hidden`.
 *
 * @param el - Widget root.
 * @returns True when the element can be seen once it is in the viewport.
 */
export function isRendered(el: Element): boolean {
  if (!el.isConnected) {
    return false;
  }
  const view = el.ownerDocument.defaultView;
  let child: Element | null = null;
  for (let node: Element | null = el; node !== null; child = node, node = node.parentElement) {
    if (node.hasAttribute('hidden')) {
      return false;
    }
    const tag = node.tagName;
    if (tag === 'DIALOG' && !node.hasAttribute('open')) {
      return false;
    }
    if (tag === 'DETAILS' && !node.hasAttribute('open') && !(child !== null && child.tagName === 'SUMMARY')) {
      return false;
    }
    const style = view?.getComputedStyle(node);
    if (style && (style.display === 'none' || style.contentVisibility === 'hidden')) {
      return false;
    }
    if (node === el && style && (style.visibility === 'hidden' || style.visibility === 'collapse')) {
      return false;
    }
  }
  return true;
}

/**
 * Create a loader bound to a document.
 *
 * @param doc - Document to watch.
 * @param options - Full build URL and optional nonce.
 * @returns Loader API.
 */
export function createLoader(doc: Document, options: LoaderOptions): Ckeditor5EditorLoader {
  const view = doc.defaultView;
  const roots = new Set<Element>();
  const intersecting = new WeakSet<Element>();
  let loadPromise: Promise<void> | null = null;
  let pending: ReturnType<typeof setTimeout> | undefined;

  const io =
    view && typeof view.IntersectionObserver === 'function'
      ? new view.IntersectionObserver(
          (entries) => {
            for (const entry of entries) {
              if (entry.isIntersecting) {
                intersecting.add(entry.target);
              } else {
                intersecting.delete(entry.target);
              }
            }
            evaluate();
          },
          { rootMargin: ROOT_MARGIN },
        )
      : null;

  const mo = new MutationObserver(() => {
    if (pending !== undefined) {
      return;
    }
    pending = setTimeout(() => {
      pending = undefined;
      refresh();
    }, 0);
  });

  function disconnect(): void {
    io?.disconnect();
    mo.disconnect();
    if (pending !== undefined) {
      clearTimeout(pending);
      pending = undefined;
    }
  }

  function scan(): void {
    for (const root of Array.from(doc.querySelectorAll(ROOT_SELECTOR))) {
      if (!roots.has(root)) {
        roots.add(root);
        io?.observe(root);
      }
    }
    for (const root of Array.from(roots)) {
      if (!root.isConnected) {
        roots.delete(root);
        io?.unobserve(root);
      }
    }
  }

  function evaluate(): void {
    if (loadPromise !== null) {
      return;
    }
    for (const root of roots) {
      if ((io === null || intersecting.has(root)) && isRendered(root)) {
        load().catch((error: unknown) => console.error(error));
        return;
      }
    }
  }

  function refresh(): void {
    if (loadPromise !== null) {
      return;
    }
    scan();
    evaluate();
  }

  function load(): Promise<void> {
    if (loadPromise !== null) {
      return loadPromise;
    }
    disconnect();
    const src = options.src;
    const already =
      (view as unknown as { NowoCkeditor5Editor?: unknown } | null)?.NowoCkeditor5Editor !== undefined ||
      (src !== null && Array.from(doc.scripts).some((s) => s.getAttribute('src') === src));
    if (already) {
      loadPromise = Promise.resolve();
      return loadPromise;
    }
    if (src === null || src === '') {
      console.warn(`[ckeditor5-editor-loader] missing ${SRC_ATTRIBUTE} on the loader script; cannot load the editor build.`);
      loadPromise = Promise.resolve();
      return loadPromise;
    }
    const script = doc.createElement('script');
    script.src = src;
    script.async = true;
    if (options.nonce) {
      script.nonce = options.nonce;
      script.setAttribute('nonce', options.nonce);
    }
    loadPromise = new Promise<void>((resolve, reject) => {
      script.addEventListener('load', () => resolve());
      script.addEventListener('error', () => reject(new Error(`[ckeditor5-editor-loader] failed to load ${src}`)));
    });
    doc.head.appendChild(script);
    return loadPromise;
  }

  mo.observe(doc.documentElement, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: WATCHED_ATTRIBUTES,
  });
  refresh();

  return {
    load,
    refresh,
    isLoaded: () => loadPromise !== null,
    disconnect,
  };
}

/**
 * Read the full build URL and nonce from the loader `<script>` tag.
 *
 * @param doc - Document.
 * @param current - `document.currentScript` captured while the loader executed.
 * @returns Loader options.
 */
export function readOptions(doc: Document, current: Element | null): LoaderOptions {
  const tag =
    current !== null && current.hasAttribute(SRC_ATTRIBUTE)
      ? current
      : doc.querySelector(`script[${SRC_ATTRIBUTE}]`);
  if (tag === null) {
    return { src: null };
  }
  const nonce = (tag as HTMLScriptElement).nonce || tag.getAttribute('nonce') || undefined;
  return { src: tag.getAttribute(SRC_ATTRIBUTE), nonce };
}

/**
 * Boot the loader on the current document and expose `window.NowoCkeditor5EditorLoader`.
 *
 * @param doc - Document.
 * @param current - Loader script element (if known).
 * @returns Loader API.
 */
export function boot(doc: Document, current: Element | null): Ckeditor5EditorLoader {
  const options = readOptions(doc, current);
  let instance: Ckeditor5EditorLoader | null = null;
  const api: Ckeditor5EditorLoader = {
    load: () => (instance ??= createLoader(doc, options)).load(),
    refresh: () => instance?.refresh(),
    isLoaded: () => instance?.isLoaded() ?? false,
    disconnect: () => instance?.disconnect(),
  };
  (doc.defaultView as unknown as { NowoCkeditor5EditorLoader?: Ckeditor5EditorLoader }).NowoCkeditor5EditorLoader = api;
  const start = (): void => {
    instance ??= createLoader(doc, options);
  };
  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
  return api;
}

boot(document, document.currentScript);
