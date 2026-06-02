<?php

namespace PkGraphQLKit\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractIntegration {

	/**
	 * Returns the plugin file path relative to the plugins directory (e.g. 'slim-seo/slim-seo.php').
	 * Used by is_active() to check whether the target plugin is installed and active.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	abstract public function get_plugin_file(): string;

	/**
	 * Registers all hooks for this integration.
	 * Called by IntegrationManager::boot() only when is_active() returns true.
	 * Default implementation calls register_graphql() to wire up schema extensions.
	 * Override and call parent::register() if additional hooks are needed.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register(): void {
		$this->register_graphql();
	}

	/**
	 * Checks whether the target plugin is currently active.
	 * Loads wp-admin/includes/plugin.php if is_plugin_active() is not yet available.
	 *
	 * @return bool True if the plugin is active, false otherwise.
	 * @since 1.0.0
	 */
	public function is_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $this->get_plugin_file() );
	}

	/**
	 * Wires up graphql_fields(), graphql_types(), and graphql_mutations()
	 * into the GraphQLManager filters so they are processed alongside
	 * all other schema registrations.
	 * Call this from register() to use the standard GraphQL registration flow.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function register_graphql(): void {
		add_filter(
			'pkgraphqlkit/graphql/register/fields',
			fn( array $fields ) => array_merge( $fields, $this->graphql_fields() )
		);

		add_filter(
			'pkgraphqlkit/graphql/register/types',
			fn( array $types ) => array_merge( $types, $this->graphql_types() )
		);

		add_filter(
			'pkgraphqlkit/graphql/register/mutations',
			fn( array $mutations ) => array_merge( $mutations, $this->graphql_mutations() )
		);
	}

	/**
	 * Returns a human-readable label for the integration derived from the plugin file path.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_label(): string {
		return ucwords( str_replace( '-', ' ', basename( dirname( $this->get_plugin_file() ) ) ) );
	}

	/**
	 * Returns the GraphQL fields registered by this integration.
	 *
	 * @return array<int, array{type_name: string, field_name: string, config: array}>
	 * @since 1.0.0
	 */
	public function get_graphql_fields(): array {
		return $this->graphql_fields();
	}

	/**
	 * Returns the GraphQL object types registered by this integration.
	 *
	 * @return array<int, array{type_name: string, config: array}>
	 * @since 1.0.0
	 */
	public function get_graphql_types(): array {
		return $this->graphql_types();
	}

	/**
	 * Returns the GraphQL mutations registered by this integration.
	 *
	 * @return array<int, array{name: string, config: array}>
	 * @since 1.0.0
	 */
	public function get_graphql_mutations(): array {
		return $this->graphql_mutations();
	}

	/**
	 * Defines custom GraphQL fields to add to the schema for this integration.
	 * Each item follows the same format as GraphQLManager::fields().
	 *
	 * @return array<int, array{type_name: string, field_name: string, config: array}>
	 * @since 1.0.0
	 */
	protected function graphql_fields(): array {
		return [];
	}

	/**
	 * Defines custom GraphQL object types to add to the schema for this integration.
	 * Each item follows the same format as GraphQLManager::types().
	 *
	 * @return array<int, array{type_name: string, config: array}>
	 * @since 1.0.0
	 */
	protected function graphql_types(): array {
		return [];
	}

	/**
	 * Defines custom GraphQL mutations to add to the schema for this integration.
	 * Each item follows the same format as GraphQLManager::mutations().
	 *
	 * @return array<int, array{name: string, config: array}>
	 * @since 1.0.0
	 */
	protected function graphql_mutations(): array {
		return [];
	}
}
