<?php
/**
 * Links only: this class makes no network requests.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

defined( 'ABSPATH' ) || exit;

/** Builds documented Bibleserver URLs independently of the displayed edition. */
final class ReferenceLink {
	/**
	 * Common supported link destinations.
	 *
	 * @return array
	 */
	public static function translations(): array {
		return array(
			'LUT' => 'Luther 2017',
			'ELB' => 'Elberfelder',
			'EU'  => 'Einheitsübersetzung',
			'HFA' => 'Hoffnung für alle',
			'SLT' => 'Schlachter 2000',
			'NGÜ' => 'Neue Genfer Übersetzung',
			'GNB' => 'Gute Nachricht Bibel',
			'NLB' => 'Neues Leben',
			'ZB'  => 'Zürcher Bibel',
		);
	}

	/**
	 * Construct a safely encoded reference link.
	 *
	 * @param string $reference Unmodified source reference.
	 * @param string $translation Optional destination override for preview.
	 * @return string
	 */
	public static function url( string $reference, string $translation = '' ): string {
		$translation = '' !== $translation ? $translation : (string) Settings::get( 'bibleserver_translation', 'LUT' );
		if ( ! isset( self::translations()[ $translation ] ) ) {
			$translation = 'LUT';
		}
		return 'https://www.bibleserver.com/' . rawurlencode( $translation ) . '/' . rawurlencode( $reference );
	}
}
