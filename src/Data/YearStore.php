<?php
/**
 * Local, guarded and atomic annual data storage.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Data;

defined( 'ABSPATH' ) || exit;

// Same-directory atomic rename and persistent flock require native local streams.
// WP_Filesystem transports cannot guarantee these locking/commit semantics.
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.WP.AlternativeFunctions.file_system_operations_fclose, WordPress.WP.AlternativeFunctions.file_system_operations_fwrite, WordPress.WP.AlternativeFunctions.rename_rename, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

/** Owns all plugin-managed verse files beneath wp-content/uploads/daily-scripture. */
final class YearStore {
	/** Prevent direct web requests from returning annual text collections. */
	private const GUARD = "<?php exit; ?>\n";

	/**
	 * Resolve and prepare a source directory, rejecting symlink escapes.
	 *
	 * Custom upload roots are intentionally not used: this plugin's data contract
	 * requires WP_CONTENT_DIR/uploads/daily-scripture. Multisite gets isolated sites.
	 *
	 * @param string $source Source identifier.
	 * @param bool   $prepare Whether to create directories and protection files.
	 * @return string Empty when absent in read-only mode.
	 * @throws \RuntimeException For unsafe or unwritable storage.
	 */
	private function directory( string $source, bool $prepare = true ): string {
		YearValidator::identity( $source, 2000 );
		$content = realpath( WP_CONTENT_DIR );
		if ( false === $content ) {
			throw new \RuntimeException( esc_html__( 'The WordPress content directory is missing.', 'daily-scripture' ) );
		}
		$parts = array( 'uploads', 'daily-scripture' );
		if ( is_multisite() ) {
			$parts[] = 'sites';
			$parts[] = (string) get_current_blog_id();
		}
		$parts[] = 'data';
		$parts[] = $source;
		$path    = $content;
		foreach ( $parts as $part ) {
			$path .= '/' . $part;
			if ( ! $prepare && ! file_exists( $path ) && ! is_link( $path ) ) {
				return '';
			}
			if ( is_link( $path ) || ( ! is_dir( $path ) && ( ! $prepare || ! wp_mkdir_p( $path ) ) ) || realpath( $path ) !== $path ) {
				throw new \RuntimeException( esc_html__( 'Data storage is unavailable or redirected by a symbolic link.', 'daily-scripture' ) );
			}
		}
		if ( $prepare ) {
			$this->protect( $path );
		}
		return $path;
	}

	/**
	 * Add defense in depth for Apache/IIS and prevent directory listings.
	 *
	 * PHP guards remain the portable fallback; nginx requires a host-level deny
	 * rule when PHP handling is disabled for uploads. Existing rules are preserved.
	 *
	 * @param string $path Validated local source directory.
	 * @return void
	 * @throws \RuntimeException For unsafe or unwritable protection files.
	 */
	private function protect( string $path ): void {
		$files = array(
			'.htaccess'  => "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n",
			'web.config' => '<?xml version="1.0"?><configuration><system.webServer><security><authorization><remove users="*" roles="" verbs=""/><add accessType="Deny" users="*"/></authorization></security></system.webServer></configuration>',
			'index.php'  => '<?php exit;',
		);
		foreach ( $files as $name => $content ) {
			$file = $path . '/' . $name;
			if ( is_link( $file ) || ( file_exists( $file ) && ! is_file( $file ) ) ) {
				throw new \RuntimeException( esc_html__( 'Unsafe protection file in data storage.', 'daily-scripture' ) );
			}
			if ( is_file( $file ) ) {
				continue;
			}
			// Exclusive creation prevents overwriting pre-existing host rules.
			$handle = fopen( $file, 'x' );
			if ( false === $handle ) {
				throw new \RuntimeException( esc_html__( 'Data storage could not be protected. Please try again.', 'daily-scripture' ) );
			}
			$written = fwrite( $handle, $content );
			fclose( $handle );
			if ( strlen( $content ) !== $written ) {
				wp_delete_file( $file );
				throw new \RuntimeException( esc_html__( 'The protection file could not be written completely.', 'daily-scripture' ) );
			}
		}
	}

