Create a new ACF field group class in `includes/Acf/FieldGroups/`.

Arguments: `$ARGUMENTS`

Expected format: `ClassName "Title" [location]`
Location can be: `post-type:post_type_slug`, `options`, `page`, `user` — defaults to `post-type:post` if omitted.
Examples:
- `BookFields "Book Fields" post-type:book`
- `SiteSettingsFields "Site Settings" options`
- `AuthorFields "Author Fields" user`

Steps:
1. Determine the file path: `includes/Acf/FieldGroups/{ClassName}.php`
2. Create the file with this skeleton:

```php
<?php

namespace PkGraphQLKit\Acf\FieldGroups;

use PkGraphQLKit\Acf\AcfFieldGroupInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class {ClassName} implements AcfFieldGroupInterface {

    public function get_key(): string {
        return 'group_{snake_case_of_class_name}';
    }

    public function get_title(): string {
        return '{Title}';
    }

    public function get_fields(): array {
        return [
            // [
            //     'key'   => 'field_{snake_case_of_class_name}_example',
            //     'label' => 'Example Field',
            //     'name'  => 'example_field',
            //     'type'  => 'text',
            // ],
        ];
    }

    public function get_location(): array {
        // location based on the provided argument
        return [
            [
                [ 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ],
            ],
        ];
    }

    public function get_args(): array {
        return [];
    }
}
```

Location rules to use based on the argument:
- `post-type:slug` → `[ 'param' => 'post_type', 'operator' => '==', 'value' => 'slug' ]`
- `options`        → `[ 'param' => 'options_page', 'operator' => '==', 'value' => 'acf-options' ]`
- `page`           → `[ 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ]`
- `user`           → `[ 'param' => 'user_form', 'operator' => '==', 'value' => 'all' ]`

Use proper PHP indentation (tabs). Do not create any other files.
Confirm the file path and remind that `get_fields()` needs to be filled in.