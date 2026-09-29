<?php
/**
 * Installation defaults.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

defined( 'ABSPATH' ) || exit;

/** Activation does not create directories outside the guarded store. */
final class Activator {
	/**
	 * Install settings. Storage and scheduled checks are initialized on demand.
	 *
	 * @param bool $network_wide Whether network activation was requested.
	 * @return void
	 */
	public static function activate( bool $network_wide = false ): void {
		if ( $network_wide ) {
			wp_die( esc_html__( 'Daily Scripture bitte für einzelne Websites aktivieren; Netzwerkaktivierung wird noch nicht unterstützt.', 'daily-scripture' ) );
		}
		if ( false === get_option( 'daily_scripture_settings', false ) ) {
			add_option( 'daily_scripture_settings', Settings::defaults() );
		}
	}
}
