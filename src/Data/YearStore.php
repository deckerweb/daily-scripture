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
	 * @return string
	 * @throws \RuntimeException For unsafe or unwritable storage.
	 */
	private function directory( string $source ): string {
		YearValidator::identity( $source, 2000 );
		$content = realpath( WP_CONTENT_DIR );
		if ( false === $content ) {
			throw new \RuntimeException( esc_html__( 'Das WordPress-Inhaltsverzeichnis fehlt.', 'daily-scripture' ) );
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
			if ( is_link( $path ) || ( ! is_dir( $path ) && ! wp_mkdir_p( $path ) ) || realpath( $path ) !== $path ) {
				throw new \RuntimeException( esc_html__( 'Datenspeicher nicht verfügbar oder durch einen symbolischen Link umgeleitet.', 'daily-scripture' ) );
			}
		}
		$this->protect( $path );
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
				throw new \RuntimeException( esc_html__( 'Unsichere Schutzdatei im Datenspeicher.', 'daily-scripture' ) );
			}
			if ( is_file( $file ) ) {
				continue;
			}
			// Exclusive creation prevents overwriting pre-existing host rules.
			$handle = fopen( $file, 'x' );
			if ( false === $handle ) {
				throw new \RuntimeException( esc_html__( 'Datenspeicher konnte nicht geschützt werden. Bitte erneut versuchen.', 'daily-scripture' ) );
			}
			$written = fwrite( $handle, $content );
			fclose( $handle );
			if ( strlen( $content ) !== $written ) {
				wp_delete_file( $file );
				throw new \RuntimeException( esc_html__( 'Schutzdatei konnte nicht vollständig geschrieben werden.', 'daily-scripture' ) );
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
			throw new \RuntimeException( esc_html__( 'Ungültiges Speicherformat. Bitte die offizielle Jahresdatei erneut importieren.', 'daily-scripture' ) );
		}
		YearValidator::text( $record['edition'] ?? null, 1000 );
		YearValidator::text( $record['copyright'] ?? null );
		YearValidator::text( $record['imported_at'] ?? null, 100 );
		YearValidator::days( $record['days'], $source, $year );
	}

	/**
	 * Read data without executing the PHP guard.
	 *
	 * @param string $source Source identifier.
	 * @param int    $year Calendar year.
	 * @return array|null Null when absent; errors are explicit.
	 * @throws \RuntimeException For damaged or unsafe files.
	 */
	public function read( string $source, int $year ): ?array {
		YearValidator::identity( $source, $year );
		$path = $this->directory( $source ) . '/' . $year . '.json.php';
		if ( is_link( $path ) ) {
			throw new \RuntimeException( esc_html__( 'Symbolische Links sind im Datenspeicher nicht erlaubt.', 'daily-scripture' ) );
		}
		if ( ! file_exists( $path ) ) {
			return null;
		}
		if ( ! is_file( $path ) || ! is_readable( $path ) || filesize( $path ) > Importer::MAX_BYTES ) {
			throw new \RuntimeException( esc_html__( 'Jahresdatei nicht lesbar oder zu groß.', 'daily-scripture' ) );
		}
		$raw = file_get_contents( $path );
		if ( false === $raw || 0 !== strpos( $raw, self::GUARD ) ) {
			throw new \RuntimeException( esc_html__( 'Jahresdatei beschädigt: Dateischutz fehlt.', 'daily-scripture' ) );
		}
		$record = json_decode( substr( $raw, strlen( self::GUARD ) ), true );
		if ( ! is_array( $record ) ) {
			throw new \RuntimeException( esc_html__( 'Jahresdatei enthält ungültiges JSON.', 'daily-scripture' ) );
		}
		$this->validate( $record, $source, $year );
		return $record;
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
			throw new \RuntimeException( esc_html__( 'Unsicherer Speicherpfad.', 'daily-scripture' ) );
		}
		$handle = fopen( $path . '.lock', 'c' );
		if ( false === $handle ) {
			throw new \RuntimeException( esc_html__( 'Der Datenspeicher ist nicht beschreibbar.', 'daily-scripture' ) );
		}
		try {
			if ( ! flock( $handle, LOCK_EX | LOCK_NB ) ) {
				throw new \RuntimeException( esc_html__( 'Für dieses Jahr läuft bereits eine Änderung. Bitte erneut versuchen.', 'daily-scripture' ) );
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
			throw new \RuntimeException( esc_html__( 'Dieses Losungen-Jahr liegt außerhalb des zulässigen Zeitraums.', 'daily-scripture' ) );
		}
		$json = wp_json_encode( $record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG );
		if ( false === $json || strlen( $json ) + strlen( self::GUARD ) > Importer::MAX_BYTES ) {
			throw new \RuntimeException( esc_html__( 'Die normalisierten Jahresdaten sind zu groß.', 'daily-scripture' ) );
		}
		$this->locked(
			$source,
			$year,
			static function ( string $path ) use ( $json, $replace ): void {
				if ( file_exists( $path ) && ! $replace ) {
					throw new \RuntimeException( esc_html__( 'Dieses Jahr ist bereits installiert. Zum Ersetzen bitte die entsprechende Option wählen.', 'daily-scripture' ) );
				}
				$temp = dirname( $path ) . '/.import-' . wp_generate_uuid4() . '.php';
				$file = fopen( $temp, 'x' );
				if ( false === $file ) {
					throw new \RuntimeException( esc_html__( 'Temporäre Jahresdatei konnte nicht angelegt werden.', 'daily-scripture' ) );
				}
				try {
					$data    = self::GUARD . $json;
					$written = fwrite( $file, $data );
					$flushed = fflush( $file );
					fclose( $file );
					$file = null;
					if ( strlen( $data ) !== $written || ! $flushed || ! rename( $temp, $path ) ) {
						throw new \RuntimeException( esc_html__( 'Die Jahresdatei konnte nicht vollständig gespeichert werden. Vorhandene Daten bleiben erhalten.', 'daily-scripture' ) );
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
			static function ( string $path ): void {
				foreach ( array( $path, substr( $path, 0, -4 ) ) as $file ) {
					if ( is_link( $file ) ) {
						throw new \RuntimeException( esc_html__( 'Symbolische Links werden nicht gelöscht.', 'daily-scripture' ) );
					}
					if ( file_exists( $file ) ) {
						wp_delete_file( $file );
						if ( file_exists( $file ) ) {
							throw new \RuntimeException( esc_html__( 'Jahresdatei konnte nicht gelöscht werden.', 'daily-scripture' ) );
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
					'error'  => $record ? '' : __( 'Altdaten aus 0.1.0: bitte erneut importieren oder löschen.', 'daily-scripture' ),
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
