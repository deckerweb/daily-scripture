<?php
/**
 * Validation shared by imports and stored records.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Data;

defined( 'ABSPATH' ) || exit;

/** Validates complete calendar years without rewriting source text. */
final class YearValidator {
	/**
	 * Validate a source and supported calendar year.
	 *
	 * @param string $source Source identifier.
	 * @param int    $year Calendar year.
	 * @return void
	 * @throws \RuntimeException For invalid identifiers.
	 */
	public static function identity( string $source, int $year ): void {
		if ( ! in_array( $source, array( 'herrnhuter', 'bible2' ), true ) || $year < 1900 || $year > 2199 ) {
			throw new \RuntimeException( esc_html__( 'Ungültige Quelle oder Jahreszahl.', 'daily-scripture' ) );
		}
	}

	/**
	 * Enforce the publisher's rolling display/import window.
	 *
	 * @param string $source Source identifier.
	 * @param int    $year Calendar year.
	 * @return bool
	 */
	public static function allowed( string $source, int $year ): bool {
		$current = (int) wp_date( 'Y' );
		return 'herrnhuter' !== $source || abs( $year - $current ) <= 1;
	}

	/**
	 * Check plain source text, preserving whitespace and Unicode.
	 *
	 * @param mixed $value Candidate text.
	 * @param int   $limit Maximum bytes.
	 * @return string
	 * @throws \RuntimeException For invalid content.
	 */
	public static function text( $value, int $limit = 20000 ): string {
		if ( ! is_string( $value ) || '' === trim( $value ) || strlen( $value ) > $limit || ! preg_match( '//u', $value ) || preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $value ) ) {
			throw new \RuntimeException( esc_html__( 'Ein Textfeld fehlt, ist zu lang oder enthält ungültige Zeichen.', 'daily-scripture' ) );
		}
		return $value;
	}

	/**
	 * Validate all days, including leap days, and exactly two verses per day.
	 *
	 * @param array  $days Date-keyed verse pairs.
	 * @param string $source Source identifier.
	 * @param int    $year Calendar year.
	 * @return void
	 * @throws \RuntimeException For incomplete or malformed data.
	 */
	public static function days( array $days, string $source, int $year ): void {
		self::identity( $source, $year );
		$date = new \DateTimeImmutable( "$year-01-01", new \DateTimeZone( 'UTC' ) );
		$end  = $date->modify( '+1 year' );
		if ( count( $days ) !== (int) $date->diff( $end )->days ) {
			throw new \RuntimeException( esc_html__( 'Die Datei muss ein vollständiges Kalenderjahr mit 365 bzw. 366 Tagen enthalten.', 'daily-scripture' ) );
		}
		while ( $date < $end ) {
			$key   = $date->format( 'Y-m-d' );
			$items = $days[ $key ] ?? null;
			if ( ! is_array( $items ) || array_keys( $items ) !== array( 0, 1 ) ) {
				throw new \RuntimeException( esc_html__( 'Ein Tagesdatum fehlt oder enthält kein vollständiges Verspaar.', 'daily-scripture' ) );
			}
			foreach ( $items as $item ) {
				if ( ! is_array( $item ) ) {
					throw new \RuntimeException( esc_html__( 'Ungültiger Tagesdatensatz.', 'daily-scripture' ) );
				}
				self::text( $item['text'] ?? null );
				self::text( $item['reference'] ?? null, 1000 );
				if ( isset( $item['intro'] ) && '' !== $item['intro'] ) {
					self::text( $item['intro'] );
				}
			}
			$date = $date->modify( '+1 day' );
		}
	}
}
