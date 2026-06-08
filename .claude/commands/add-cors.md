Add one or more CORS origins to `GraphQLSettings.php`.

Arguments: `$ARGUMENTS`

Expected format: one or more URLs separated by spaces — for example: `https://mysite.com` or `https://mysite.com http://localhost:3000`.

Steps:
1. Read `includes/GraphQL/GraphQLSettings.php`.
2. In the `allowed_origins()` method, add each provided URL as a new string entry in the return array:
   ```php
   'https://yoursite.com',
   ```
3. Remove the commented-out example lines only if real entries are added alongside them. Never remove `get_bloginfo( 'url' )`.
4. Do not change anything else in the file.
5. Confirm which URLs were added.