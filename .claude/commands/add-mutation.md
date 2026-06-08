Add a custom GraphQL mutation to `includes/GraphQL/GraphQLManager.php`.

Arguments: `$ARGUMENTS`

Expected format: `MutationName [description]`
Examples:
- `SubmitContactForm`
- `SubmitContactForm "Handles contact form submission"`

Steps:
1. Read `includes/GraphQL/GraphQLManager.php`.
2. In the `mutations()` method, append a new entry to the return array with a full skeleton:
   ```php
   [
       'name'   => 'MutationName',
       'config' => [
           'inputFields'         => [
               // 'fieldName' => [ 'type' => [ 'non_null' => 'String' ] ],
           ],
           'outputFields'        => [
               'success' => [ 'type' => 'Boolean' ],
               'message' => [ 'type' => 'String' ],
           ],
           'mutateAndGetPayload' => function ( array $input ): array {
               // TODO: implement mutation logic
               return [ 'success' => false, 'message' => 'Not implemented' ];
           },
       ],
   ],
   ```
3. If the array contains only the commented-out example, replace it with the new entry. Otherwise append after the last real entry.
4. Do not change anything else in the file.
5. Confirm what was added, and remind that inputFields and mutateAndGetPayload need to be implemented.