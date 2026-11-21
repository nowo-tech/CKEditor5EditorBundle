import { expect, type Locator, type Page } from '@playwright/test';

/** Waits until a CKEditor 5 instance is mounted (toolbar + editable) and returns its editable root. */
export async function editable(page: Page, index = 0): Promise<Locator> {
  const root = page.locator('.ck-editor__editable[contenteditable="true"]').nth(index);
  await expect(root).toBeVisible({ timeout: 15_000 });
  return root;
}
