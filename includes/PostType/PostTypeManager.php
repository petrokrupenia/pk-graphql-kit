<?php

namespace PkGraphQLKit\PostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostTypeManager {

	/**
	 * Defines the custom post types to register.
	 * Each item is a raw data array with keys: slug, singular, plural, args (optional).
	 * Passes through the pkgraphqlkit/register/post_types filter before processing.
	 *
	 * @skill /register-cpt slug singular plural
	 * @return array<int, array{slug: string, singular: string, plural: string, args?: array}>
	 * @since 1.0.0
	 */
	protected function post_types(): array {
		return [
			// [ 'slug' => 'book', 'singular' => 'Book', 'plural' => 'Books' ],
		];
	}

	/**
	 * Defines the taxonomies to register.
	 * Each item is a raw data array with keys: slug, singular, plural, post_types (optional), args (optional).
	 * Passes through the pkgraphqlkit/register/taxonomies filter before processing.
	 *
	 * @skill /register-taxonomy slug singular plural [post_type1,post_type2]
	 * @return array<int, array{slug: string, singular: string, plural: string, post_types?: string[], args?: array}>
	 * @since 1.0.0
	 */
	protected function taxonomies(): array {
		return [
			// [ 'slug' => 'genre', 'singular' => 'Genre', 'plural' => 'Genres', 'post_types' => ['book'] ],
		];
	}

	/**
	 * Defines post type slugs to unregister.
	 * Passes through the pkgraphqlkit/unregister/post_types filter before processing.
	 *
	 * @skill /unregister cpt slug
	 * @return string[]
	 * @since 1.0.0
	 */
	protected function unregister_post_types(): array {
		return [];
	}

	/**
	 * Defines taxonomy slugs to unregister.
	 * Passes through the pkgraphqlkit/unregister/taxonomies filter before processing.
	 *
	 * @skill /unregister taxonomy slug
	 * @return string[]
	 * @since 1.0.0
	 */
	protected function unregister_taxonomies(): array {
		return [];
	}

	/**
	 * Defines post type slugs of external post types (registered by other plugins)
	 * to enable WPGraphQL support for.
	 * Passes through the pkgraphqlkit/graphql/post_types filter before processing.
	 *
	 * @return string[]
	 * @since 1.0.0
	 */
	protected function graphql_post_types(): array {
		return [
			// 'page',
		];
	}

	/**
	 * Defines taxonomy slugs of external taxonomies (registered by other plugins)
	 * to enable WPGraphQL support for.
	 * Passes through the pkgraphqlkit/graphql/taxonomies filter before processing.
	 *
	 * @return string[]
	 * @since 1.0.0
	 */
	protected function graphql_taxonomies(): array {
		return [
			// 'category',
		];
	}

	/**
	 * Registers all action hooks for the manager.
	 * Called once from PkGraphQLKit::boot() on plugins_loaded.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {
		add_action( 'init', [ $this, 'do_register' ], 10 );
		add_action( 'init', [ $this, 'do_unregister' ], 20 );
		add_action( 'init', [ $this, 'do_enable_graphql' ], 25 );
	}

	/**
	 * Registers all post types and taxonomies defined in post_types() and taxonomies().
	 * Runs on init at priority 10.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function do_register(): void {
		$post_types = apply_filters( 'pkgraphqlkit/register/post_types', $this->post_types() );

		foreach ( $post_types as $data ) {
			$config = new PostTypeConfig(
				slug:     $data['slug'],
				singular: $data['singular'],
				plural:   $data['plural'],
				args:     $data['args'] ?? [],
			);
			register_post_type( $config->slug, $config->to_args() );
		}

		$taxonomies = apply_filters( 'pkgraphqlkit/register/taxonomies', $this->taxonomies() );

		foreach ( $taxonomies as $data ) {
			$config = new TaxonomyConfig(
				slug:       $data['slug'],
				singular:   $data['singular'],
				plural:     $data['plural'],
				post_types: $data['post_types'] ?? [],
				args:       $data['args'] ?? [],
			);
			register_taxonomy( $config->slug, $config->post_types, $config->to_args() );
		}
	}

	/**
	 * Unregisters post types and taxonomies defined in unregister_post_types() and unregister_taxonomies().
	 * Runs on init at priority 20, after do_register().
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function do_unregister(): void {
		$post_types = apply_filters( 'pkgraphqlkit/unregister/post_types', $this->unregister_post_types() );

		foreach ( $post_types as $slug ) {
			unregister_post_type( $slug );
		}

		$taxonomies = apply_filters( 'pkgraphqlkit/unregister/taxonomies', $this->unregister_taxonomies() );

		foreach ( $taxonomies as $slug ) {
			unregister_taxonomy( $slug );
		}
	}

	/**
	 * Enables WPGraphQL support for external post types and taxonomies
	 * defined in graphql_post_types() and graphql_taxonomies().
	 * Runs on init at priority 25, after do_register() and do_unregister().
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function do_enable_graphql(): void {
		$post_types = apply_filters( 'pkgraphqlkit/graphql/enable/post_types', $this->graphql_post_types() );
		PostTypeHelper::enable_graphql( $post_types );

		$taxonomies = apply_filters( 'pkgraphqlkit/graphql/enable/taxonomies', $this->graphql_taxonomies() );
		TaxonomyHelper::enable_graphql( $taxonomies );
	}
}
