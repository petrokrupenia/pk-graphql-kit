<?php

namespace PkGraphQLKit\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for dynamically populating ACF field choices.
 *
 * Extend this class, implement register_filters() to wire up
 * acf/load_field filters, and add a method per field.
 *
 * Example:
 *
 *   class MyDynamicFields extends AcfDynamicFields {
 *       protected function register_filters(): void {
 *           add_filter( 'acf/load_field/name=my_select', [ $this, 'load_my_select' ] );
 *       }
 *
 *       public function load_my_select( array $field ): array {
 *           $field['choices'] = [ 'foo' => 'Foo', 'bar' => 'Bar' ];
 *           return $field;
 *       }
 *   }
 *
 *   pkgraphqlkit()->acf()->register_dynamic_fields( new MyDynamicFields() );
 *
 * @since 1.0.0
 */
abstract class AcfDynamicFields {

	/**
	 * Called by AcfManager on acf/init.
	 * Invokes register_filters() to wire up all acf/load_field filters.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	final public function boot(): void {
		$this->register_filters();
	}

	/**
	 * Implement this method to register acf/load_field filters.
	 * Each filter callback should be a public method on the subclass.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	abstract protected function register_filters(): void;
}
