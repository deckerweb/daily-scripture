<?php
/**
 * Plugin Name: Daily Scripture
 * Plugin URI: https://github.com/deckerweb/daily-scripture
 * Description: Daily verses from Die Losungen and Bible 2.0, plus selected passages from four local Bible editions. Includes live previews, ten layouts, flexible typography, Gutenberg, Elementor, Bricks, shortcodes, a compact dashboard widget and JSON settings transfer.
 * Version: 0.16.4
 * Update URI: https://github.com/deckerweb/daily-scripture
 * GitHub Plugin URI: https://github.com/deckerweb/daily-scripture
 * Author: David Decker
 * Author URI: https://github.com/deckerweb
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: daily-scripture
 * Domain Path: /languages
 * Requires at least: 6.6
 * Requires PHP: 8.0
 *
 * @package DailyScripture
 */

defined( 'ABSPATH' ) || exit;

define( 'DAILY_SCRIPTURE_VERSION', '0.16.4' );
define( 'DAILY_SCRIPTURE_FILE', __FILE__ );
define( 'DAILY_SCRIPTURE_DIR', plugin_dir_path( __FILE__ ) );
define( 'DAILY_SCRIPTURE_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register(
	static function ( $class_name ) {
		$prefix = 'Deckerweb\\DailyScripture\\';
		if ( strncmp( $class_name, $prefix, strlen( $prefix ) ) !== 0 ) {
			return;
		}
		$relative = str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) );
		$file     = DAILY_SCRIPTURE_DIR . 'src/' . $relative . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

register_activation_hook( __FILE__, array( 'Deckerweb\\DailyScripture\\Core\\Activator', 'activate' ) );
register_deactivation_hook(
	__FILE__,
	static function () {
		wp_clear_scheduled_hook( 'daily_scripture_year_check' );
	}
);

add_action(
	'plugins_loaded',
	static function () {
		( new Deckerweb\DailyScripture\Core\Plugin() )->boot();
	}
);
