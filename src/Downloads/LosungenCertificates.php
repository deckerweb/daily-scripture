<?php
/**
 * Complete the publisher's missing TLS intermediates without disabling verification.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Downloads;

defined( 'ABSPATH' ) || exit;

/** Restrict a single verified retry to the reviewed Losungen download endpoints. */
final class LosungenCertificates {
	/** Fingerprint of the unchanged official YR1 and cross-signed Root YR certificates. */
	private const CHAIN_SHA256 = 'c19b138ec203a4c16701cb2da590eef2e15af9e664f7334308c6908f04b97fb1';

	/**
	 * Retry only a missing-issuer error, preserving the original WordPress trust roots.
	 *
	 * Official intermediates must themselves validate to the current WordPress CA
	 * bundle before being added. No new root is trusted and no certificate is fetched.
	 * The private temporary CA file is removed even if the HTTP transport throws.
	 *
	 * @param string    $url Reviewed HTTPS publisher endpoint.
	 * @param array     $args Original bounded HTTP arguments.
	 * @param \WP_Error $original Original transport error to retain if no retry is safe.
	 * @return array|\WP_Error WordPress HTTP response or transport error.
	 */
	public static function retry( string $url, array $args, \WP_Error $original ) {
		$parts   = wp_parse_url( $url );
		$message = $original->get_error_message();
		if ( ! is_array( $parts ) || 'https' !== ( $parts['scheme'] ?? '' ) || 'www.losungen.de' !== ( $parts['host'] ?? '' )
			|| isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['port'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] )
			|| ! preg_match( '~^/(?:download/|fileadmin/media-losungen/download/Losung_\d{4}_XML\.zip)$~D', $parts['path'] ?? '' )
			|| 'http_request_failed' !== $original->get_error_code() || ! str_contains( $message, 'cURL error 60:' )
			|| ! preg_match( '/unable to get (?:local )?issuer certificate|unable to verify the first certificate/i', $message )
			|| ! function_exists( 'openssl_x509_checkpurpose' ) ) {
			return $original;
		}
		$roots_path = ABSPATH . WPINC . '/certificates/ca-bundle.crt';
		$chain_path = dirname( __DIR__, 2 ) . '/resources/certificates/losungen-chain.pem';
		if ( ! is_readable( $roots_path ) || ! is_readable( $chain_path ) || ! hash_equals( self::CHAIN_SHA256, (string) hash_file( 'sha256', $chain_path ) ) ) {
			return $original;
		}
		$roots = file_get_contents( $roots_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Fixed local WordPress CA file.
		$chain = file_get_contents( $chain_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Fingerprinted local certificates.
		if ( ! is_string( $roots ) || ! is_string( $chain ) || ! preg_match( '~-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----~s', $chain, $cert ) ) {
			return $original;
		}
		$trusted = openssl_x509_checkpurpose( $cert[0], X509_PURPOSE_SSL_SERVER, array( $roots_path ), $chain_path );
		if ( true !== $trusted && 1 !== $trusted ) {
			return $original;
		}
		$temporary = wp_tempnam( 'daily-scripture-ca-bundle' );
		if ( ! $temporary ) {
			return $original;
		}
		try {
			$bundle = $roots . "\n" . $chain;
			// The file contains only public certificates, but remains private to this request.
			if ( ! chmod( $temporary, 0600 ) || strlen( $bundle ) !== file_put_contents( $temporary, $bundle, LOCK_EX ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
				return $original;
			}
			$args['sslverify']       = true;
			$args['sslcertificates'] = $temporary;
			$args['redirection']     = 0;
			return wp_safe_remote_get( $url, $args );
		} finally {
			wp_delete_file( $temporary );
		}
	}
}
