Unregister a post type or taxonomy in `includes/PostType/PostTypeManager.php`.

Arguments: `$ARGUMENTS`

Expected format: `type slug` — where `type` is either `cpt` or `taxonomy`, for example: `cpt attachment` or `taxonomy post_tag`.

Steps:
1. Read `includes/PostType/PostTypeManager.php`.
2. Depending on the type:
   - For `cpt`: add the slug as a string to the array in `unregister_post_types()`.
   - For `taxonomy`: add the slug as a string to the array in `unregister_taxonomies()`.
3. Format: `'slug',` — one slug per line, consistent with existing entries.
4. Do not change anything else in the file.
5. Confirm what was added.