<?php

namespace PkGraphQLKit\GraphQL;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GraphQLManager {

	/**
	 * Defines custom fields to register in the WPGraphQL schema.
	 * Each item requires: type_name, field_name, config.
	 * Config follows the register_graphql_field() signature.
	 *
	 * @skill /add-graphql-field TypeName fieldName ReturnType [description]
	 * @return array<int, array{type_name: string, field_name: string, config: array}>
	 * @since 1.0.0
	 */
	protected function fields(): array {
		return [
			// [
			//     'type_name'  => 'Post',
			//     'field_name' => 'readingTime',
			//     'config'     => [
			//         'type'        => 'Int',
			//         'description' => 'Estimated reading time in minutes.',
			//         'resolve'     => fn( $post ) => (int) get_post_meta( $post->databaseId, '_reading_time', true ),
			//     ],
			// ],
		];
	}

	/**
	 * Defines custom object types to register in the WPGraphQL schema.
	 * Each item requires: type_name, config.
	 * Config follows the register_graphql_object_type() signature.
	 *
	 * @return array<int, array{type_name: string, config: array}>
	 * @since 1.0.0
	 */
	protected function types(): array {
		return [
			// [
			//     'type_name' => 'SeoData',
			//     'config'    => [
			//         'description' => 'SEO metadata fields.',
			//         'fields'      => [
			//             'title'       => [ 'type' => 'String' ],
			//             'description' => [ 'type' => 'String' ],
			//         ],
			//     ],
			// ],
		];
	}

	/**
	 * Defines custom mutations to register in the WPGraphQL schema.
	 * Each item requires: name, config.
	 * Config follows the register_graphql_mutation() signature:
	 * inputFields, outputFields, mutateAndGetPayload.
	 *
	 * @skill /add-mutation MutationName [description]
	 * @return array<int, array{name: string, config: array}>
	 * @since 1.0.0
	 */
	protected function mutations(): array {
		return [
			// [
			//     'name'   => 'SubmitContactForm',
			//     'config' => [
			//         'inputFields'          => [
			//             'name'    => [ 'type' => [ 'non_null' => 'String' ] ],
			//             'email'   => [ 'type' => [ 'non_null' => 'String' ] ],
			//             'message' => [ 'type' => [ 'non_null' => 'String' ] ],
			//         ],
			//         'outputFields'         => [
			//             'success' => [ 'type' => 'Boolean' ],
			//             'message' => [ 'type' => 'String' ],
			//         ],
			//         'mutateAndGetPayload'  => function ( array $input ): array {
			//             // handle the mutation, return output fields
			//             return [ 'success' => true, 'message' => 'Sent!' ];
			//         },
			//     ],
			// ],
		];
	}

	/**
	 * Registers all action hooks for the manager.
	 * Called once from PkGraphQLKit::boot().
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {
		add_action( 'graphql_register_types', [ $this, 'do_register_types' ] );
		add_action( 'graphql_register_types', [ $this, 'do_register_fields' ] );
		add_action( 'graphql_register_types', [ $this, 'do_register_mutations' ] );
	}

	/**
	 * Registers all custom object types defined in types().
	 * Runs on graphql_register_types.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function do_register_types(): void {
		$types = apply_filters( 'pkgraphqlkit/graphql/register/types', $this->types() );

		foreach ( $types as $type ) {
			register_graphql_object_type( $type['type_name'], $type['config'] );
		}
	}

	/**
	 * Registers all custom fields defined in fields().
	 * Types must be registered before fields that reference them.
	 * Runs on graphql_register_types after do_register_types().
	 *
	 * @return void
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function do_register_fields(): void {
		$fields = apply_filters( 'pkgraphqlkit/graphql/register/fields', $this->fields() );

		foreach ( $fields as $field ) {
			register_graphql_field( $field['type_name'], $field['field_name'], $field['config'] );
		}
	}

	/**
	 * Registers all custom mutations defined in mutations().
	 * Runs on graphql_register_types.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function do_register_mutations(): void {
		$mutations = apply_filters( 'pkgraphqlkit/graphql/register/mutations', $this->mutations() );

		foreach ( $mutations as $mutation ) {
			register_graphql_mutation( $mutation['name'], $mutation['config'] );
		}
	}
}
