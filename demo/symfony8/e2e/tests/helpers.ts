import { expect, type Locator, type Page } from '@playwright/test';

/** Waits until a CKEditor 5 instance is mounted (toolbar + editable) and returns its editable root. */
export async function editable(page: Page, index = 0): Promise<Locator> {
  const root = page.locator('.ck-editor__editable[contenteditable="true"]').nth(index);
  await expect(root).toBeVisible({ timeout: 15_000 });
  return root;
}

/** Waits for the Symfony WebProfiler toolbar so it is part of the capture. */
export async function waitForToolbar(page: Page): Promise<void> {
  await expect(page.locator('.sf-toolbar .sf-toolbarreset').first()).toBeVisible({ timeout: 15_000 });
  await page.waitForTimeout(300);
}
