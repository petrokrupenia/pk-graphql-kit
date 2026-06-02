<?php

namespace PkGraphQLKit\Integration\Integrations;

use PkGraphQLKit\Integration\AbstractIntegration;
use PkGraphQLKit\PostType\PostTypeHelper;
use PkGraphQLKit\PostType\TaxonomyHelper;
use SlimSEO\MetaTags\Description;
use SlimSEO\MetaTags\Image;
use SlimSEO\MetaTags\Robots;
use SlimSEO\MetaTags\CanonicalUrl;
use SlimSEO\MetaTags\Title;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SlimSeoIntegration extends AbstractIntegration {

	/**
	 * Returns the plugin file path used to check if Slim SEO is active.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_plugin_file(): string {
		return 'slim-seo/slim-seo.php';
	}

	/**
	 * Registers WPGraphQL schema extensions for Slim SEO.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register(): void {
		$this->register_graphql();
	}

	/**
	 * Defines SlimSEO GraphQL types: SlimSeoFields, SlimSeoOpenGraph, SlimSeoTwitter.
	 *
	 * @return array<int, array{type_name: string, config: array}>
	 * @since 1.0.0
	 */
	protected function graphql_types(): array {
		return [
			[
				'type_name' => 'SlimSeoOpenGraph',
				'config'    => [
					'description' => 'Open Graph meta fields provided by Slim SEO.',
					'fields'      => [
						'title'       => [ 'type' => 'String', 'description' => 'og:title.' ],
						'description' => [ 'type' => 'String', 'description' => 'og:description.' ],
						'image'       => [ 'type' => 'String', 'description' => 'og:image URL.' ],
					],
				],
			],
			[
				'type_name' => 'SlimSeoTwitter',
				'config'    => [
					'description' => 'Twitter Card meta fields provided by Slim SEO.',
					'fields'      => [
						'card'  => [ 'type' => 'String', 'description' => 'twitter:card type.' ],
						'image' => [ 'type' => 'String', 'description' => 'twitter:image URL.' ],
						'site'  => [ 'type' => 'String', 'description' => 'twitter:site handle (e.g. @username).' ],
					],
				],
			],
			[
				'type_name' => 'SlimSeoFields',
				'config'    => [
					'description' => 'SEO fields provided by Slim SEO.',
					'fields'      => [
						'title'       => [ 'type' => 'String',          'description' => 'SEO title.' ],
						'description' => [ 'type' => 'String',          'description' => 'SEO description.' ],
						'noindex'     => [ 'type' => 'Boolean',         'description' => 'Whether the page is excluded from search engines.' ],
						'canonical'   => [ 'type' => 'String',          'description' => 'Canonical URL.' ],
						'og'          => [ 'type' => 'SlimSeoOpenGraph', 'description' => 'Open Graph data.' ],
						'twitter'     => [ 'type' => 'SlimSeoTwitter',  'description' => 'Twitter Card data.' ],
					],
				],
			],
		];
	}

	/**
	 * Registers the seo field on every GraphQL-enabled post type and taxonomy.
	 *
	 * @return array<int, array{type_name: string, field_name: string, config: array}>
	 * @since 1.0.0
	 */
	protected function graphql_fields(): array {
		$fields              = [];
		$excluded_taxonomies = [ 'post_format' ];

		foreach ( PostTypeHelper::all_graphql_types() as $post_type ) {
			$graphql_type = ucfirst( $post_type->graphql_single_name ?? $post_type->name );

			$fields[] = [
				'type_name'  => $graphql_type,
				'field_name' => 'seo',
				'config'     => [
					'type'        => 'SlimSeoFields',
					'description' => "Slim SEO data for {$post_type->label}.",
					'resolve'     => [ $this, 'resolve_post_type' ],
				],
			];
		}

		foreach ( TaxonomyHelper::all_graphql_types() as $taxonomy ) {
			if ( in_array( $taxonomy->name, $excluded_taxonomies, true ) ) {
				continue;
			}

			$graphql_type = ucfirst( $taxonomy->graphql_single_name ?? $taxonomy->name );

			$fields[] = [
				'type_name'  => $graphql_type,
				'field_name' => 'seo',
				'config'     => [
					'type'        => 'SlimSeoFields',
					'description' => "Slim SEO data for {$taxonomy->label}.",
					'resolve'     => [ $this, 'resolve_taxonomy' ],
				],
			];
		}

		return $fields;
	}

	/**
	 * Resolves Slim SEO fields for a post type object.
	 *
	 * @param object $post WPGraphQL post model.
	 * @return array
	 * @since 1.0.0
	 */
	public function resolve_post_type( object $post ): array {
		$id      = $post->databaseId;
		$meta    = get_post_meta( $id, 'slim_seo', true ) ?: [];
		$url     = new CanonicalUrl();
		$robots  = new Robots( $url );
		$og_img  = $this->post_image( $id, 'facebook_image', $meta );
		$tw_img  = $this->post_image( $id, 'twitter_image', $meta ) ?: $og_img;

		return [
			'title'       => ( new Title() )->get_rendered_singular_value( $id ),
			'description' => ( new Description() )->get_rendered_singular_value( $id ),
			'noindex'     => $robots->get_singular_value( $id ),
			'canonical'   => ! empty( $meta['canonical'] ) ? $meta['canonical'] : (string) get_permalink( $id ),
			'og'          => [
				'title'       => ( new Title() )->get_rendered_singular_value( $id ),
				'description' => ( new Description() )->get_rendered_singular_value( $id ),
				'image'       => $og_img,
			],
			'twitter'     => [
				'card'  => (string) apply_filters( 'slim_seo_twitter_card_type', 'summary_large_image' ),
				'image' => $tw_img,
				'site'  => $this->twitter_site(),
			],
		];
	}

	/**
	 * Resolves Slim SEO fields for a taxonomy term object.
	 *
	 * @param object $term WPGraphQL term model.
	 * @return array
	 * @since 1.0.0
	 */
	public function resolve_taxonomy( object $term ): array {
		$id      = $term->databaseId;
		$meta    = get_term_meta( $id, 'slim_seo', true ) ?: [];
		$link    = get_term_link( $id );
		$og_img  = $this->term_image( $id, 'facebook_image', $meta );
		$tw_img  = $this->term_image( $id, 'twitter_image', $meta ) ?: $og_img;

		return [
			'title'       => ( new Title() )->get_rendered_term_value( $id ),
			'description' => ( new Description() )->get_rendered_term_value( $id ),
			'noindex'     => null,
			'canonical'   => ! empty( $meta['canonical'] ) ? $meta['canonical'] : ( is_wp_error( $link ) ? '' : $link ),
			'og'          => [
				'title'       => ( new Title() )->get_rendered_term_value( $id ),
				'description' => ( new Description() )->get_rendered_term_value( $id ),
				'image'       => $og_img,
			],
			'twitter'     => [
				'card'  => (string) apply_filters( 'slim_seo_twitter_card_type', 'summary_large_image' ),
				'image' => $tw_img,
				'site'  => $this->twitter_site(),
			],
		];
	}

	/**
	 * Returns the Twitter site handle from SlimSEO global settings.
	 * Returns empty string if not configured.
	 *
	 * @return string Twitter handle (e.g. '@username') or empty string.
	 * @since 1.0.0
	 */
	private function twitter_site(): string {
		$option = get_option( 'slim_seo', [] );
		$site   = $option['twitter_site'] ?? '';

		return (string) apply_filters( 'slim_seo_twitter_card_site', $site );
	}

	/**
	 * Returns the image URL for a post from SlimSEO meta or falls back to the featured image.
	 *
	 * @param int    $post_id  WordPress post ID.
	 * @param string $meta_key SlimSEO meta key ('facebook_image' or 'twitter_image').
	 * @param array  $meta     Already loaded slim_seo post meta.
	 * @return string Image URL or empty string.
	 * @since 1.0.0
	 */
	private function post_image( int $post_id, string $meta_key, array $meta ): string {
		if ( ! empty( $meta[ $meta_key ] ) ) {
			return ( new Image( $meta_key ) )->get_data_from_url( $meta[ $meta_key ] )['src'] ?? '';
		}

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( ! $thumbnail_id ) {
			return '';
		}

		$src = wp_get_attachment_image_src( $thumbnail_id, 'full' );
		return $src ? $src[0] : '';
	}

	/**
	 * Returns the image URL for a taxonomy term from SlimSEO meta.
	 *
	 * @param int    $term_id  WordPress term ID.
	 * @param string $meta_key SlimSEO meta key ('facebook_image' or 'twitter_image').
	 * @param array  $meta     Already loaded slim_seo term meta.
	 * @return string Image URL or empty string.
	 * @since 1.0.0
	 */
	private function term_image( int $term_id, string $meta_key, array $meta ): string {
		if ( ! empty( $meta[ $meta_key ] ) ) {
			return ( new Image( $meta_key ) )->get_data_from_url( $meta[ $meta_key ] )['src'] ?? '';
		}

		return '';
	}
}
