<?php

namespace PkGraphQLKit\PostType;

use PkGraphQLKit\Helpers\StringHelper;
use WP_Post_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostTypeHelper {

	/**
	 * Returns all registered custom post types, excluding WordPress built-ins.
	 *
	 * @return WP_Post_Type[] Keyed by post type slug.
	 * @since 1.0.0
	 */
	public static function all(): array {
		return get_post_types( [ '_builtin' => false ], 'objects' );
	}

	/**
	 * Returns all public custom post types, excluding WordPress built-ins.
	 *
	 * @return WP_Post_Type[] Keyed by post type slug.
	 * @since 1.0.0
	 */
	public static function public_types(): array {
		return get_post_types( [ '_builtin' => false, 'public' => true ], 'objects' );
	}

	/**
	 * Returns all non-public custom post types, excluding WordPress built-ins.
	 *
	 * @return WP_Post_Type[] Keyed by post type slug.
	 * @since 1.0.0
	 */
	public static function non_public_types(): array {
		return get_post_types( [ '_builtin' => false, 'public' => false ], 'objects' );
	}

	/**
	 * Returns all custom post types that have WPGraphQL support enabled.
	 * Excludes WordPress built-in post types.
	 *
	 * @return WP_Post_Type[] Keyed by post type slug.
	 * @since 1.0.0
	 */
	public static function graphql_types(): array {
		return array_filter( self::all(), fn( $cpt ) => ! empty( $cpt->show_in_graphql ) );
	}

	/**
	 * Returns all post types with WPGraphQL support enabled, including WordPress built-ins.
	 * Use this when attaching fields to every GraphQL-enabled type (e.g. integrations).
	 *
	 * @return WP_Post_Type[] Keyed by post type slug.
	 * @since 1.0.0
	 */
	public static function all_graphql_types(): array {
		return array_filter(
			get_post_types( [], 'objects' ),
			fn( $cpt ) => ! empty( $cpt->show_in_graphql )
		);
	}

	/**
	 * Enables WPGraphQL support for one or multiple post types by slug.
	 * Auto-generates graphql_single_name and graphql_plural_name from labels if not already set.
	 * Must be called after the post types are registered (init priority 10+).
	 *
	 * @param string|string[] $slugs Post type slug or array of slugs.
	 * @return void
	 * @since 1.0.0
	 */
	public static function enable_graphql( string|array $slugs ): void {
		foreach ( (array) $slugs as $slug ) {
			$post_type = get_post_type_object( $slug );

			if ( ! $post_type ) {
				continue;
			}

			$post_type->show_in_graphql = true;

			if ( empty( $post_type->graphql_single_name ) ) {
				$post_type->graphql_single_name = StringHelper::to_graphql_name(
					$post_type->labels->singular_name ?? $slug
				);
			}

			if ( empty( $post_type->graphql_plural_name ) ) {
				$post_type->graphql_plural_name = StringHelper::to_graphql_name(
					$post_type->labels->name ?? $slug
				);
			}
		}
	}
}
