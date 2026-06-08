Register a new Custom Post Type in `includes/PostType/PostTypeManager.php`.

Arguments: `$ARGUMENTS`

Expected format: `slug singular plural` — for example: `book Book Books`

Optional: extra args can be passed as a 4th argument in PHP array syntax, e.g. `book Book Books "['menu_icon' => 'dashicons-book']"`.

Steps:
1. Read `includes/PostType/PostTypeManager.php`.
2. In the `post_types()` method, add a new entry to the return array:
   ```php
   [ 'slug' => 'slug', 'singular' => 'Singular', 'plural' => 'Plural' ],
   ```
   If extra args were provided, include `'args' => [...]` key as well.
3. Remove the commented-out example line if it is the only content in the array — replace it with the real entry. If real entries already exist, append after the last one.
4. Do not change anything else in the file.
5. Confirm what was added.