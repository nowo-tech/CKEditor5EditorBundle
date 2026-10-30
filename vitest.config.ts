import { defineConfig } from 'vitest/config';

/**
 * Vitest: happy-dom, 100% coverage gate on every TypeScript source (logger, lazy loader and the CKEditor build entry).
 */
export default defineConfig({
  test: {
    environment: 'happy-dom',
    environmentOptions: {
      // The lazy loader injects <script> tags: never fetch them; report them as loaded
      // (`handleDisabledFileLoadingAsSuccess` exists in happy-dom 20 but not yet in Vitest 2 typings).
      happyDOM: {
        settings: { disableJavaScriptFileLoading: true, handleDisabledFileLoadingAsSuccess: true } as Record<string, boolean>,
      },
    },
    globals: true,
    include: ['src/Resources/assets/**/*.test.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'text-summary', 'html'],
      reportsDirectory: './coverage-ts',
      include: ['src/Resources/assets/src/**/*.ts'],
      exclude: ['**/*.test.ts', '**/node_modules/**'],
      thresholds: {
        lines: 100,
        functions: 100,
        branches: 100,
        statements: 100,
      },
    },
  },
});
