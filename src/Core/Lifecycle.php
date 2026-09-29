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
		if ( ! wp_next_scheduled( 'daily_scripture_year_check' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'daily_scripture_year_check' );
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
					$result[ $source ][ $year ] = ( new YearStore() )->read( $source, $year ) ? __( 'Vollständig installiert', 'daily-scripture' ) : __( 'Import erforderlich', 'daily-scripture' );
				} catch ( \RuntimeException $error ) {
					$result[ $source ][ $year ] = $error->getMessage();
				}
			}
		}
		return $result;
	}
}
