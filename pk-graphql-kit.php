<?php
/**
 * @link              https://github.com/petrokrupenia
 * @since             1.0.0
 * @package           PkGraphQLKit
 *
 * @wordpress-plugin
 * Plugin Name:       PK GraphQL Kit
 * Plugin URI:        https://github.com/petrokrupenia/pk-graphql-kit
 * Description:       Developer toolkit for headless WordPress — CPT, ACF, WPGraphQL integrations and schema in one place
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.0
 * Author:            Petro Krupenia
 * Author URI:        https://github.com/petrokrupenia
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pkgraphqlkit
 * Domain Path:       /languages
 * Requires Plugins:  wp-graphql
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PK_GRAPHQL_KIT_VERSION', '1.0.0' );
define( 'PK_GRAPHQL_KIT_PATH', plugin_dir_path( __FILE__ ) );

require_once __DIR__ . '/vendor/autoload.php';

use PkGraphQLKit\PkGraphQLKit;
use PkGraphQLKit\Activator;
use PkGraphQLKit\Deactivator;

function pkgraphqlkit_activate(): void {
	Activator::activate();
}

function pkgraphqlkit_deactivate(): void {
	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'pkgraphqlkit_activate' );
register_deactivation_hook( __FILE__, 'pkgraphqlkit_deactivate' );

/**
 * Returns the instance of the PkGraphQLKit class.
 *
 * The main function is responsible for returning the one true instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $pkgraphqlkit = PkGraphQLKit(); ?>
 *
 * @return PkGraphQLKit
 * @since 1.0.0
 */
function pkgraphqlkit(): PkGraphQLKit {
	return PkGraphQLKit::get_instance();
}

add_action( 'plugins_loaded', function (): void {
	pkgraphqlkit()->boot();
}, 99 );