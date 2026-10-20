<?php
/**
 * Read-only, progressively enhanced download diagnostics.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

use Deckerweb\DailyScripture\Downloads\DownloadError;

defined( 'ABSPATH' ) || exit;

/** Display only approved technical codes to the source administrator. */
final class DownloadDiagnostics {
	/**
	 * Render collapsed details with a manually selectable copy fallback.
	 *
	 * @param array $data Safe download diagnostic.
	 * @return void
	 */
	public static function render( array $data ): void {
		if ( ! current_user_can( 'manage_options' ) || ! $data ) {
			return;
		}
		$codes = DownloadError::restore( $data )->diagnostic();
		$text  = 'Daily Scripture ' . DAILY_SCRIPTURE_VERSION . "\nReason: " . $codes['reason'];
		foreach ( array(
			'http' => 'HTTP',
			'curl' => 'cURL',
		) as $key => $label ) {
			if ( $codes[ $key ] ) {
				$text .= "\n" . $label . ': ' . $codes[ $key ];
			}
		}
		echo '<details class="ds-download-diagnostic"><summary>' . esc_html__( 'Technical details', 'daily-scripture' ) . '</summary><p><label>' . esc_html__( 'Download diagnostic codes', 'daily-scripture' ) . '<textarea readonly rows="4" class="large-text code">' . esc_textarea( $text ) . '</textarea></label></p><p><button type="button" class="button" data-ds-diagnostic-copy hidden>' . esc_html__( 'Copy diagnostic', 'daily-scripture' ) . '</button> <span role="status" aria-live="polite"></span></p></details>';
	}
}
