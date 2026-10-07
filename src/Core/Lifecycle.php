<?php
/**
 * Annual readiness monitoring, without automatic publisher requests.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

use Deckerweb\DailyScripture\Data\YearStore;

defined( 'ABSPATH' ) || exit;

/** Prepares year rollover using the site timezone and complete local years. */
final class Lifecycle {
	/**
	 * Register daily checks, also after upgrades that do not run activation.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'daily_scripture_year_check', array( $this, 'check' ) );
		$this->schedule();
		add_action( 'wp_initialize_site', array( $this, 'initialize_site' ), 200 );
	}

	/** Schedule one annual-readiness check per site, without duplicates. @return void */
	public function schedule(): void {
		if ( ! wp_next_scheduled( 'daily_scripture_year_check' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'daily_scripture_year_check' );
		}
	}

	/**
	 * Prepare a newly initialized site only when this plugin belongs to its network.
	 *
	 * @param \WP_Site $site Initialized site with available database tables.
	 * @return void
	 */
	public function initialize_site( \WP_Site $site ): void {
		$active = get_network_option( (int) $site->network_id, 'active_sitewide_plugins', array() );
		if ( ! is_array( $active ) || ! isset( $active[ plugin_basename( DAILY_SCRIPTURE_FILE ) ] ) ) {
			return;
		}
		switch_to_blog( (int) $site->blog_id );
		try {
			Activator::activate();
			$this->schedule();
		} finally {
			restore_current_blog();
		}
	}

	/**
	 * Emit a readiness integration point; never delete or download data implicitly.
	 *
	 * @return void
	 */
	public function check(): void {
		/**
		 * Fires after annual readiness validation.
		 *
		 * @since 0.2.0
		 *
		 * @param array $status Per-source readiness and messages.
		 */
		do_action( 'daily_scripture_year_readiness', $this->status() );
	}

	/**
	 * Compute readiness live so imports and year changes are immediately reflected.
	 *
	 * @return array
	 */
	public function status(): array {
		$current = (int) wp_date( 'Y' );
		$result  = array();
		foreach ( array( 'herrnhuter', 'bible2' ) as $source ) {
			foreach ( array( $current, $current + 1 ) as $year ) {
				try {
					$result[ $source ][ $year ] = ( new YearStore() )->read( $source, $year ) ? __( 'Fully installed', 'daily-scripture' ) : __( 'Import required', 'daily-scripture' );
				} catch ( \RuntimeException $error ) {
					$result[ $source ][ $year ] = $error->getMessage();
				}
			}
		}
		return $result;
	}
}
