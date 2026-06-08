Register a new Taxonomy in `includes/PostType/PostTypeManager.php`.

Arguments: `$ARGUMENTS`

Expected format: `slug singular plural [post_type1,post_type2]` — for example: `genre Genre Genres book` or `genre Genre Genres book,article`.

Optional: extra args can be passed as a last argument in PHP array syntax, e.g. `"['hierarchical' => false]"`.

Steps:
1. Read `includes/PostType/PostTypeManager.php`.
2. In the `taxonomies()` method, add a new entry to the return array:
   ```php
   [ 'slug' => 'slug', 'singular' => 'Singular', 'plural' => 'Plural', 'post_types' => ['post_type'] ],
   ```
   - If no post types were specified, omit the `post_types` key.
   - If extra args were provided, include `'args' => [...]` key as well.
3. Remove the commented-out example line if it is the only content in the array — replace it with the real entry. If real entries already exist, append after the last one.
4. Do not change anything else in the file.
5. Confirm what was added.