	/**
	 * Validate a stored envelope and all source text before it reaches rendering.
	 *
	 * @param array  $record Stored data.
	 * @param string $source Source identifier.
	 * @param int    $year Calendar year.
	 * @return void
	 * @throws \RuntimeException For invalid data.
	 */
	private function validate( array $record, string $source, int $year ): void {
		if ( 1 !== ( $record['schema'] ?? null ) || ( $record['source'] ?? null ) !== $source || ( $record['year'] ?? null ) !== $year || ! is_array( $record['days'] ?? null ) || ! is_string( $record['sha256'] ?? null ) || ! preg_match( '/^[a-f0-9]{64}$/D', $record['sha256'] ) ) {
			throw new \RuntimeException( esc_html__( 'Invalid storage format. Please import the official annual file again.', 'daily-scripture' ) );
		}
		YearValidator::text( $record['edition'] ?? null, 1000 );
		YearValidator::text( $record['copyright'] ?? null );
		YearValidator::text( $record['imported_at'] ?? null, 100 );
		YearValidator::days( $record['days'], $source, $year );
	}

	/**
	 * Read data without executing the PHP guard.
	 * Pass false for diagnostics that must not create storage or protection files.
	 *
	 * @param string $source Source identifier.
	 * @param int    $year Calendar year.
	 * @param bool   $prepare Whether to prepare storage (false for diagnostics).
	 * @return array|null Null when absent; errors are explicit.
	 * @throws \RuntimeException For damaged or unsafe files.
	 */
	public function read( string $source, int $year, bool $prepare = true ): ?array {
		YearValidator::identity( $source, $year );
		$directory = $this->directory( $source, $prepare );
		if ( '' === $directory ) {
			return null;
		}
		$path = $directory . '/' . $year . '.json.php';
		if ( is_link( $path ) ) {
			throw new \RuntimeException( esc_html__( 'Symbolic links are not allowed in data storage.', 'daily-scripture' ) );
		}
		if ( ! file_exists( $path ) ) {
			return null;
		}
		if ( ! is_file( $path ) || ! is_readable( $path ) || filesize( $path ) > Importer::MAX_BYTES ) {
			throw new \RuntimeException( esc_html__( 'Annual file is unreadable or too large.', 'daily-scripture' ) );
		}
		$raw = file_get_contents( $path );
		if ( false === $raw || 0 !== strpos( $raw, self::GUARD ) ) {
			throw new \RuntimeException( esc_html__( 'Annual file is damaged: file protection is missing.', 'daily-scripture' ) );
		}
		$record = json_decode( substr( $raw, strlen( self::GUARD ) ), true );
		if ( ! is_array( $record ) ) {
			throw new \RuntimeException( esc_html__( 'Annual file contains invalid JSON.', 'daily-scripture' ) );
		}
		$this->validate( $record, $source, $year );
		return $record;
	}

	/**
	 * List annual filenames without loading texts or creating storage.
	 *
	 * @param string $source Source identifier.
	 * @return int[] Years present; their contents are not validated here.
	 * @throws \RuntimeException For unsafe or unreadable storage.
	 */
	public function existing_years( string $source ): array {
		$directory = $this->directory( $source, false );
		if ( '' === $directory ) {
			return array();
		}
		if ( ! is_readable( $directory ) ) {
			throw new \RuntimeException( 'Annual storage is unreadable.' );
		}
		$years = array();
		foreach ( new \DirectoryIterator( $directory ) as $file ) {
			if ( preg_match( '/^((?:19|20|21)[0-9]{2})\.json\.php$/D', $file->getFilename(), $match ) ) {
				$years[] = (int) $match[1];
			}
		}
		sort( $years, SORT_NUMERIC );
		return $years;
	}

	/**
	 * Serialize writes and deletes for a source/year.
	 *
	 * @param string   $source Source identifier.
	 * @param int      $year Calendar year.
	 * @param callable $operation Mutation receiving the target path.
	 * @return void
	 * @throws \RuntimeException When locking fails.
	 */
	private function locked( string $source, int $year, callable $operation ): void {
		YearValidator::identity( $source, $year );
		$path = $this->directory( $source ) . '/' . $year;
		if ( is_link( $path . '.lock' ) || is_link( $path . '.json.php' ) ) {
			throw new \RuntimeException( esc_html__( 'Unsafe storage path.', 'daily-scripture' ) );
		}
		$handle = fopen( $path . '.lock', 'c' );
		if ( false === $handle ) {
			throw new \RuntimeException( esc_html__( 'Data storage is not writable.', 'daily-scripture' ) );
		}
		try {
			if ( ! flock( $handle, LOCK_EX | LOCK_NB ) ) {
				throw new \RuntimeException( esc_html__( 'This year is already being changed. Please try again.', 'daily-scripture' ) );
			}
			$operation( $path . '.json.php' );
		} finally {
			flock( $handle, LOCK_UN );
			fclose( $handle );
		}
		// Keep the lock inode: removing it would allow overlapping locks.
	}

