<?php
/**
 * Bounded HTTPS requests restricted to reviewed publisher endpoints.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Downloads;

defined( 'ABSPATH' ) || exit;

/** No user-supplied URLs, redirects, cookies or credentials. */
final class Remote {
	/**
	 * Retrieve a reviewed HTTPS endpoint with WordPress SSRF protection.
	 *
	 * @param string $url Catalogue-controlled URL.
	 * @param int    $limit Maximum body bytes.
	 * @param int    $redirects Internal bounded SourceForge redirect count.
	 * @return string
	 * @throws \RuntimeException On remote or validation failure.
	 */
	public static function get( string $url, int $limit = 2097152, int $redirects = 0 ): string {
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || 'https' !== ( $parts['scheme'] ?? '' ) || ! ( in_array( $parts['host'] ?? '', array( 'www.losungen.de', 'bible2.net', 'ebible.org', 'downloads.sourceforge.net' ), true ) || preg_match( '/^[a-z0-9-]+\.dl\.sourceforge\.net$/D', $parts['host'] ?? '' ) ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['port'] ) || isset( $parts['fragment'] ) ) {
			throw new \RuntimeException( esc_html__( 'Diese Downloadadresse ist nicht freigegeben.', 'daily-scripture' ) );
		}
		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'             => 25,
				'redirection'         => 0,
				'limit_response_size' => $limit + 1,
				'sslverify'           => true,
				'headers'             => array( 'Accept' => '*/*' ),
			)
		);
		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( esc_html__( 'Anbieter nicht erreichbar oder sichere Verbindung fehlgeschlagen. Manueller Upload bleibt möglich.', 'daily-scripture' ) );
		}
		$status = wp_remote_retrieve_response_code( $response );
		$body   = wp_remote_retrieve_body( $response );
		// SourceForge distributes this exact archive through its HTTPS mirrors.
		if ( in_array( $status, array( 301, 302, 303, 307, 308 ), true ) && $redirects < 3 && ( 'downloads.sourceforge.net' === $parts['host'] || str_ends_with( $parts['host'], '.dl.sourceforge.net' ) ) ) {
			$location = wp_remote_retrieve_header( $response, 'location' );
			$next     = is_string( $location ) ? wp_parse_url( $location ) : false;
			if ( is_array( $next ) && 'https' === ( $next['scheme'] ?? '' ) && preg_match( '/^[a-z0-9-]+\.dl\.sourceforge\.net$/D', $next['host'] ?? '' ) && rawurldecode( $parts['path'] ?? '' ) === rawurldecode( $next['path'] ?? '' ) ) {
				return self::get( $location, $limit, $redirects + 1 );
			}
		}
		if ( 200 !== $status || '' === $body || strlen( $body ) > $limit ) {
			throw new \RuntimeException( esc_html__( 'Download nicht verfügbar, umgeleitet, leer oder zu groß. Bitte manuell hochladen oder später erneut prüfen.', 'daily-scripture' ) );
		}
		return $body;
	}
}
