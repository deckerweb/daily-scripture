<?php
/**
 * Versioned JSON transfer without verse data or arbitrary option writes.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

use Deckerweb\DailyScripture\Core\Presentation;
use Deckerweb\DailyScripture\Core\Settings;

defined( 'ABSPATH' ) || exit;

/** Whitelisted configuration exchange; imports are staged for review in the form. */
final class SettingsTransfer {
	/** Maximum JSON bytes. */
	public const MAX_BYTES = 65536;

	/**
	 * Build a portable configuration envelope.
	 *
	 * @param string $kind Design or complete settings.
	 * @return array
	 * @throws \RuntimeException For unsupported kinds.
	 */
	public static function export( string $kind ): array {
		if ( ! in_array( $kind, array( 'design', 'settings' ), true ) ) {
			throw new \RuntimeException( esc_html__( 'Unbekanntes Exportformat.', 'daily-scripture' ) );
		}
		$settings = 'design' === $kind ? Presentation::sanitize( Settings::all() ) : ( new Admin() )->sanitize( Settings::all() );
		return array(
			'plugin'   => 'daily-scripture',
			'schema'   => 1,
			'kind'     => $kind,
			'version'  => DAILY_SCRIPTURE_VERSION,
			'settings' => $settings,
		);
	}

	/**
	 * Validate the entire document before returning any candidate changes.
	 *
	 * @param string $json JSON document.
	 * @return array Validated kind and settings.
	 * @throws \RuntimeException For malformed or incompatible input.
	 */
	public static function decode( string $json ): array {
		if ( strlen( $json ) > self::MAX_BYTES ) {
			throw new \RuntimeException( esc_html__( 'Die JSON-Datei darf höchstens 64 KiB groß sein.', 'daily-scripture' ) );
		}
		$data = json_decode( $json, true, 16 );
		if ( ! is_array( $data ) || JSON_ERROR_NONE !== json_last_error() || 'daily-scripture' !== ( $data['plugin'] ?? null ) || 1 !== ( $data['schema'] ?? null ) || ! in_array( $data['kind'] ?? null, array( 'design', 'settings' ), true ) || ! is_array( $data['settings'] ?? null ) ) {
			throw new \RuntimeException( esc_html__( 'Das ist keine unterstützte Daily-Scripture-Konfiguration (Schema 1).', 'daily-scripture' ) );
		}
		$expected = 'design' === $data['kind'] ? Presentation::defaults() : Settings::defaults();
		$input    = $data['settings'];
		// Schema 1 from 0.4 did not include headings; preserve current site choices.
		foreach ( array( 'title_herrnhuter', 'title_bible2' ) as $key ) {
			if ( ! array_key_exists( $key, $input ) ) {
				$input[ $key ] = Presentation::sanitize_heading( Settings::get( $key, '' ) );
			}
		}
		// Legacy schema-1 exports predate optional CSS sizes.
		foreach ( array( 'heading', 'verse', 'reference', 'meta', 'date' ) as $part ) {
			if ( ! array_key_exists( 'font_' . $part, $input ) ) {
				$input[ 'font_' . $part ] = '';
			}
		}
		if ( array_diff_key( $expected, $input ) || array_diff_key( $input, $expected ) ) {
			throw new \RuntimeException( esc_html__( 'Die Datei enthält fehlende oder unbekannte Einstellungen. Es wurde nichts übernommen.', 'daily-scripture' ) );
		}
		foreach ( $expected as $key => $default ) {
			if ( gettype( $input[ $key ] ) !== gettype( $default ) ) {
				throw new \RuntimeException( esc_html__( 'Ein Einstellungswert hat einen ungültigen Datentyp.', 'daily-scripture' ) );
			}
		}
		$clean = 'design' === $data['kind'] ? Presentation::sanitize( $input ) : ( new Admin() )->sanitize( $input );
		foreach ( $clean as $key => $value ) {
			if ( $input[ $key ] !== $value ) {
				throw new \RuntimeException( esc_html__( 'Die Datei enthält einen ungültigen Wert oder überschreitet einen zulässigen Bereich.', 'daily-scripture' ) );
			}
		}
		return array(
			'kind'     => $data['kind'],
			'settings' => $clean,
		);
	}

	/**
	 * Authorized POST-only download of saved settings.
	 *
	 * @return void
	 */
	public function download(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'daily-scripture' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'daily_scripture_export_settings' );
		$kind = isset( $_POST['kind'] ) && is_string( $_POST['kind'] ) ? sanitize_key( wp_unslash( $_POST['kind'] ) ) : '';
		try {
			$data = self::export( $kind );
		} catch ( \RuntimeException $error ) {
			wp_die( esc_html( $error->getMessage() ), '', array( 'response' => 400 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="daily-scripture-' . $kind . '.json"' );
		echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download, not HTML; all values are whitelisted.
		exit;
	}
}
