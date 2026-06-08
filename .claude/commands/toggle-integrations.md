Enable or disable all integrations in `IntegrationManager.php`.

Arguments: `$ARGUMENTS`

Expected format: `on` or `off` — for example: `off` to disable all integrations, `on` to re-enable.

Steps:
1. Read `includes/Integration/IntegrationManager.php`.
2. Find the `integrations_enabled()` method.
3. Change its return value:
   - For `on`:  `return true;`
   - For `off`: `return false;`
4. Do not change anything else in the file.
5. Confirm the new state.