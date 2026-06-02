<?php

namespace PkGraphQLKit\PostType;

use PkGraphQLKit\Helpers\StringHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TaxonomyHelper {

	/**
	 * Returns all registered custom taxonomies, excluding WordPress built-ins.
	 *
	 * @return \WP_Taxonomy[] Keyed by taxonomy slug.
	 * @since 1.0.0
	 */
	public static function all(): array {
		return get_taxonomies( [ '_builtin' => false ], 'objects' );
	}

	/**
	 * Returns all public custom taxonomies, excluding WordPress built-ins.
	 *
	 * @return \WP_Taxonomy[] Keyed by taxonomy slug.
	 * @since 1.0.0
	 */
	public static function public_types(): array {
		return get_taxonomies( [ '_builtin' => false, 'public' => true ], 'objects' );
	}

	/**
	 * Returns all non-public custom taxonomies, excluding WordPress built-ins.
	 *
	 * @return \WP_Taxonomy[] Keyed by taxonomy slug.
	 * @since 1.0.0
	 */
	public static function non_public_types(): array {
		return get_taxonomies( [ '_builtin' => false, 'public' => false ], 'objects' );
	}

	/**
	 * Returns all custom taxonomies that have WPGraphQL support enabled.
	 * Excludes WordPress built-in taxonomies.
	 *
	 * @return WP_Taxonomy[] Keyed by taxonomy slug.
	 * @since 1.0.0
	 */
	public static function graphql_types(): array {
		return array_filter( self::all(), fn( $taxonomy ) => ! empty( $taxonomy->show_in_graphql ) );
	}

	/**
	 * Returns all taxonomies with WPGraphQL support enabled, including WordPress built-ins.
	 * Use this when attaching fields to every GraphQL-enabled taxonomy (e.g. integrations).
	 *
	 * @return WP_Taxonomy[] Keyed by taxonomy slug.
	 * @since 1.0.0
	 */
	public static function all_graphql_types(): array {
		return array_filter(
			get_taxonomies( [], 'objects' ),
			fn( $taxonomy ) => ! empty( $taxonomy->show_in_graphql )
		);
	}

	/**
	 * Enables WPGraphQL support for one or multiple taxonomies by slug.
	 * Auto-generates graphql_single_name and graphql_plural_name from labels if not already set.
	 * Must be called after the taxonomies are registered (init priority 10+).
	 *
	 * @param string|string[] $slugs Taxonomy slug or array of slugs.
	 * @return void
	 * @since 1.0.0
	 */
	public static function enable_graphql( string|array $slugs ): void {
		foreach ( (array) $slugs as $slug ) {
			$taxonomy = get_taxonomy( $slug );

			if ( ! $taxonomy ) {
				continue;
			}

			$taxonomy->show_in_graphql = true;

			if ( empty( $taxonomy->graphql_single_name ) ) {
				$taxonomy->graphql_single_name = StringHelper::to_graphql_name(
					$taxonomy->labels->singular_name ?? $slug
				);
			}

			if ( empty( $taxonomy->graphql_plural_name ) ) {
				$taxonomy->graphql_plural_name = StringHelper::to_graphql_name(
					$taxonomy->labels->name ?? $slug
				);
			}
		}
	}
}
