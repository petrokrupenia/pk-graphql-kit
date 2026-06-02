<?php

namespace PkGraphQLKit\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class StringHelper {

	/**
	 * Converts a human-readable string to camelCase for use as a WPGraphQL type name.
	 * Handles spaces, underscores, and hyphens as word separators.
	 *
	 * Example: "Team Member" → "teamMember", "book_genre" → "bookGenre"
	 *
	 * @param string $str The input string to convert.
	 * @return string camelCase representation of the input.
	 * @since 1.0.0
	 */
	public static function to_graphql_name( string $str ): string {
		$words = preg_split( '/[\s_-]+/', trim( strtolower( $str ) ) );
		$first = array_shift( $words );

		return $first . implode( '', array_map( 'ucfirst', $words ) );
	}
}