	/**
	 * Commit a verified record via same-directory atomic rename.
	 *
	 * @param array $record Validated import.
	 * @param bool  $replace Explicit permission to replace an existing year.
	 * @return void
	 * @throws \RuntimeException On validation, collision or write failure.
	 */
	public function save( array $record, bool $replace = false ): void {
		$source = $record['source'] ?? '';
		$year   = $record['year'] ?? 0;
		$this->validate( $record, $source, $year );
		if ( ! YearValidator::allowed( $source, $year ) ) {
			throw new \RuntimeException( esc_html__( 'This Losungen year is outside the permitted range.', 'daily-scripture' ) );
		}
		$json = wp_json_encode( $record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG );
		if ( false === $json || strlen( $json ) + strlen( self::GUARD ) > Importer::MAX_BYTES ) {
			throw new \RuntimeException( esc_html__( 'The normalized annual data is too large.', 'daily-scripture' ) );
		}
		$this->locked(
			$source,
			$year,
			/**
			 * Atomically replace one validated annual record under its source lock.
			 *
			 * @param string $path Guarded annual destination path.
			 * @return void
			 */
			static function ( string $path ) use ( $json, $replace ): void {
				if ( file_exists( $path ) && ! $replace ) {
					throw new \RuntimeException( esc_html__( 'This year is already installed. Please select the replacement option to replace it.', 'daily-scripture' ) );
				}
				$temp = dirname( $path ) . '/.import-' . wp_generate_uuid4() . '.php';
				$file = fopen( $temp, 'x' );
				if ( false === $file ) {
					throw new \RuntimeException( esc_html__( 'A temporary annual file could not be created.', 'daily-scripture' ) );
				}
				try {
					$data    = self::GUARD . $json;
					$written = fwrite( $file, $data );
					$flushed = fflush( $file );
					fclose( $file );
					$file = null;
					if ( strlen( $data ) !== $written || ! $flushed || ! rename( $temp, $path ) ) {
						throw new \RuntimeException( esc_html__( 'The annual file could not be saved completely. Existing data has been preserved.', 'daily-scripture' ) );
					}
				} finally {
					if ( is_resource( $file ) ) {
						fclose( $file );
					}
					if ( is_file( $temp ) ) {
						wp_delete_file( $temp );
					}
				}
			}
		);
	}

	/**
	 * Remove only the chosen year's data, including untrusted legacy JSON.
	 *
	 * @param string $source Source identifier.
	 * @param int    $year Calendar year.
	 * @return void
	 * @throws \RuntimeException When deletion fails.
	 */
	public function delete( string $source, int $year ): void {
		$this->locked(
			$source,
			$year,
			/**
			 * Delete only the selected annual record under its source lock.
			 *
			 * @param string $path Guarded annual destination path.
			 * @return void
			 */
			static function ( string $path ): void {
				foreach ( array( $path, substr( $path, 0, -4 ) ) as $file ) {
					if ( is_link( $file ) ) {
						throw new \RuntimeException( esc_html__( 'Symbolic links are not deleted.', 'daily-scripture' ) );
					}
					if ( file_exists( $file ) ) {
						wp_delete_file( $file );
						if ( file_exists( $file ) ) {
							throw new \RuntimeException( esc_html__( 'Annual file could not be deleted.', 'daily-scripture' ) );
						}
					}
				}
			}
		);
	}

	/**
	 * List files with validation status, including broken and prototype data.
	 *
	 * @param string $source Source identifier.
	 * @return array Year-keyed rows.
	 */
	public function inventory( string $source ): array {
		$rows  = array();
		$files = glob( $this->directory( $source ) . '/*' );
		foreach ( is_array( $files ) ? $files : array() as $path ) {
			if ( ! preg_match( '/^(\d{4})\.json(?:\.php)?$/D', basename( $path ), $match ) ) {
				continue;
			}
			$year = (int) $match[1];
			try {
				$record        = $this->read( $source, $year );
				$rows[ $year ] = array(
					'record' => $record,
					'error'  => $record ? '' : __( 'Legacy data from 0.1.0: please import again or delete.', 'daily-scripture' ),
				);
			} catch ( \RuntimeException $error ) {
				$rows[ $year ] = array(
					'record' => null,
					'error'  => $error->getMessage(),
				);
			}
		}
		ksort( $rows );
		return $rows;
	}
}
