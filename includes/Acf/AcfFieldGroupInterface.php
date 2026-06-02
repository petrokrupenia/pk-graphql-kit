<?php

namespace PkGraphQLKit\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface AcfFieldGroupInterface {

	/**
	 * Returns the unique key for the field group (e.g. 'group_abc123').
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_key(): string;

	/**
	 * Returns the human-readable title of the field group.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_title(): string;

	/**
	 * Returns the array of ACF field definitions for this group.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_fields(): array;

	/**
	 * Returns the location rules that determine where this field group is displayed.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_location(): array;

	/**
	 * Returns additional args merged into the acf_add_local_field_group() call.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_args(): array;
}
