<?php

namespace PkGraphQLKit\PostType;

use PkGraphQLKit\Helpers\StringHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostTypeConfig {

	/**
	 * @param string $slug     The post type slug used in register_post_type().
	 * @param string $singular Human-readable singular name (e.g. 'Book').
	 * @param string $plural   Human-readable plural name (e.g. 'Books').
	 * @param array  $args     Additional args merged over the defaults via array_replace_recursive().
	 * @since 1.0.0
	 */
	public function __construct(
		public string $slug,
		public string $singular,
		public string $plural,
		public array $args = []
	) {}

	/**
	 * Returns the fully merged args array ready to pass to register_post_type().
	 * User-supplied args override defaults recursively, allowing partial label overrides.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function to_args(): array {
		return array_replace_recursive( $this->defaults(), $this->args );
	}

	/**
	 * Returns the default args for the post type.
	 * Includes auto-generated WPGraphQL names and a full labels array.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function defaults(): array {
		return [
			'labels'              => $this->labels(),
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
			'name'               => $p,
			'singular_name'      => $s,
			'add_new_item'       => "Add New $s",
			'edit_item'          => "Edit $s",
			'new_item'           => "New $s",
			'view_item'          => "View $s",
			'view_items'         => "View $p",
			'search_items'       => "Search $p",
			'not_found'          => "No $p found",
			'not_found_in_trash' => "No $p found in Trash",
			'all_items'          => "All $p",
			'menu_name'          => $p,
			'name_admin_bar'     => $s,
		];
	}
}
