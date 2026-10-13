import { defineConfig } from 'vite';

/**
 * Vite IIFE build for the lazy loader (a few KB). Output: `src/Resources/public/ckeditor5-editor-loader.js`.
 * Separate config because Rollup cannot emit several IIFE entries in one build.
 */
export default defineConfig({
  build: {
    outDir: 'src/Resources/public',
    emptyOutDir: false,
    rollupOptions: {
      input: 'src/Resources/assets/src/ckeditor5-editor-loader.ts',
      output: {
        format: 'iife',
        entryFileNames: 'ckeditor5-editor-loader.js',
      },
    },
    minify: true,
    sourcemap: false,
  },
});
