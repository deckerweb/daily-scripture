<?php
/**
 * Plugin Name: Daily Scripture
 * Plugin URI: https://github.com/deckerweb/daily-scripture
 * Description: Daily readings and selected local Bible passages for WordPress websites, including Multisite. Flexible design, live previews, Gutenberg, Elementor, Bricks, shortcodes, personal dashboard widgets and JSON settings transfer.
 * Version: 1.0.1
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Author: David Decker – DECKERWEB
 * Author URI: https://github.com/deckerweb
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: daily-scripture
 * Domain Path: /languages/
 * Update URI: https://github.com/deckerweb/daily-scripture
 * GitHub Plugin URI: https://github.com/deckerweb/daily-scripture
 *
 * Copyright © 2026 David Decker – DECKERWEB.
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @package DailyScripture
 */

defined( 'ABSPATH' ) || exit;

define( 'DAILY_SCRIPTURE_VERSION', '1.0.1' );
define( 'DAILY_SCRIPTURE_FILE', __FILE__ );
define( 'DAILY_SCRIPTURE_DIR', plugin_dir_path( __FILE__ ) );
define( 'DAILY_SCRIPTURE_URL', plugin_dir_url( __FILE__ ) );

require_once DAILY_SCRIPTURE_DIR . 'includes/deckerweb-plugin-library/bootstrap.php';
deckerweb_library_register_v2( __FILE__, array(), DAILY_SCRIPTURE_DIR . 'includes/deckerweb-plugin-library' );

spl_autoload_register(
	/**
	 * Load only classes owned by this plugin.
	 *
	 * @param string $class_name Fully qualified class name.
	 * @return void
	 */
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
	/**
	 * Clear scheduled work during deactivation while retaining user data.
	 *
	 * @return void
	 */
	static function () {
		Deckerweb\DailyScripture\Core\Cleanup::deactivate();
	}
);

add_action(
	'plugins_loaded',
	/**
	 * Boot services after WordPress has loaded the active plugins.
	 *
	 * @return void
	 */
	static function () {
		( new Deckerweb\DailyScripture\Core\Plugin() )->boot();
	}
);
