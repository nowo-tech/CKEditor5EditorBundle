import { defineConfig } from 'vite';

/**
 * Vite IIFE build for the CKEditor 5 form widget. Output: `src/Resources/public/ckeditor5-editor.js` for `assets:install`.
 */
export default defineConfig({
  define: {
    __CKEDITOR5_EDITOR_BUILD_TIME__: JSON.stringify(new Date().toISOString()),
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
    minify: true,
    sourcemap: false,
  },
});
