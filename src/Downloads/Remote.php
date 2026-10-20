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
	 * @throws \RuntimeException On an unapproved download address.
	 * @throws DownloadError On a categorized transport or response failure.
	 */
	public static function get( string $url, int $limit = 2097152, int $redirects = 0 ): string {
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || 'https' !== ( $parts['scheme'] ?? '' ) || ! ( in_array( $parts['host'] ?? '', array( 'www.losungen.de', 'bible2.net', 'ebible.org', 'downloads.sourceforge.net' ), true ) || preg_match( '/^[a-z0-9-]+\.dl\.sourceforge\.net$/D', $parts['host'] ?? '' ) ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['port'] ) || isset( $parts['fragment'] ) ) {
			throw new \RuntimeException( esc_html__( 'This download address is not approved.', 'daily-scripture' ) );
		}
		$args     = array(
			'timeout'             => 25,
			'redirection'         => 0,
			'limit_response_size' => $limit + 1,
			'sslverify'           => true,
			'headers'             => array( 'Accept' => '*/*' ),
		);
		$response = wp_safe_remote_get( $url, $args );
		if ( is_wp_error( $response ) && 'www.losungen.de' === $parts['host'] ) {
			$response = LosungenCertificates::retry( $url, $args, $response );
		}
		if ( is_wp_error( $response ) ) {
			throw DownloadError::transport( $response ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception carries only safe localized categories; output is escaped by the admin renderer.
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
		if ( 200 !== $status ) {
			throw new DownloadError( 'http', (int) $status );
		}
		if ( '' === $body ) {
			throw new DownloadError( 'empty' );
		}
		if ( strlen( $body ) > $limit ) {
			throw new DownloadError( 'large' );
		}
		return $body;
	}
}
