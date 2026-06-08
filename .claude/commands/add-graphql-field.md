Add a custom GraphQL field to `includes/GraphQL/GraphQLManager.php`.

Arguments: `$ARGUMENTS`

Expected format: `TypeName fieldName ReturnType [description]`
Examples:
- `Post readingTime Int`
- `Post readingTime Int "Estimated reading time in minutes"`
- `Page heroImage String "URL of the hero image"`

Steps:
1. Read `includes/GraphQL/GraphQLManager.php`.
2. In the `fields()` method, append a new entry to the return array:
   ```php
   [
       'type_name'  => 'TypeName',
       'field_name' => 'fieldName',
       'config'     => [
           'type'        => 'ReturnType',
           'description' => 'description if provided, otherwise generate a sensible one',
           'resolve'     => fn( $post ) => null, // TODO: implement resolver
       ],
   ],
   ```
3. If the array contains only the commented-out example, replace it with the new entry. Otherwise append after the last real entry.
4. Do not change anything else in the file.
5. Confirm what was added, and remind that the resolver needs to be implemented.