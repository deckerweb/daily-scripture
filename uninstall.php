<?php
/**
 * Remove known data only when the site explicitly opted in.
 *
 * @package DailyScripture
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

wp_clear_scheduled_hook( 'daily_scripture_year_check' );
$daily_scripture_options = get_option( 'daily_scripture_settings', array() );
if ( ! empty( $daily_scripture_options['remove_data_on_uninstall'] ) ) {
	// Use the original autoloader without booting the plugin or scheduling jobs.
	spl_autoload_register(
		static function ( $class_name ) {
			$prefix = 'Deckerweb\\DailyScripture\\';
			if ( 0 !== strpos( $class_name, $prefix ) ) {
				return;
			}
			$file = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
			if ( is_file( $file ) ) {
				require_once $file;
			}
		}
	);
	foreach ( array( 'herrnhuter', 'bible2' ) as $daily_scripture_source ) {
		try {
			$daily_scripture_store = new Deckerweb\DailyScripture\Data\YearStore();
			foreach ( array_keys( $daily_scripture_store->inventory( $daily_scripture_source ) ) as $daily_scripture_year ) {
				$daily_scripture_store->delete( $daily_scripture_source, $daily_scripture_year );
			}
		} catch ( RuntimeException $daily_scripture_error ) {
			continue;
			// Leave unsafe/unwritable paths untouched; never recurse into arbitrary directories.
		}
	}
	foreach ( array_keys( ( new Deckerweb\DailyScripture\Bible\TranslationManager() )->bundled() ) as $daily_scripture_edition ) {
		try {
			( new Deckerweb\DailyScripture\Bible\Library() )->delete( $daily_scripture_edition );
		} catch ( RuntimeException $daily_scripture_error ) {
			continue; }
	}
}
delete_option( 'daily_scripture_settings' );

// Remove only this website's personal widget preferences.
global $wpdb;
delete_metadata( 'user', 0, $wpdb->get_blog_prefix() . 'daily_scripture_dashboard', '', true );
