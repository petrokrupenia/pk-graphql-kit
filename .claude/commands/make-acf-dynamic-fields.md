Create a new ACF dynamic fields class in `includes/Acf/DynamicFields/`.
Classes placed there are auto-discovered by AcfManager — no manual registration needed.

Arguments: `$ARGUMENTS`

Expected format: `ClassName [field_name1,field_name2,...]`
Examples:
- `PostCategoryChoices`
- `ProductChoices product_category,product_type`

Steps:
1. Determine the file path: `includes/Acf/DynamicFields/{ClassName}.php`
2. Create the file with this skeleton:

```php
<?php

namespace PkGraphQLKit\Acf\DynamicFields;

use PkGraphQLKit\Acf\AcfDynamicFields;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class {ClassName} extends AcfDynamicFields {

    protected function register_filters(): void {
        // For each field_name provided as argument, generate one filter + method pair.
        // If no field names provided, generate one example pair.
        add_filter( 'acf/load_field/name=field_name', [ $this, 'load_field_name' ] );
    }

    public function load_field_name( array $field ): array {
        $field['choices'] = [
            // 'value' => 'Label',
        ];

        return $field;
    }
}
```

If field names were provided, generate one `add_filter` call and one `load_{field_name}` method per field name.
Use proper PHP indentation (tabs). Do not create any other files.
Confirm the file path and remind that `$field['choices']` needs to be populated.