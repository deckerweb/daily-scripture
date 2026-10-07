<?php
/**
 * Plugin-owned lifecycle cleanup, preserving data unless a site opted in.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

defined( 'ABSPATH' ) || exit;

/** Keep site data isolated during deactivation and physical network uninstall. */
final class Cleanup {
	/**
	 * Visit sites in bounded batches, restoring the caller's site after each visit.
	 *
	 * @param callable $operation Operation in the current site's context.
	 * @param bool     $all_sites Whether to include every site in Multisite.
	 * @return void
	 */
	public static function sites( callable $operation, bool $all_sites ): void {
		if ( ! is_multisite() || ! $all_sites ) {
			$operation();
			return;
		}
		$offset = 0;
		do {
			$ids = get_sites(
				array(
					'fields'  => 'ids',
					'number'  => 100,
					'offset'  => $offset,
					'orderby' => 'id',
					'order'   => 'ASC',
				)
			);
			foreach ( $ids as $id ) {
				switch_to_blog( (int) $id );
				try {
					$operation();
				} finally {
					restore_current_blog();
				}
			}
			$count   = count( $ids );
			$offset += $count;
		} while ( 100 === $count );
	}

	/**
	 * Clear scheduled work on the affected sites; preserve all user data.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		self::sites(
			/**
			 * Remove this site's scheduled annual check during deactivation.
			 *
			 * @return void
			 */
			static function (): void {
				wp_clear_scheduled_hook( 'daily_scripture_year_check' );
			},
			is_multisite() && is_plugin_active_for_network( plugin_basename( DAILY_SCRIPTURE_FILE ) )
		);
	}

	/**
	 * Remove this site's caches and optionally its settings, preferences and texts.
	 *
	 * Deleting a plugin physically applies each site's own deletion preference.
	 * No other plugin's data or network-wide Library settings are removed here.
	 *
	 * @return void
	 */
	public static function site(): void {
		global $wpdb;
		wp_clear_scheduled_hook( 'daily_scripture_year_check' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Uninstall enumerates exact plugin-owned transient names once; cached results would be stale.
		$names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_daily_scripture_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_daily_scripture_' ) . '%'
			)
		);
		foreach ( $names as $name ) {
			$key = preg_replace( '/^_transient_(?:timeout_)?/', '', $name );
			if ( preg_match( '/^daily_scripture_(?:packages_(?:herrnhuter|bible2)_\d{4}|notice_\d+|library_notice_\d+)$/D', $key ) ) {
				delete_transient( $key );
			}
		}
		// Known availability keys also reach an external object cache.
		foreach ( array( 'herrnhuter', 'bible2' ) as $source ) {
			for ( $year = 2000; $year <= 2100; ++$year ) {
				delete_transient( 'daily_scripture_packages_' . $source . '_' . $year );
			}
		}
		$options = get_option( 'daily_scripture_settings', array() );
		if ( ! is_array( $options ) || empty( $options['remove_data_on_uninstall'] ) ) {
			return;
		}
		$root = WP_CONTENT_DIR . '/uploads/daily-scripture/' . ( is_multisite() ? 'sites/' . get_current_blog_id() . '/' : '' );
		if ( is_dir( $root . 'data' ) && ! is_link( $root . 'data' ) ) {
			foreach ( array( 'herrnhuter', 'bible2' ) as $source ) {
				try {
					$store = new \Deckerweb\DailyScripture\Data\YearStore();
					foreach ( $store->existing_years( $source ) as $year ) {
						$store->delete( $source, $year );
					}
				} catch ( \RuntimeException $error ) {
					// Leave unsafe paths untouched rather than following links or recursing.
					continue;
				}
			}
		}
		if ( is_dir( $root . 'bibles' ) && ! is_link( $root . 'bibles' ) ) {
			foreach ( array_keys( ( new \Deckerweb\DailyScripture\Bible\TranslationManager() )->bundled() ) as $edition ) {
				try {
					( new \Deckerweb\DailyScripture\Bible\Library() )->delete( $edition );
				} catch ( \RuntimeException $error ) {
					continue;
				}
			}
		}
		delete_option( 'daily_scripture_settings' );
		delete_option( \Deckerweb\DailyScripture\Admin\DashboardReadings::OPTION );
		delete_metadata( 'user', 0, $wpdb->get_blog_prefix() . 'daily_scripture_dashboard', '', true );
		if ( is_multisite() ) {
			$site = get_site();
			if ( $site && get_current_blog_id() === get_main_site_id( (int) $site->network_id ) ) {
				delete_metadata( 'user', 0, 'daily_scripture_network_dashboard_' . $site->network_id, '', true );
			}
		}
	}
}
