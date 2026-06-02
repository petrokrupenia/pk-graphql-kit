# PK GraphQL Kit

Developer toolkit for headless WordPress — CPT, ACF, WPGraphQL schema in one place.

## Requirements

- WordPress 6.8+
- PHP 8.0+
- [WPGraphQL](https://wordpress.org/plugins/wp-graphql/) (required)
- [Advanced Custom Fields](https://www.advancedcustomfields.com/) (optional)

## Installation

1. Clone or copy the plugin into `wp-content/plugins/pk-graphql-kit/`
2. Run `composer install`
3. Activate the plugin in WordPress admin

## Overview

The plugin is configured by editing files inside `includes/`. There is no settings UI for configuration — every manager exposes protected methods you override directly.

The admin overview page is available at **Settings → PK GraphQL Kit**.

---

## Post Types & Taxonomies

Edit `includes/PostType/PostTypeManager.php`.

### Register post types

```php
protected function post_types(): array {
    return [
        [ 'slug' => 'book', 'singular' => 'Book', 'plural' => 'Books' ],
        [ 'slug' => 'team-member', 'singular' => 'Team Member', 'plural' => 'Team Members' ],
    ];
}
```

All post types are registered with `show_in_graphql`, `show_in_rest`, and auto-generated `graphql_single_name` / `graphql_plural_name` by default. Pass `args` to override any defaults.

### Register taxonomies

```php
protected function taxonomies(): array {
    return [
        [ 'slug' => 'genre', 'singular' => 'Genre', 'plural' => 'Genres', 'post_types' => [ 'book' ] ],
    ];
}
```

Taxonomies default to `hierarchical => true` and full GraphQL support.

### Unregister

```php
protected function unregister_post_types(): array {
    return [ 'some-post-type' ];
}

protected function unregister_taxonomies(): array {
    return [ 'some-taxonomy' ];
}
```

### Enable WPGraphQL on external post types

For post types registered by other plugins that don't have GraphQL support:

```php
protected function graphql_post_types(): array {
    return [ 'page', 'product' ];
}

protected function graphql_taxonomies(): array {
    return [ 'category' ];
}
```

`graphql_single_name` and `graphql_plural_name` are auto-generated from the post type labels.

### Helpers

```php
use PkGraphQLKit\PostType\PostTypeHelper;
use PkGraphQLKit\PostType\TaxonomyHelper;

// Custom post types only (no built-ins)
PostTypeHelper::all();
PostTypeHelper::public_types();
PostTypeHelper::non_public_types();
PostTypeHelper::graphql_types();

// All post types including built-ins (useful in integrations)
PostTypeHelper::all_graphql_types();

// Enable GraphQL support manually
PostTypeHelper::enable_graphql( [ 'page', 'product' ] );
```

Same methods available on `TaxonomyHelper`.

### Filters

| Filter | Description |
|--------|-------------|
| `pkgraphqlkit/register/post_types` | Modify post types array before registration |
| `pkgraphqlkit/register/taxonomies` | Modify taxonomies array before registration |
| `pkgraphqlkit/unregister/post_types` | Modify slugs array before unregistering |
| `pkgraphqlkit/unregister/taxonomies` | Modify slugs array before unregistering |
| `pkgraphqlkit/graphql/post_types` | Modify slugs array before enabling GraphQL |
| `pkgraphqlkit/graphql/taxonomies` | Modify slugs array before enabling GraphQL |

---

## ACF

Edit `includes/Acf/AcfManager.php`. Only available when ACF is active.

### Options pages

```php
protected function options_pages(): array {
    return [
        'Site Settings',
        [ 'page_title' => 'SEO Settings', 'parent_slug' => 'options' ],
    ];
}
```

### ACF JSON

Field groups created in the ACF admin are automatically saved to and loaded from `acf-json/` in the plugin root. Commit this directory to version control.

### Dynamic fields

To dynamically populate field choices (e.g. a select from an options page), create a class in `includes/Acf/DynamicFields/` that extends `AcfDynamicFields`:

```php
namespace PkGraphQLKit\Acf\DynamicFields;

use PkGraphQLKit\Acf\AcfDynamicFields;

class MyDynamicFields extends AcfDynamicFields {
    protected function register_filters(): void {
        add_filter( 'acf/load_field/name=my_select', [ $this, 'load_my_select' ] );
    }

    public function load_my_select( array $field ): array {
        $field['choices'] = [ 'foo' => 'Foo', 'bar' => 'Bar' ];
        return $field;
    }
}
```

The class is discovered and booted automatically — no registration required.

### Code-based field groups

Implement `AcfFieldGroupInterface` and register via `AcfManager::register_field_group()` if you need field groups defined in PHP. In most cases, the ACF admin + JSON sync is preferable.

### Filters

| Filter | Description |
|--------|-------------|
| `pkgraphqlkit/acf/options_pages` | Add or modify options pages before registration |

---

## WPGraphQL

### Settings

Edit `includes/GraphQL/GraphQLSettings.php`.

```php
protected function settings(): array {
    return [
        'graphql_endpoint'             => 'graphql',
        'public_introspection_enabled' => 'on',
    ];
}
```

Boolean settings use `'on'` / `'off'` strings as WPGraphQL expects. Settings defined here override the WPGraphQL admin UI.

**Environment presets** — `debug_mode_enabled` and `tracing_enabled` are automatically set to `'on'` for `local`, `development`, and `staging` environments. Values in `settings()` always take precedence.

### CORS

```php
protected function allowed_origins(): array {
    return [
        get_bloginfo( 'url' ),  // always included
        'https://yoursite.com',
        'http://localhost:3000',
    ];
}
```

### Custom types, fields, mutations

Edit `includes/GraphQL/GraphQLManager.php`.

```php
protected function types(): array {
    return [
        [
            'type_name' => 'Address',
            'config'    => [
                'description' => 'A physical address.',
                'fields'      => [
                    'street' => [ 'type' => 'String' ],
                    'city'   => [ 'type' => 'String' ],
                ],
            ],
        ],
    ];
}

protected function fields(): array {
    return [
        [
            'type_name'  => 'Post',
            'field_name' => 'readingTime',
            'config'     => [
                'type'    => 'Int',
                'resolve' => fn( $post ) => (int) get_post_meta( $post->databaseId, '_reading_time', true ),
            ],
        ],
    ];
}

protected function mutations(): array {
    return [
        [
            'name'   => 'SubmitContactForm',
            'config' => [
                'inputFields'         => [
                    'name'    => [ 'type' => [ 'non_null' => 'String' ] ],
                    'email'   => [ 'type' => [ 'non_null' => 'String' ] ],
                    'message' => [ 'type' => [ 'non_null' => 'String' ] ],
                ],
                'outputFields'        => [
                    'success' => [ 'type' => 'Boolean' ],
                    'message' => [ 'type' => 'String' ],
                ],
                'mutateAndGetPayload' => function ( array $input ): array {
                    // handle the mutation
                    return [ 'success' => true, 'message' => 'Sent!' ];
                },
            ],
        ],
    ];
}
```

### Filters

| Filter | Description |
|--------|-------------|
| `pkgraphqlkit/graphql/register/types` | Modify types array before registration |
| `pkgraphqlkit/graphql/register/fields` | Modify fields array before registration |
| `pkgraphqlkit/graphql/register/mutations` | Modify mutations array before registration |

---

## Integrations

Integrations add WPGraphQL support for third-party plugins. They are **auto-discovered** — create a file in `includes/Integration/Integrations/`, extend `AbstractIntegration`, and it will be loaded automatically when the target plugin is active.

```php
namespace PkGraphQLKit\Integration\Integrations;

use PkGraphQLKit\Integration\AbstractIntegration;

class MyPluginIntegration extends AbstractIntegration {

    public function get_plugin_file(): string {
        return 'my-plugin/my-plugin.php';
    }

    protected function graphql_fields(): array {
        return [
            [
                'type_name'  => 'Post',
                'field_name' => 'myField',
                'config'     => [
                    'type'    => 'String',
                    'resolve' => fn( $post ) => get_post_meta( $post->databaseId, '_my_field', true ),
                ],
            ],
        ];
    }
}
```

Override `register()` and call `parent::register()` if additional hooks beyond GraphQL schema registration are needed.

### Bundled integrations

| Integration | Plugin |
|-------------|--------|
| `SlimSeoIntegration` | [Slim SEO](https://wordpress.org/plugins/slim-seo/) — exposes `seo { title, description, noindex, canonical, og { title, description, image }, twitter { card, image, site } }` on all GraphQL-enabled post types and taxonomies |

---

## Accessing managers programmatically

```php
pkgraphqlkit()->post_types()       // PostTypeManager
pkgraphqlkit()->acf()              // AcfManager|null
pkgraphqlkit()->integrations()     // IntegrationManager
pkgraphqlkit()->graphql()          // GraphQLManager
pkgraphqlkit()->graphql_settings() // GraphQLSettings
pkgraphqlkit()->admin()            // AdminPage
```

---

## Directory structure

```
includes/
├── Admin/
│   └── AdminPage.php               — admin overview page (Settings → PK GraphQL Kit)
├── Acf/
│   ├── AcfDynamicFields.php        — base class for dynamic field population
│   ├── AcfFieldGroupInterface.php  — interface for code-based field groups
│   ├── AcfManager.php              — options pages, field groups, ACF JSON path
│   └── DynamicFields/              — auto-discovered dynamic field classes
├── GraphQL/
│   ├── GraphQLManager.php          — custom types, fields, mutations
│   └── GraphQLSettings.php         — WPGraphQL settings overrides + CORS
├── Helpers/
│   └── StringHelper.php            — to_graphql_name() utility
├── Integration/
│   ├── AbstractIntegration.php     — base class for integrations
│   ├── IntegrationManager.php      — auto-discovers and boots integrations
│   └── Integrations/               — auto-discovered integration classes
└── PostType/
    ├── PostTypeConfig.php           — value object for CPT registration
    ├── PostTypeHelper.php           — static helpers for querying post types
    ├── PostTypeManager.php          — registers/unregisters CPTs and taxonomies
    ├── TaxonomyConfig.php           — value object for taxonomy registration
    └── TaxonomyHelper.php           — static helpers for querying taxonomies
acf-json/                            — ACF field group JSON (commit to version control)
```
