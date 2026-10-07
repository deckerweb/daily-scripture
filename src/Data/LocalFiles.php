<?php
/**
 * Guarded local files for downloads and optional Bible editions.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Data;

defined( 'ABSPATH' ) || exit;
// Atomic local commits and locks require native streams.
// phpcs:disable WordPress.WP.AlternativeFunctions

/** Never resolves storage outside the plugin's fixed uploads subtree. */
final class LocalFiles {
	/** PHP protection prefix; these files are read as data, never included. */
	public const GUARD = "<?php exit; ?>\n";
	/**
	 * Prepare a protected directory.
	 *
	 * @param string $area Internal area.
	 * @return string
	 * @throws \RuntimeException For unsafe paths.
	 */
	public static function directory( string $area ): string {
		if ( ! in_array( $area, array( 'downloads', 'bibles' ), true ) ) {
			throw new \RuntimeException( esc_html__( 'Invalid storage area.', 'daily-scripture' ) );
		}
		$path = realpath( WP_CONTENT_DIR );
		if ( false === $path ) {
			throw new \RuntimeException( esc_html__( 'Data storage is unavailable.', 'daily-scripture' ) );
		}
		$parts = array( 'uploads', 'daily-scripture' );
		if ( is_multisite() ) {
			$parts = array_merge( $parts, array( 'sites', (string) get_current_blog_id() ) );
		}
		$parts[] = $area;
		foreach ( $parts as $part ) {
			$path .= '/' . $part;
			if ( is_link( $path ) || ( ! is_dir( $path ) && ! wp_mkdir_p( $path ) ) || realpath( $path ) !== $path ) {
				throw new \RuntimeException( esc_html__( 'Data storage is unsafe or not writable.', 'daily-scripture' ) );
			}
		}
		foreach ( array(
			'index.php'  => '<?php exit;',
			'.htaccess'  => "Require all denied\n",
			'web.config' => '<configuration><system.webServer><security><authorization><remove users="*" roles="" verbs=""/><add accessType="Deny" users="*"/></authorization></security></system.webServer></configuration>',
		) as $name => $text ) {
			$file = $path . '/' . $name;
			if ( is_link( $file ) || ( file_exists( $file ) && ! is_file( $file ) ) ) {
				throw new \RuntimeException( esc_html__( 'Unsafe protection file.', 'daily-scripture' ) );
			}
			if ( ! file_exists( $file ) && false === file_put_contents( $file, $text, LOCK_EX ) ) {
				throw new \RuntimeException( esc_html__( 'Data storage could not be protected.', 'daily-scripture' ) );
			}
		}
		return $path;
	}
	/**
	 * Hold a local lock and reject symlink targets.
	 *
	 * @param string   $id Whitelisted edition ID.
	 * @param callable $operation Operation receiving target filename.
	 * @return mixed
	 * @throws \RuntimeException On unsafe or busy storage.
	 */
	public static function locked( string $id, callable $operation ) {
		if ( ! preg_match( '/^[a-z]+-\d{4}$/D', $id ) ) {
			throw new \RuntimeException( esc_html__( 'Invalid Bible edition.', 'daily-scripture' ) );
		}
		$base = self::directory( 'bibles' ) . '/' . $id;
		foreach ( array( $base . '.lock', $base . '.json.php' ) as $file ) {
			if ( is_link( $file ) || ( file_exists( $file ) && ! is_file( $file ) ) ) {
				throw new \RuntimeException( esc_html__( 'Unsafe data file.', 'daily-scripture' ) );
			}
		}
		$lock = fopen( $base . '.lock', 'c' );
		if ( false === $lock ) {
			throw new \RuntimeException( esc_html__( 'Data storage could not be locked.', 'daily-scripture' ) );
		}
		try {
			if ( ! flock( $lock, LOCK_EX | LOCK_NB ) ) {
				throw new \RuntimeException( esc_html__( 'Another operation is running. Please try again.', 'daily-scripture' ) );
			}
			return $operation( $base . '.json.php' );
		} finally {
			flock( $lock, LOCK_UN );
			fclose( $lock );
		}
	}
	/**
	 * Atomically write guarded JSON without damaging an existing installation.
	 *
	 * @param string $target Checked target path under lock.
	 * @param array  $record Data.
	 * @return void
	 * @throws \RuntimeException On write failure.
	 */
	public static function commit( string $target, array $record ): void {
		$json = wp_json_encode( $record, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG );
		if ( false === $json || strlen( $json ) > 16000000 ) {
			throw new \RuntimeException( esc_html__( 'Bible data could not be encoded.', 'daily-scripture' ) );
		}
		$temp = dirname( $target ) . '/.import-' . wp_generate_uuid4() . '.php';
		try {
			$data = self::GUARD . $json;
			if ( strlen( $data ) !== file_put_contents( $temp, $data, LOCK_EX ) || ! rename( $temp, $target ) ) {
				throw new \RuntimeException( esc_html__( 'Saving failed. Existing data has been preserved.', 'daily-scripture' ) );
			}
		} finally {
			if ( is_file( $temp ) ) {
				wp_delete_file( $temp );
			}
		}
	}
}
