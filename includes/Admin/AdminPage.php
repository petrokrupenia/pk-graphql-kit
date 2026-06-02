<?php

namespace PkGraphQLKit\Admin;

use PkGraphQLKit\PostType\PostTypeHelper;
use PkGraphQLKit\PostType\TaxonomyHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AdminPage {

	/**
	 * Registers the admin_menu hook.
	 * Called once from PkGraphQLKit::boot().
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {
		add_action( 'admin_menu', [ $this, 'register' ] );
	}

	/**
	 * Registers the admin page under the Settings menu.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register(): void {
		add_options_page(
			'PK GraphQL Kit',
			'PK GraphQL Kit',
			'manage_options',
			'pk-graphql-kit',
			[ $this, 'render' ]
		);
	}

	/**
	 * Renders the admin page HTML.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render(): void {
		$env           = wp_get_environment_type();
		$endpoint_slug = function_exists( 'get_graphql_setting' ) ? get_graphql_setting( 'graphql_endpoint', 'graphql' ) : 'graphql';
		$endpoint_url  = home_url( '/' . $endpoint_slug );
		$acf_active    = class_exists( 'ACF' );
		$integrations  = pkgraphqlkit()->integrations()->active();
		$post_types    = PostTypeHelper::graphql_types();
		$taxonomies    = TaxonomyHelper::graphql_types();

		$env_colors = [
			'local'       => '#00a32a',
			'development' => '#00a32a',
			'staging'     => '#dba617',
			'production'  => '#d63638',
		];
		$env_color = $env_colors[ $env ] ?? '#72777c';
		?>

		<div class="wrap">

			<h1>PK GraphQL Kit</h1>
			<p style="color:#646970; margin-top:-8px;">Developer toolkit for headless WordPress — CPT, ACF, WPGraphQL schema in one place. <strong>v<?php echo esc_html( PK_GRAPHQL_KIT_VERSION ); ?></strong></p>

			<?php $this->render_section( 'Status', function () use ( $env, $env_color, $endpoint_url, $acf_active ): void { ?>
				<table class="widefat striped" style="max-width:600px;">
					<tbody>
						<tr>
							<td style="width:200px;"><strong><?php esc_html_e( 'Environment', 'pkgraphqlkit' ); ?></strong></td>
							<td>
								<span style="display:inline-block; padding:2px 10px; border-radius:3px; background:<?php echo esc_attr( $env_color ); ?>; color:#fff; font-size:12px; font-weight:600; text-transform:uppercase;">
									<?php echo esc_html( $env ); ?>
								</span>
							</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'GraphQL Endpoint', 'pkgraphqlkit' ); ?></strong></td>
							<td><a href="<?php echo esc_url( $endpoint_url ); ?>" target="_blank"><?php echo esc_html( $endpoint_url ); ?></a></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'ACF', 'pkgraphqlkit' ); ?></strong></td>
							<td><?php echo $acf_active ? '<span style="color:#00a32a;">&#10003; Active</span>' : '<span style="color:#72777c;">&#8211; Not active</span>'; ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'WPGraphQL', 'pkgraphqlkit' ); ?></strong></td>
							<td><span style="color:#00a32a;">&#10003; Active</span></td>
						</tr>
					</tbody>
				</table>
			<?php } );

			$this->render_section(
				sprintf( 'GraphQL Schema — Post Types (%d)', count( $post_types ) ),
				function () use ( $post_types ): void {
					if ( empty( $post_types ) ) {
						echo '<p style="color:#72777c;">No post types with GraphQL support found.</p>';
						return;
					}
					?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Slug', 'pkgraphqlkit' ); ?></th>
								<th><?php esc_html_e( 'Label', 'pkgraphqlkit' ); ?></th>
								<th><?php esc_html_e( 'GraphQL Single Name', 'pkgraphqlkit' ); ?></th>
								<th><?php esc_html_e( 'GraphQL Plural Name', 'pkgraphqlkit' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $post_types as $cpt ) : ?>
								<tr>
									<td><code><?php echo esc_html( $cpt->name ); ?></code></td>
									<td><?php echo esc_html( $cpt->label ); ?></td>
									<td><code><?php echo esc_html( $cpt->graphql_single_name ?? '—' ); ?></code></td>
									<td><code><?php echo esc_html( $cpt->graphql_plural_name ?? '—' ); ?></code></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php
				}
			);

			$this->render_section(
				sprintf( 'GraphQL Schema — Taxonomies (%d)', count( $taxonomies ) ),
				function () use ( $taxonomies ): void {
					if ( empty( $taxonomies ) ) {
						echo '<p style="color:#72777c;">No taxonomies with GraphQL support found.</p>';
						return;
					}
					?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Slug', 'pkgraphqlkit' ); ?></th>
								<th><?php esc_html_e( 'Label', 'pkgraphqlkit' ); ?></th>
								<th><?php esc_html_e( 'GraphQL Single Name', 'pkgraphqlkit' ); ?></th>
								<th><?php esc_html_e( 'GraphQL Plural Name', 'pkgraphqlkit' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $taxonomies as $taxonomy ) : ?>
								<tr>
									<td><code><?php echo esc_html( $taxonomy->name ); ?></code></td>
									<td><?php echo esc_html( $taxonomy->label ); ?></td>
									<td><code><?php echo esc_html( $taxonomy->graphql_single_name ?? '—' ); ?></code></td>
									<td><code><?php echo esc_html( $taxonomy->graphql_plural_name ?? '—' ); ?></code></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php
				}
			);

			$this->render_section(
				sprintf( 'Active Integrations (%d)', count( $integrations ) ),
				function () use ( $integrations ): void {
					if ( empty( $integrations ) ) {
						echo '<p style="color:#72777c;">No active integrations found.</p>';
						return;
					}

					foreach ( $integrations as $integration ) :
						$fields    = $integration->get_graphql_fields();
						$types     = $integration->get_graphql_types();
						$mutations = $integration->get_graphql_mutations();
						?>
						<div style="margin-bottom:20px; padding:12px 16px; border:1px solid #c3c4c7; border-radius:4px; background:#fff;">
							<h3 style="margin:0 0 10px;">
								<?php echo esc_html( $integration->get_label() ); ?>
								<code style="font-size:12px; color:#646970; font-weight:400; margin-left:8px;"><?php echo esc_html( $integration->get_plugin_file() ); ?></code>
							</h3>
							<p style="margin:0; color:#646970;">
								<span style="margin-right:16px;">&#10022; <strong><?php echo count( $types ); ?></strong> <?php esc_html_e( 'types', 'pkgraphqlkit' ); ?></span>
								<span style="margin-right:16px;">&#10022; <strong><?php echo count( $fields ); ?></strong> <?php esc_html_e( 'fields', 'pkgraphqlkit' ); ?></span>
								<span>&#10022; <strong><?php echo count( $mutations ); ?></strong> <?php esc_html_e( 'mutations', 'pkgraphqlkit' ); ?></span>
							</p>

							<?php if ( ! empty( $types ) ) : ?>
								<p style="margin:8px 0 2px; font-size:12px; color:#646970; text-transform:uppercase; letter-spacing:.5px;">Types</p>
								<p style="margin:0;"><?php
									echo implode( ', ', array_map(
										fn( $t ) => '<code>' . esc_html( $t['type_name'] ) . '</code>',
										$types
									) );
								?></p>
							<?php endif; ?>

							<?php if ( ! empty( $fields ) ) : ?>
								<p style="margin:8px 0 2px; font-size:12px; color:#646970; text-transform:uppercase; letter-spacing:.5px;">Fields</p>
								<p style="margin:0;"><?php
									echo implode( ', ', array_map(
										fn( $f ) => '<code>' . esc_html( $f['type_name'] . '.' . $f['field_name'] ) . '</code>',
										$fields
									) );
								?></p>
							<?php endif; ?>

							<?php if ( ! empty( $mutations ) ) : ?>
								<p style="margin:8px 0 2px; font-size:12px; color:#646970; text-transform:uppercase; letter-spacing:.5px;">Mutations</p>
								<p style="margin:0;"><?php
									echo implode( ', ', array_map(
										fn( $m ) => '<code>' . esc_html( $m['name'] ) . '</code>',
										$mutations
									) );
								?></p>
							<?php endif; ?>
						</div>
					<?php endforeach;
				}
			); ?>

		</div>
		<?php
	}

	/**
	 * Renders a titled card section wrapping a callable content block.
	 *
	 * @param string   $title   Section heading.
	 * @param callable $content Callable that outputs the section body HTML.
	 * @return void
	 * @since 1.0.0
	 */
	private function render_section( string $title, callable $content ): void {
		?>
		<div class="card" style="max-width:100%; margin-bottom:20px; padding:16px 20px;">
			<h2 style="margin-top:0;"><?php echo esc_html( $title ); ?></h2>
			<?php $content(); ?>
		</div>
		<?php
	}
}
