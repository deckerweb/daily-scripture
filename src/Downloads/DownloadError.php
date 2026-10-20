<?php
/**
 * Safe download failure categories without provider bodies or transport secrets.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Downloads;

defined( 'ABSPATH' ) || exit;

/** Carry only bounded reason/status numbers across the existing admin notice flow. */
final class DownloadError extends \RuntimeException {
	/**
	 * Safe reason and optional HTTP/cURL numbers.
	 *
	 * @var array
	 */
	private array $diagnostic;

	/**
	 * Create a localized failure from an approved category.
	 *
	 * @param string $reason Approved failure category.
	 * @param int    $http HTTP response status, if available.
	 * @param int    $curl cURL error number, if available.
	 * @return void
	 */
	public function __construct( string $reason, int $http = 0, int $curl = 0 ) {
		$reason           = in_array( $reason, array( 'timeout', 'tls', 'http', 'empty', 'large', 'content', 'connection' ), true ) ? $reason : 'connection';
		$this->diagnostic = array(
			'reason' => $reason,
			'http'   => $http >= 100 && $http <= 599 ? $http : 0,
			'curl'   => $curl > 0 && $curl <= 999 ? $curl : 0,
		);
		$messages         = array(
			'timeout'    => __( 'The provider did not respond in time. Try again later or upload the file manually.', 'daily-scripture' ),
			'tls'        => __( 'The secure connection could not be verified. Certificate checks remain enabled; manual upload is still available.', 'daily-scripture' ),
			'http'       => __( 'The provider returned an unexpected HTTP response. Try again later or upload the file manually.', 'daily-scripture' ),
			'empty'      => __( 'The provider returned an empty file. Please try again later or upload the original file manually.', 'daily-scripture' ),
			'large'      => __( 'The download exceeds the permitted size. Please check the original package before uploading it manually.', 'daily-scripture' ),
			'content'    => __( 'The downloaded content is not a supported provider directory or annual file. Existing data has not been replaced.', 'daily-scripture' ),
			'connection' => __( 'The provider could not be reached. Check the server connection or upload the file manually.', 'daily-scripture' ),
		);
		parent::__construct( $messages[ $reason ] );
	}

	/**
	 * Classify transport errors without retaining their possibly sensitive text.
	 *
	 * @param \WP_Error $error WordPress transport failure.
	 * @return self Safe categorized error.
	 */
	public static function transport( \WP_Error $error ): self {
		$message = $error->get_error_message();
		$curl    = preg_match( '/cURL error (\d{1,3}):/i', $message, $match ) ? (int) $match[1] : 0;
		if ( 28 === $curl || preg_match( '/timed? out|timeout/i', $message ) ) {
			return new self( 'timeout', 0, $curl );
		}
		if ( in_array( $curl, array( 35, 51, 58, 59, 60, 64, 66, 77, 80, 82, 83, 90, 91 ), true ) || preg_match( '/certificate|\bSSL\b|\bTLS\b/i', $message ) ) {
			return new self( 'tls', 0, $curl );
		}
		return new self( 'connection', 0, $curl );
	}

	/**
	 * Recreate a localized message from whitelisted cached diagnostic values.
	 *
	 * @param array $data Cached diagnostic; all other keys are discarded.
	 * @return self Safe localized error.
	 */
	public static function restore( array $data ): self {
		return new self( is_string( $data['reason'] ?? null ) ? $data['reason'] : 'connection', is_int( $data['http'] ?? null ) ? $data['http'] : 0, is_int( $data['curl'] ?? null ) ? $data['curl'] : 0 );
	}

	/**
	 * Return only bounded codes, never raw messages, addresses or file contents.
	 *
	 * @return array Safe diagnostic for existing short-lived caches/notices.
	 */
	public function diagnostic(): array {
		return $this->diagnostic;
	}
}
