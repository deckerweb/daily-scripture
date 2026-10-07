<?php
/**
 * Reviewed freely licensed editions and canonical book identifiers.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Bible;

defined( 'ABSPATH' ) || exit;
/** Source payload fingerprints pin the reviewed text, not a mutable ZIP container. */
final class TranslationManager {
	/**
	 * Reviewed editions; texts are downloaded separately into uploads.
	 *
	 * @return array
	 */
	public function bundled(): array {
		return array(
			'luther-1912'      => array(
				'label'   => 'Luther 1912',
				'license' => 'Public Domain',
				'status'  => 'available',
				'url'     => 'https://ebible.org/Scriptures/deu1912_vpl.zip',
				'source'  => 'https://ebible.org/bible/details.php?id=deu1912',
				'entry'   => 'deu1912_vpl.txt',
				'format'  => 'vpl',
				'sha256'  => 'f6723776717b2e18e53dc81b0b7758aeb6ce969aee9cfb432d9b61006fa13dff',
				'verses'  => 31102,
			),
			'elberfelder-1905' => array(
				'label'   => __( 'Elberfelder 1905 (unrevised)', 'daily-scripture' ),
				'license' => 'Public Domain',
				'status'  => 'available',
				'url'     => 'https://ebible.org/Scriptures/deuelo_vpl.zip',
				'source'  => 'https://ebible.org/bible/details.php?id=deuelo',
				'entry'   => 'deuelo_vpl.txt',
				'format'  => 'vpl',
				'sha256'  => 'a9cd10022e49e80ced94f60e8b781e383cce4a80cff595b95531622804b3e826',
				'verses'  => 31102,
			),
			'menge-1939'       => array(
				'label'   => 'Menge 1939',
				'license' => 'Public Domain',
				'status'  => 'available',
				'url'     => 'https://downloads.sourceforge.net/project/zefania-sharp/Bibles/GER/Menge-Bibel/SF_2010-01-01_GER_MENG39_%28MENGE-BIBEL%29.zip',
				'source'  => 'https://sourceforge.net/projects/zefania-sharp/files/Bibles/GER/Menge-Bibel/',
				'entry'   => 'SF_2010-01-01_GER_MENG39_(MENGE-BIBEL).xml',
				'format'  => 'zefania',
				'sha256'  => 'dfc6344658e501c997c990e657d5e9d023e8403322b7c14030a3938a43b59c37',
				'verses'  => 31168,
			),
			'schlachter-1951'  => array(
				'label'       => 'Schlachter 1951',
				'license'     => 'CC BY 4.0',
				'license_url' => 'https://creativecommons.org/licenses/by/4.0/',
				'rights_url'  => 'https://ebible.org/deu1951/copyright.htm',
				'attribution' => '© 1951 Genfer Bibelgesellschaft. Übersetzung: Franz Eugen Schlachter; Überarbeitung 1951: Genfer Bibelgesellschaft.',
				'status'      => 'available',
				'url'         => 'https://ebible.org/Scriptures/deu1951_vpl.zip',
				'source'      => 'https://ebible.org/bible/details.php?id=deu1951',
				'entry'       => 'deu1951_vpl.txt',
				'format'      => 'vpl',
				'sha256'      => '3a68f2f8816f3da952b4a5e5da515ce8bf4081c412d77578805cfe7fdd4eb32c',
				'verses'      => 31102,
			),
		);
	}
	/**
	 * Localized display label without changing canonical source metadata.
	 *
	 * @param array $edition Reviewed registry entry.
	 * @return string License label for display.
	 */
	public static function license_label( array $edition ): string {
		return 'Public Domain' === $edition['license'] ? __( 'Public domain', 'daily-scripture' ) : $edition['license'];
	}

	/**
	 * Additional attribution for editions requiring it, shared by all surfaces.
	 *
	 * @param array $edition Reviewed registry entry.
	 * @return string Escaped attribution and license links.
	 */
	public static function attribution_html( array $edition ): string {
		if ( empty( $edition['attribution'] ) ) {
			return '';
		}
		return '<p>' . esc_html( $edition['attribution'] ) . '</p><p><a href="' . esc_url( $edition['license_url'] ) . '">' . esc_html__( 'License: CC BY 4.0 – attribution', 'daily-scripture' ) . '</a> · <a href="' . esc_url( $edition['rights_url'] ) . '">' . esc_html__( 'License notice at eBible', 'daily-scripture' ) . '</a></p><p>' . esc_html__( 'Verse text unchanged from the eBible package, prepared for this edition and displayed as an excerpt.', 'daily-scripture' ) . '</p>';
	}

