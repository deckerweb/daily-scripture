<?php
/**
 * Discover official annual packages and reuse the validated local importer.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Downloads;

use Deckerweb\DailyScripture\Data\{Importer, YearStore, YearValidator, LocalFiles};

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.WP.AlternativeFunctions
// DOM properties are defined by PHP.
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

/** Catalogues never fetch verse text during frontend rendering. */
final class AnnualPackages {
	/** Cache is per site and timezone year; stale catalogues cannot authorize imports. */
	private const PREFIX = 'daily_scripture_packages_';
	/**
	 * Read last explicit availability check, or refresh one publisher.
	 *
	 * @param string $source Provider.
	 * @param bool   $refresh Fetch catalogue now.
	 * @return array|null
	 */
	public function catalogue( string $source, bool $refresh = false ): ?array {
		YearValidator::identity( $source, 2000 );
		$key = self::PREFIX . $source . '_' . wp_date( 'Y' );
		if ( ! $refresh ) {
			$value = get_transient( $key );
			return is_array( $value ) ? $value : null;
		}
		$result = array(
			'checked' => time(),
			'items'   => array(),
			'error'   => '',
		);
		try {
			$url             = 'herrnhuter' === $source ? 'https://www.losungen.de/download/' : 'https://bible2.net/service/TheWord/twd11/current?format=json';
			$result['items'] = $this->decode( $source, Remote::get( $url ) );
		} catch ( \RuntimeException $error ) {
			$result['error'] = $error->getMessage();
		}
		set_transient( $key, $result, '' === $result['error'] ? 6 * HOUR_IN_SECONDS : 5 * MINUTE_IN_SECONDS );
		return $result;
	}
	/**
	 * Extract only supported provider-owned packages from untrusted catalogue data.
	 *
	 * @param string $source Provider.
	 * @param string $body Catalogue bytes.
	 * @return array Package ID keyed records.
	 * @throws \RuntimeException On a malformed catalogue.
	 */
	public function decode( string $source, string $body ): array {
		$items   = array();
		$current = (int) wp_date( 'Y' );
		if ( 'herrnhuter' === $source ) {
			if ( ! class_exists( '\DOMDocument' ) ) {
				throw new \RuntimeException( esc_html__( 'DOM/XML is required.', 'daily-scripture' ) );
			}
			$previous = libxml_use_internal_errors( true );
			try {
				$doc = new \DOMDocument();
				$doc->loadHTML( $body, LIBXML_NONET );
				foreach ( $doc->getElementsByTagName( 'a' ) as $link ) {
					$href = $link->getAttribute( 'href' );
					if ( preg_match( '~^(?:https://www\.losungen\.de)?(/fileadmin/media-losungen/download/Losung_(\d{4})_XML\.zip)$~D', $href, $match ) && YearValidator::allowed( $source, (int) $match[2] ) ) {
						$id           = 'losungen-' . $match[2];
						$items[ $id ] = array(
							'year'  => (int) $match[2],
							'label' => 'Die Losungen · ' . $match[2],
							'url'   => 'https://www.losungen.de' . $match[1],
						);
					}
				}
			} finally {
				libxml_clear_errors();
				libxml_use_internal_errors( $previous );
			}
		} elseif ( 'bible2' === $source ) {
			$list = json_decode( $body, true );
			if ( ! is_array( $list ) || count( $list ) > 3000 ) {
				throw new \RuntimeException( esc_html__( 'The provider directory is unreadable.', 'daily-scripture' ) );
			}
			foreach ( $list as $entry ) {
				if ( ! is_array( $entry ) || 'file' !== ( $entry['category'] ?? '' ) || ! is_int( $entry['year'] ?? null ) || $entry['year'] < $current || $entry['year'] > $current + 1 || ! is_string( $entry['bible'] ?? null ) || ! preg_match( '/^[A-Za-z0-9]+$/D', $entry['bible'] ) || ! is_string( $entry['lang'] ?? null ) || ! preg_match( '/^[a-z]{2,3}(?:-[A-Za-z]+)*$/D', $entry['lang'] ) || ! is_string( $entry['biblename'] ?? null ) ) {
					continue;
				}
				$slug = $entry['lang'] . '_' . $entry['bible'] . '_' . $entry['year'];
				$url  = 'https://bible2.net/service/TheWord/twd11/' . $slug . '.twd';
				if ( ( $entry['url'] ?? '' ) !== $url ) {
					continue;
				}
				$items[ $slug ] = array(
					'year'  => $entry['year'],
					'label' => $entry['lang'] . ' · ' . sanitize_text_field( $entry['biblename'] ) . ' · ' . $entry['year'],
					'url'   => $url,
				);
			}
			// German first, then alphabetically, with all offered languages available.
			/**
			* Sort German publisher editions before other catalogue labels.
			*
			* @param array $a First catalogue entry.
			* @param array $b Second catalogue entry.
			* @return int
			*/
			$compare_editions = static fn( $a, $b ) => strcmp( ( str_starts_with( $a['label'], 'de ·' ) ? '0' : '1' ) . $a['label'], ( str_starts_with( $b['label'], 'de ·' ) ? '0' : '1' ) . $b['label'] );
			uasort( $items, $compare_editions );
		}
		if ( ! $items ) {
			throw new \RuntimeException( esc_html__( 'No matching annual packages were found in the provider directory. Manual upload is still available.', 'daily-scripture' ) );
		}
		return $items;
	}
	/**
	 * Download a selected catalogue ID and atomically import only validated data.
	 *
	 * @param string $source Provider.
	 * @param string $id Catalogue key, never an arbitrary URL.
	 * @param bool   $replace Explicit replacement permission.
	 * @return void
	 * @throws \RuntimeException On unavailable or invalid data.
	 */
	public function install( string $source, string $id, bool $replace ): void {
		$catalogue = $this->catalogue( $source );
		$item      = $catalogue['items'][ $id ] ?? null;
		if ( ! is_array( $item ) || ! empty( $catalogue['error'] ) ) {
			throw new \RuntimeException( esc_html__( 'Please check the download source first and choose an available package.', 'daily-scripture' ) );
		}
		// Recheck the fixed URL pattern even for cached metadata.
		$pattern = 'herrnhuter' === $source ? '~^https://www\.losungen\.de/fileadmin/media-losungen/download/Losung_\d{4}_XML\.zip$~D' : '~^https://bible2\.net/service/TheWord/twd11/[a-z]{2,3}(?:-[A-Za-z]+)*_[A-Za-z0-9]+_\d{4}\.twd$~D';
		if ( ! preg_match( $pattern, $item['url'] ) || ! YearValidator::allowed( $source, $item['year'] ) ) {
			throw new \RuntimeException( esc_html__( 'The package is no longer approved.', 'daily-scripture' ) );
		}
		$body = Remote::get( $item['url'], Importer::MAX_BYTES );
		$file = LocalFiles::directory( 'downloads' ) . '/.annual-' . wp_generate_uuid4() . '.bin';
		try {
			if ( strlen( $body ) !== file_put_contents( $file, $body, LOCK_EX ) ) {
				throw new \RuntimeException( esc_html__( 'The download could not be stored temporarily.', 'daily-scripture' ) );
			}
			chmod( $file, 0600 );
			$record = ( new Importer() )->parse( $file, $source, $item['year'] );
			( new YearStore() )->save( $record, $replace );
		} finally {
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}
	}
}
