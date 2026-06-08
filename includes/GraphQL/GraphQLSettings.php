<?php

namespace PkGraphQLKit\GraphQL;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GraphQLSettings {

	/**
	 * Defines WPGraphQL settings to override programmatically.
	 * These values are merged over environment presets and take full precedence.
	 *
	 * Available keys mirror the graphql_general_settings option:
	 *   graphql_endpoint, public_introspection_enabled, debug_mode_enabled,
	 *   batch_queries_enabled, batch_query_limit, graphql_max_depth,
	 *   graphiql_enabled, tracing_enabled, tracing_user_role,
	 *   query_logs_enabled, query_log_role,
	 *   restrict_endpoint_to_authenticated_users,
	 *   graphql_type_tracking_enabled, delete_data_on_deactivation
	 *
	 * Boolean settings use 'on' / 'off' strings as WPGraphQL expects.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	protected function settings(): array {
		return [
			// 'graphql_endpoint'                         => 'graphql',
			// 'public_introspection_enabled'             => 'on',
			// 'batch_queries_enabled'                    => 'on',
			// 'batch_query_limit'                        => 10,
			// 'graphql_max_depth'                        => 10,
			// 'restrict_endpoint_to_authenticated_users' => 'off',
		];
	}

	/**
	 * Defines allowed CORS origins for GraphQL requests.
	 * The current site URL is included by default.
	 * Add your Next.js or other frontend URLs here.
	 *
	 * @skill /add-cors https://mysite.com http://localhost:3000
	 * @return string[]
	 * @since 1.0.0
	 */
	protected function allowed_origins(): array {
		return [
			get_bloginfo( 'url' ),
			// 'https://yoursite.com',
			// 'http://localhost:3000',
		];
	}

	/**
	 * Returns environment-specific setting presets applied automatically
	 * for all non-production environments (local, development, staging).
	 * Values from settings() always override these.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	private function environment_settings(): array {
		if ( wp_get_environment_type() === 'production' ) {
			return [];
		}

		return [
			'debug_mode_enabled' => 'on',
			'tracing_enabled'    => 'on',
		];
	}

	/**
	 * Registers all hooks for settings overrides and CORS headers.
	 * Called once from PkGraphQLKit::boot().
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {
		add_filter( 'graphql_get_setting', [ $this, 'filter_setting' ], 10, 3 );
		add_filter( 'graphql_response_headers_array', [ $this, 'filter_cors_headers' ] );
	}

	/**
	 * Overrides WPGraphQL settings by merging environment presets with settings().
	 * User-defined settings() values take precedence over environment presets.
	 * Hooks into graphql_get_setting.
	 *
	 * @param mixed  $value   The current setting value.
	 * @param string $key     The setting key being requested.
	 * @param mixed  $default The default value for the setting.
	 * @return mixed The overridden value if defined, otherwise the original value.
	 * @since 1.0.0
	 */
	public function filter_setting( mixed $value, string $key, mixed $default ): mixed {
		$settings = array_merge( $this->environment_settings(), $this->settings() );

		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $value;
	}

	/**
	 * Adds CORS headers to GraphQL responses when the request origin is in allowed_origins().
	 * Hooks into graphql_response_headers_array.
	 *
	 * @param array $headers Existing response headers.
	 * @return array Updated headers with CORS entries added if origin is allowed.
	 * @since 1.0.0
	 */
	public function filter_cors_headers( array $headers ): array {
		$allowed = $this->allowed_origins();
		$origin  = $_SERVER['HTTP_ORIGIN'] ?? '';

		if ( empty( $origin ) || ! in_array( $origin, $allowed, true ) ) {
			return $headers;
		}

		$headers['Access-Control-Allow-Origin']      = $origin;
		$headers['Access-Control-Allow-Methods']     = 'GET, POST, OPTIONS';
		$headers['Access-Control-Allow-Headers']     = 'Content-Type, Authorization';
		$headers['Access-Control-Allow-Credentials'] = 'true';
		$headers['Vary']                             = 'Origin';

		return $headers;
	}
}
