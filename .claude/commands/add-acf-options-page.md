Add an ACF options page to `includes/Acf/AcfManager.php`.

Arguments: `$ARGUMENTS`

Expected format: `Title` for a simple page, or `Title parent:parent-slug` for a sub-page.
Examples:
- `Site Settings`
- `SEO parent:options`
- `Theme Options`

Steps:
1. Read `includes/Acf/AcfManager.php`.
2. In the `options_pages()` method, add a new entry to the return array:
   - Simple title (no parent): add as a plain string — `'Site Settings',`
   - Sub-page (parent provided): add as an array —
     ```php
     [ 'page_title' => 'SEO', 'menu_title' => 'SEO', 'parent_slug' => 'options' ],
     ```
3. Remove the commented-out example lines if they are the only content — replace with the real entry. Otherwise append after the last real entry.
4. Do not change anything else in the file.
5. Confirm what was added.