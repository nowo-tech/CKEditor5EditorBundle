import { test, expect } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { resolve } from 'node:path';
import { editable, waitForToolbar } from './helpers';

/** REQ-DEMO-013 — README screenshots (captured as PNG, converted to WebP by scripts/to-webp.mjs). */
const outDir = process.env.SCREENSHOT_DIR
  ? resolve(process.env.SCREENSHOT_DIR)
  : resolve(__dirname, '../../../../docs/images/demo');

const RICH_HTML = [
  '<h2>Release notes</h2>',
  '<p>The editor is a regular <strong>Symfony form type</strong>: the HTML is posted with the form and <i>validated</i> like any other field.</p>',
  '<ul><li>Toolbar presets defined in YAML</li><li>Image uploads and emoji</li><li>Lazy loading when the editor becomes visible</li></ul>',
  '<blockquote><p>Rich text without leaving Symfony forms.</p></blockquote>',
].join('');

test.beforeAll(() => {
  mkdirSync(outDir, { recursive: true });
});

test.describe('CKEditor 5 screenshots', () => {
  test('editor — rich content saved through the form', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 820 });
    await page.goto('/en/demo');
    const root = await editable(page);
    await root.evaluate((el, html) => {
      (el as unknown as { ckeditorInstance: { setData(h: string): void } }).ckeditorInstance.setData(html);
    }, RICH_HTML);
    await page.getByRole('button', { name: 'Save', exact: true }).click();
    await expect(page.locator('.alert-success')).toBeVisible();
    await expect(page.locator('pre')).toContainText('<h2>Release notes</h2>');
    // Put the caret in the editor so the toolbar shows its active state.
    await (await editable(page)).locator('strong').first().click();
    await waitForToolbar(page);
    await page.screenshot({ path: resolve(outDir, 'editor.png') });
  });

  test('presets — standard, simple and minimal toolbars from YAML profiles', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 1560 });
    await page.goto('/en/demo/configs');
    await editable(page, 2);
    await waitForToolbar(page);
    await page.screenshot({ path: resolve(outDir, 'presets.png') });
  });
});
