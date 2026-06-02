<?php

namespace PkGraphQLKit;

use PkGraphQLKit\Acf\AcfManager;
use PkGraphQLKit\Admin\AdminPage;
use PkGraphQLKit\GraphQL\GraphQLManager;
use PkGraphQLKit\GraphQL\GraphQLSettings;
use PkGraphQLKit\Integration\IntegrationManager;
use PkGraphQLKit\PostType\PostTypeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PkGraphQLKit {

	private static ?PkGraphQLKit $instance = null;

	private PostTypeManager $post_type_manager;
	private ?AcfManager $acf_manager = null;
	private IntegrationManager $integration_manager;
	private GraphQLSettings $graphql_settings;
	private GraphQLManager $graphql_manager;
	private AdminPage $admin_page;

	private bool $booted = false;

	/**
	 * Returns the singleton instance of the plugin.
	 *
	 * @return PkGraphQLKit
	 * @since 1.0.0
	 */
	public static function get_instance(): PkGraphQLKit {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Boots all plugin managers and registers their hooks.
	 * Called once on plugins_loaded at priority 99.
	 * Subsequent calls are ignored.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		$this->post_types()->boot();
		$this->integrations()->boot();
		$this->graphql_settings()->boot();
		$this->graphql()->boot();
		$this->admin()->boot();

		if ( $this->acf() ) {
			$this->acf()->boot();
		}
	}

	/**
	 * Returns the PostTypeManager instance.
	 * Lazily instantiated on the first call.
	 *
	 * @return PostTypeManager
	 * @since 1.0.0
	 */
	public function post_types(): PostTypeManager {
		if ( ! isset( $this->post_type_manager ) ) {
			$this->post_type_manager = new PostTypeManager();
		}

		return $this->post_type_manager;
	}

	/**
	 * Returns the AcfManager instance, or null if ACF is not active.
	 * Lazily instantiated on the first call.
	 *
	 * @return AcfManager|null
	 * @since 1.0.0
	 */
	public function acf(): ?AcfManager {
		if ( ! isset( $this->acf_manager ) && class_exists( 'ACF' ) ) {
			$this->acf_manager = new AcfManager();
		}

		return $this->acf_manager;
	}

	/**
	 * Returns the IntegrationManager instance.
	 * Lazily instantiated on the first call.
	 *
	 * @return IntegrationManager
	 * @since 1.0.0
	 */
	public function integrations(): IntegrationManager {
		if ( ! isset( $this->integration_manager ) ) {
			$this->integration_manager = new IntegrationManager();
		}

		return $this->integration_manager;
	}

	/**
	 * Returns the GraphQLSettings instance.
	 * Lazily instantiated on the first call.
	 *
	 * @return GraphQLSettings
	 * @since 1.0.0
	 */
	public function graphql_settings(): GraphQLSettings {
		if ( ! isset( $this->graphql_settings ) ) {
			$this->graphql_settings = new GraphQLSettings();
		}

		return $this->graphql_settings;
	}

	/**
	 * Returns the GraphQLManager instance.
	 * Lazily instantiated on the first call.
	 *
	 * @return GraphQLManager
	 * @since 1.0.0
	 */
	public function graphql(): GraphQLManager {
		if ( ! isset( $this->graphql_manager ) ) {
			$this->graphql_manager = new GraphQLManager();
		}

		return $this->graphql_manager;
	}

	/**
	 * Returns the AdminPage instance.
	 * Lazily instantiated on the first call.
	 *
	 * @return AdminPage
	 * @since 1.0.0
	 */
	public function admin(): AdminPage {
		if ( ! isset( $this->admin_page ) ) {
			$this->admin_page = new AdminPage();
		}

		return $this->admin_page;
	}
}
