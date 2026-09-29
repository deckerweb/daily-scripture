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
				'label'   => 'Elberfelder 1905 (unrevidiert)',
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
		return 'Public Domain' === $edition['license'] ? __( 'Gemeinfrei (Public Domain)', 'daily-scripture' ) : $edition['license'];
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
		return '<p>' . esc_html( $edition['attribution'] ) . '</p><p><a href="' . esc_url( $edition['license_url'] ) . '">' . esc_html__( 'Lizenz: CC BY 4.0 – Namensnennung', 'daily-scripture' ) . '</a> · <a href="' . esc_url( $edition['rights_url'] ) . '">' . esc_html__( 'Lizenznachweis bei eBible', 'daily-scripture' ) . '</a></p><p>' . esc_html__( 'Verswortlaut unverändert aus dem eBible-Textpaket; für diese Ausgabe technisch aufbereitet und als Auszug dargestellt.', 'daily-scripture' ) . '</p>';
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
			throw new \RuntimeException( esc_html__( 'Unbekannte Bibelausgabe.', 'daily-scripture' ) ); }
		return $editions[ $id ];
	}
	/**
	 * Stable book codes with German display names (66 canonical books).
	 *
	 * @return array
	 */
	public static function books(): array {
		return array(
			'GEN' => '1. Mose',
			'EXO' => '2. Mose',
			'LEV' => '3. Mose',
			'NUM' => '4. Mose',
			'DEU' => '5. Mose',
			'JOS' => 'Josua',
			'JDG' => 'Richter',
			'RUT' => 'Rut',
			'1SA' => '1. Samuel',
			'2SA' => '2. Samuel',
			'1KI' => '1. Könige',
			'2KI' => '2. Könige',
			'1CH' => '1. Chronik',
			'2CH' => '2. Chronik',
			'EZR' => 'Esra',
			'NEH' => 'Nehemia',
			'EST' => 'Ester',
			'JOB' => 'Hiob',
			'PSA' => 'Psalmen',
			'PRO' => 'Sprüche',
			'ECC' => 'Prediger',
			'SOL' => 'Hohelied',
			'ISA' => 'Jesaja',
			'JER' => 'Jeremia',
			'LAM' => 'Klagelieder',
			'EZE' => 'Hesekiel',
			'DAN' => 'Daniel',
			'HOS' => 'Hosea',
			'JOE' => 'Joel',
			'AMO' => 'Amos',
			'OBA' => 'Obadja',
			'JON' => 'Jona',
			'MIC' => 'Micha',
			'NAH' => 'Nahum',
			'HAB' => 'Habakuk',
			'ZEP' => 'Zefanja',
			'HAG' => 'Haggai',
			'ZEC' => 'Sacharja',
			'MAL' => 'Maleachi',
			'MAT' => 'Matthäus',
			'MAR' => 'Markus',
			'LUK' => 'Lukas',
			'JOH' => 'Johannes',
			'ACT' => 'Apostelgeschichte',
			'ROM' => 'Römer',
			'1CO' => '1. Korinther',
			'2CO' => '2. Korinther',
			'GAL' => 'Galater',
			'EPH' => 'Epheser',
			'PHI' => 'Philipper',
			'COL' => 'Kolosser',
			'1TH' => '1. Thessalonicher',
			'2TH' => '2. Thessalonicher',
			'1TI' => '1. Timotheus',
			'2TI' => '2. Timotheus',
			'TIT' => 'Titus',
			'PHM' => 'Philemon',
			'HEB' => 'Hebräer',
			'JAM' => 'Jakobus',
			'1PE' => '1. Petrus',
			'2PE' => '2. Petrus',
			'1JO' => '1. Johannes',
			'2JO' => '2. Johannes',
			'3JO' => '3. Johannes',
			'JUD' => 'Judas',
			'REV' => 'Offenbarung',
		);
	}
}