	/**
	 * Resolve only a known edition.
	 *
	 * @param string $id Edition identifier.
	 * @return array
	 * @throws \RuntimeException For unknown editions.
	 */
	public static function edition( string $id ): array {
		$editions = ( new self() )->bundled();
		if ( ! isset( $editions[ $id ] ) ) {
			throw new \RuntimeException( esc_html__( 'Unknown Bible edition.', 'daily-scripture' ) ); }
		return $editions[ $id ];
	}
	/**
	 * Stable book codes with localized display names (66 canonical books).
	 *
	 * @return array
	 */
	public static function books(): array {
		return array(
			'GEN' => __( 'Genesis', 'daily-scripture' ),
			'EXO' => __( 'Exodus', 'daily-scripture' ),
			'LEV' => __( 'Leviticus', 'daily-scripture' ),
			'NUM' => __( 'Numbers', 'daily-scripture' ),
			'DEU' => __( 'Deuteronomy', 'daily-scripture' ),
			'JOS' => __( 'Joshua', 'daily-scripture' ),
			'JDG' => __( 'Judges', 'daily-scripture' ),
			'RUT' => __( 'Ruth', 'daily-scripture' ),
			'1SA' => __( '1 Samuel', 'daily-scripture' ),
			'2SA' => __( '2 Samuel', 'daily-scripture' ),
			'1KI' => __( '1 Kings', 'daily-scripture' ),
			'2KI' => __( '2 Kings', 'daily-scripture' ),
			'1CH' => __( '1 Chronicles', 'daily-scripture' ),
			'2CH' => __( '2 Chronicles', 'daily-scripture' ),
			'EZR' => __( 'Ezra', 'daily-scripture' ),
			'NEH' => __( 'Nehemiah', 'daily-scripture' ),
			'EST' => __( 'Esther', 'daily-scripture' ),
			'JOB' => __( 'Job', 'daily-scripture' ),
			'PSA' => __( 'Psalms', 'daily-scripture' ),
			'PRO' => __( 'Proverbs', 'daily-scripture' ),
			'ECC' => __( 'Ecclesiastes', 'daily-scripture' ),
			'SOL' => __( 'Song of Songs', 'daily-scripture' ),
			'ISA' => __( 'Isaiah', 'daily-scripture' ),
			'JER' => __( 'Jeremiah', 'daily-scripture' ),
			'LAM' => __( 'Lamentations', 'daily-scripture' ),
			'EZE' => __( 'Ezekiel', 'daily-scripture' ),
			'DAN' => __( 'Daniel', 'daily-scripture' ),
			'HOS' => __( 'Hosea', 'daily-scripture' ),
			'JOE' => __( 'Joel', 'daily-scripture' ),
			'AMO' => __( 'Amos', 'daily-scripture' ),
			'OBA' => __( 'Obadiah', 'daily-scripture' ),
			'JON' => __( 'Jonah', 'daily-scripture' ),
			'MIC' => __( 'Micah', 'daily-scripture' ),
			'NAH' => __( 'Nahum', 'daily-scripture' ),
			'HAB' => __( 'Habakkuk', 'daily-scripture' ),
			'ZEP' => __( 'Zephaniah', 'daily-scripture' ),
			'HAG' => __( 'Haggai', 'daily-scripture' ),
			'ZEC' => __( 'Zechariah', 'daily-scripture' ),
			'MAL' => __( 'Malachi', 'daily-scripture' ),
			'MAT' => __( 'Matthew', 'daily-scripture' ),
			'MAR' => __( 'Mark', 'daily-scripture' ),
			'LUK' => __( 'Luke', 'daily-scripture' ),
			'JOH' => __( 'John', 'daily-scripture' ),
			'ACT' => __( 'Acts', 'daily-scripture' ),
			'ROM' => __( 'Romans', 'daily-scripture' ),
			'1CO' => __( '1 Corinthians', 'daily-scripture' ),
			'2CO' => __( '2 Corinthians', 'daily-scripture' ),
			'GAL' => __( 'Galatians', 'daily-scripture' ),
			'EPH' => __( 'Ephesians', 'daily-scripture' ),
			'PHI' => __( 'Philippians', 'daily-scripture' ),
			'COL' => __( 'Colossians', 'daily-scripture' ),
			'1TH' => __( '1 Thessalonians', 'daily-scripture' ),
			'2TH' => __( '2 Thessalonians', 'daily-scripture' ),
			'1TI' => __( '1 Timothy', 'daily-scripture' ),
			'2TI' => __( '2 Timothy', 'daily-scripture' ),
			'TIT' => __( 'Titus', 'daily-scripture' ),
			'PHM' => __( 'Philemon', 'daily-scripture' ),
			'HEB' => __( 'Hebrews', 'daily-scripture' ),
			'JAM' => __( 'James', 'daily-scripture' ),
			'1PE' => __( '1 Peter', 'daily-scripture' ),
			'2PE' => __( '2 Peter', 'daily-scripture' ),
			'1JO' => __( '1 John', 'daily-scripture' ),
			'2JO' => __( '2 John', 'daily-scripture' ),
			'3JO' => __( '3 John', 'daily-scripture' ),
			'JUD' => __( 'Jude', 'daily-scripture' ),
			'REV' => __( 'Revelation', 'daily-scripture' ),
		);
	}
}
