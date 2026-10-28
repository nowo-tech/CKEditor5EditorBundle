import { test, expect } from '@playwright/test';
import { editable } from './helpers';

/** REQ-DEMO-013 — CKEditor 5 demo happy path (home → editor → save → submitted HTML; presets). */
test.describe('CKEditor 5 demo', () => {
  test('home links to the editor page', async ({ page }) => {
    const response = await page.goto('/en/');
    expect(response?.ok()).toBeTruthy();
    await page.locator('main, body').getByRole('link', { name: 'Editor', exact: true }).last().click();
    await expect(page).toHaveURL(/\/en\/demo$/);
    await expect(page.locator('.ck-toolbar').first()).toBeVisible();
  });

  test('typing with the toolbar and saving posts the HTML', async ({ page }) => {
    await page.goto('/en/demo');
    const root = await editable(page);
    await root.click();
    await page.keyboard.press('ControlOrMeta+A');
    await page.keyboard.type('Plain and ');
    await page.locator('.ck-toolbar').first().getByRole('button', { name: 'Bold' }).click();
    await page.keyboard.type('bold text');
    await expect(root.locator('strong')).toContainText('bold text');
    await page.getByRole('button', { name: 'Save', exact: true }).click();
    await expect(page.locator('.alert-success')).toContainText('Content saved.');
    await expect(page.locator('pre')).toContainText('<p>Plain and <strong>bold text</strong></p>');
  });

  test('presets page mounts one editor per YAML profile', async ({ page }) => {
    await page.goto('/en/demo/configs');
    await expect(page.getByRole('heading', { name: 'Named YAML profiles' })).toBeVisible();
    await editable(page, 2);
    await expect(page.locator('.ck-editor')).toHaveCount(3);
    // The standard preset has a table button; the minimal one does not.
    await expect(page.locator('.ck-editor').nth(0).getByRole('button', { name: 'Insert table' })).toBeVisible();
    await expect(page.locator('.ck-editor').nth(2).getByRole('button', { name: 'Insert table' })).toHaveCount(0);
  });
});
