<?php

namespace PkGraphQLKit\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IntegrationManager {

	/** @var AbstractIntegration[] */
	private array $active = [];

	/**
	 * Master toggle for all integrations.
	 * Return false to skip all integration discovery and registration.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function integrations_enabled(): bool {
		return true;
	}

	/**
	 * Discovers all integration classes from the Integrations/ directory,
	 * then calls register() on each one whose target plugin is active.
	 * Called once from PkGraphQLKit::boot() on plugins_loaded.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {
		if ( ! $this->integrations_enabled() ) {
			return;
		}

		foreach ( $this->discover() as $integration ) {
			if ( $integration->is_active() ) {
				$integration->register();
				$this->active[] = $integration;
			}
		}
	}

	/**
	 * Returns all currently active integrations.
	 *
	 * @return AbstractIntegration[]
	 * @since 1.0.0
	 */
	public function active(): array {
		return $this->active;
	}

	/**
	 * Scans the Integrations/ directory and returns an instance of every class
	 * that extends AbstractIntegration.
	 *
	 * @return AbstractIntegration[]
	 * @since 1.0.0
	 */
	private function discover(): array {
		$dir   = PK_GRAPHQL_KIT_PATH . 'includes/Integration/Integrations/';
		$files = glob( $dir . '*.php' ) ?: [];

		$integrations = [];

		foreach ( $files as $file ) {
			$class = 'PkGraphQLKit\\Integration\\Integrations\\' . basename( $file, '.php' );

			if ( class_exists( $class ) && is_subclass_of( $class, AbstractIntegration::class ) ) {
				$integrations[] = new $class();
			}
		}

		return $integrations;
	}
}
