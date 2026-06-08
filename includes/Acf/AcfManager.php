<?php

namespace PkGraphQLKit\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AcfManager {

	private array $field_groups   = [];
	private array $dynamic_fields = [];

	/**
	 * Defines the options pages to register via acf_add_options_page().
	 * Each item is a string title or a full args array.
	 * Passes through the pkgraphqlkit/acf/options_pages filter before processing.
	 *
	 * @skill /add-acf-options-page Title [parent:parent-slug]
	 * @return array<int, string|array>
	 * @since 1.0.0
	 */
	protected function options_pages(): array {
		return [
			// 'Site Settings',
			// [ 'page_title' => 'SEO', 'parent_slug' => 'options' ],
		];
	}

	/**
	 * Registers a code-based ACF field group to be added via acf_add_local_field_group().
	 *
	 * @param AcfFieldGroupInterface $group The field group to register.
	 * @return static
	 * @since 1.0.0
	 */
	public function register_field_group( AcfFieldGroupInterface $group ): static {
		$this->field_groups[] = $group;

		return $this;
	}

	/**
	 * Registers an AcfDynamicFields instance to be booted on acf/init.
	 *
	 * @param AcfDynamicFields $fields The dynamic fields instance to register.
	 * @return static
	 * @since 1.0.0
	 */
	public function register_dynamic_fields( AcfDynamicFields $fields ): static {
		$this->dynamic_fields[] = $fields;

		return $this;
	}

	/**
	 * Registers all action and filter hooks for the manager.
	 * Called once from PkGraphQLKit::boot() when ACF is active.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {
		add_action( 'acf/init', [ $this, 'do_register_options_pages' ] );
		add_action( 'acf/init', [ $this, 'do_register_field_groups' ] );
		add_action( 'acf/init', [ $this, 'do_register_dynamic_fields' ] );
		add_filter( 'acf/settings/save_json', [ $this, 'json_save_path' ] );
		add_filter( 'acf/settings/load_json', [ $this, 'json_load_paths' ] );
	}

	/**
	 * Registers all options pages defined in options_pages().
	 * Applies the pkgraphqlkit/acf/options_pages filter before processing.
	 * Runs on acf/init.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function do_register_options_pages(): void {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		$pages = apply_filters( 'pkgraphqlkit/acf/options_pages', $this->options_pages() );

		foreach ( $pages as $page ) {
			acf_add_options_page( $this->normalize_page( $page ) );
		}
	}

	/**
	 * Registers all code-based field groups added via register_field_group()
	 * and auto-discovered from includes/Acf/FieldGroups/.
	 * Runs on acf/init.
	 *
	 * @skill /make-acf-field-group ClassName "Title" [location]
	 * @return void
	 * @since 1.0.0
	 */
	public function do_register_field_groups(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$dir   = PK_GRAPHQL_KIT_PATH . 'includes/Acf/FieldGroups/';
		$files = glob( $dir . '*.php' ) ?: [];

		foreach ( $files as $file ) {
			$class = 'PkGraphQLKit\\Acf\\FieldGroups\\' . basename( $file, '.php' );

			if ( class_exists( $class ) && is_subclass_of( $class, AcfFieldGroupInterface::class ) ) {
				$this->field_groups[] = new $class();
			}
		}

		foreach ( $this->field_groups as $group ) {
			acf_add_local_field_group( array_merge(
				[
					'key'      => $group->get_key(),
					'title'    => $group->get_title(),
					'fields'   => $group->get_fields(),
					'location' => $group->get_location(),
				],
				$group->get_args()
			) );
		}
	}

	/**
	 * Boots all AcfDynamicFields instances registered via register_dynamic_fields().
	 * Also auto-discovers subclasses from includes/Acf/DynamicFields/ directory.
	 * Runs on acf/init.
	 *
	 * @skill /make-acf-dynamic-fields ClassName [field_name1,field_name2]
	 * @return void
	 * @since 1.0.0
	 */
	public function do_register_dynamic_fields(): void {
		foreach ( $this->dynamic_fields as $fields ) {
			$fields->boot();
		}

		$dir   = PK_GRAPHQL_KIT_PATH . 'includes/Acf/DynamicFields/';
		$files = glob( $dir . '*.php' ) ?: [];

		foreach ( $files as $file ) {
			$class = 'PkGraphQLKit\\Acf\\DynamicFields\\' . basename( $file, '.php' );

			if ( class_exists( $class ) && is_subclass_of( $class, AcfDynamicFields::class ) ) {
				( new $class() )->boot();
			}
		}
	}

	/**
	 * Returns the directory path where ACF JSON files will be saved.
	 * Hooks into acf/settings/save_json.
	 *
	 * @param string $path The default save path provided by ACF.
	 * @return string The plugin's acf-json directory path.
	 * @since 1.0.0
	 */
	public function json_save_path( string $path ): string {
		return PK_GRAPHQL_KIT_PATH . 'acf-json';
	}

	/**
	 * Adds the plugin's acf-json directory to ACF's list of JSON load paths.
	 * Hooks into acf/settings/load_json.
	 *
	 * @param array $paths Existing load paths provided by ACF.
	 * @return array Updated list including the plugin's acf-json directory.
	 * @since 1.0.0
	 */
	public function json_load_paths( array $paths ): array {
		$paths[] = PK_GRAPHQL_KIT_PATH . 'acf-json';

		return $paths;
	}

	/**
	 * Normalizes a page definition to the args array expected by acf_add_options_page().
	 * Converts a string title to a minimal args array with page_title, menu_title, and menu_slug.
	 *
	 * @param string|array $page String title or full args array.
	 * @return array Normalized args array.
	 * @since 1.0.0
	 */
	private function normalize_page( string|array $page ): array {
		if ( is_array( $page ) ) {
			return $page;
		}

		return [
			'page_title' => $page,
			'menu_title' => $page,
			'menu_slug'  => sanitize_title( $page ),
		];
	}
}
