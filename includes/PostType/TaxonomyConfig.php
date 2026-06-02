<?php

namespace PkGraphQLKit\PostType;

use PkGraphQLKit\Helpers\StringHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TaxonomyConfig {

	/**
	 * @param string   $slug       The taxonomy slug used in register_taxonomy().
	 * @param string   $singular   Human-readable singular name (e.g. 'Genre').
	 * @param string   $plural     Human-readable plural name (e.g. 'Genres').
	 * @param string[] $post_types Post type slugs this taxonomy is registered for.
	 * @param array    $args       Additional args merged over the defaults via array_replace_recursive().
	 * @since 1.0.0
	 */
	public function __construct(
		public string $slug,
		public string $singular,
		public string $plural,
		public array $post_types = [],
		public array $args = []
	) {}

	/**
	 * Returns the fully merged args array ready to pass to register_taxonomy().
	 * User-supplied args override defaults recursively, allowing partial label overrides.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function to_args(): array {
		return array_replace_recursive( $this->defaults(), $this->args );
	}

	/**
	 * Returns the default args for the taxonomy.
	 * Includes auto-generated WPGraphQL names, hierarchical true, and a full labels array.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function defaults(): array {
		return [
			'labels'              => $this->labels(),
			'hierarchical'        => true,
			'public'              => true,
			'show_in_rest'        => true,
			'show_in_graphql'     => true,
			'graphql_single_name' => StringHelper::to_graphql_name( $this->singular ),
			'graphql_plural_name' => StringHelper::to_graphql_name( $this->plural ),
		];
	}

	/**
	 * Generates a standard WordPress labels array from the singular and plural names.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function labels(): array {
		$s = $this->singular;
		$p = $this->plural;

		return [
			'name'              => $p,
			'singular_name'     => $s,
			'search_items'      => "Search $p",
			'all_items'         => "All $p",
			'parent_item'       => "Parent $s",
			'parent_item_colon' => "Parent $s:",
			'edit_item'         => "Edit $s",
			'update_item'       => "Update $s",
			'add_new_item'      => "Add New $s",
			'new_item_name'     => "New $s Name",
			'menu_name'         => $p,
		];
	}
}
