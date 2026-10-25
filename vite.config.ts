import { createHash } from 'node:crypto';
import { readdirSync, readFileSync, statSync } from 'node:fs';
import { defineConfig } from 'vite';

/**
 * Deterministic build id: a hash of the TypeScript/CSS sources and pnpm-lock.yaml. It only changes when the code or
 * the dependencies change, so rebuilding unchanged sources (e.g. in `make release-check`) leaves the committed bundle
 * byte-identical (the former wall-clock stamp rewrote it on every build).
 */
function sourceBuildId(dir: string): string {
  const root = new URL(`${dir}/`, import.meta.url);
  const hash = createHash('sha256');
  const files = (readdirSync(root, { recursive: true }) as string[])
    .filter((file) => !file.includes('.test.') && statSync(new URL(file, root)).isFile())
    .sort();
  for (const file of files) {
    hash.update(file).update(readFileSync(new URL(file, root)));
  }
  hash.update(readFileSync(new URL('pnpm-lock.yaml', import.meta.url)));

  return hash.digest('hex').slice(0, 12);
}

/**
 * Vite IIFE build for the CKEditor 5 form widget. Output: `src/Resources/public/ckeditor5-editor.js` for `assets:install`.
 */
export default defineConfig({
  define: {
    __CKEDITOR5_EDITOR_BUILD_TIME__: JSON.stringify(sourceBuildId('src/Resources/assets/src')),
  },
  build: {
    outDir: 'src/Resources/public',
    emptyOutDir: false,
    rollupOptions: {
      input: 'src/Resources/assets/src/ckeditor5-editor.ts',
      output: {
        format: 'iife',
        entryFileNames: 'ckeditor5-editor.js',
        assetFileNames: 'ckeditor5-editor.[ext]',
      },
    },
    // Emit the editor CSS as ckeditor5-editor.css (linked by the Twig helper / lazy loader) instead of a
    // runtime-injected <style>: strict CSP (style-src-elem with a nonce) blocks injected styles.
    cssCodeSplit: false,
    minify: true,
    sourcemap: false,
  },
});
