<?php
/**
 * Install reviewed full editions and resolve local chapter/verse ranges.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Bible;

use Deckerweb\DailyScripture\Data\{LocalFiles, YearValidator};
use Deckerweb\DailyScripture\Downloads\Remote;
defined( 'ABSPATH' ) || exit;
// Local bounded files and atomic writes; no remote filesystem transports.
// phpcs:disable WordPress.WP.AlternativeFunctions
// DOM and ZipArchive properties are prescribed by PHP.
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

/** The annual providers never replace their official text with this library. */
final class Library {
	/** Input and decompressed payload limit. */
	public const MAX_BYTES = 10000000;
	/**
	 * Install from a reviewed remote package or user-uploaded equivalent.
	 *
	 * @param string $id Edition.
	 * @param bool   $replace Explicit replacement permission.
	 * @param string $upload PHP-verified upload path, empty for remote.
	 * @return void
	 * @throws \RuntimeException On invalid or unavailable input.
	 */
	public function install( string $id, bool $replace = false, string $upload = '' ): void {
		$edition = TranslationManager::edition( $id );
		if ( '' !== $upload && ( ! is_file( $upload ) || filesize( $upload ) > self::MAX_BYTES ) ) {
			throw new \RuntimeException( esc_html__( 'The Bible download is too large or unreadable.', 'daily-scripture' ) );
		}
		$body = '' === $upload ? Remote::get( $edition['url'], self::MAX_BYTES ) : file_get_contents( $upload );
		if ( ! is_string( $body ) || '' === $body ) {
			throw new \RuntimeException( esc_html__( 'The Bible file is empty or unreadable.', 'daily-scripture' ) );
		}
		$record = $this->parse( $id, $body );
		LocalFiles::locked(
			$id,
			/**
			 * Commit a validated edition while holding its exclusive installation lock.
			 *
			 * @param string $file Guarded edition path.
			 * @return void
			 */
			static function ( $file ) use ( $record, $replace ) {
				if ( file_exists( $file ) && ! $replace ) {
					throw new \RuntimeException( esc_html__( 'This edition is already installed. Please explicitly select replacement.', 'daily-scripture' ) );
				}
				LocalFiles::commit( $file, $record );
			}
		);
	}
	/**
	 * Parse exactly the reviewed source payload. Unknown revisions fail closed.
	 *
	 * @param string $id Edition.
	 * @param string $body ZIP or original TXT/XML.
	 * @return array Guarded-storage record.
	 * @throws \RuntimeException On a fingerprint or format mismatch.
	 */
	public function parse( string $id, string $body ): array {
		$edition = TranslationManager::edition( $id );
		if ( strlen( $body ) > self::MAX_BYTES ) {
			throw new \RuntimeException( esc_html__( 'The Bible file is too large.', 'daily-scripture' ) );
		}
		if ( str_starts_with( $body, 'PK' ) ) {
			$body = $this->unzip( $body, $edition['entry'] );
		}
		if ( ! hash_equals( $edition['sha256'], hash( 'sha256', $body ) ) ) {
			throw new \RuntimeException( esc_html__( 'This text does not match the verified source revision. Nothing was installed. Please use the linked original package; newer revisions require a plugin update.', 'daily-scripture' ) );
		}
		$books      = array();
		$parts_seen = array();
		if ( 'vpl' === $edition['format'] ) {
			foreach ( preg_split( '/\r?\n/', $body ) as $line ) {
				if ( '' === $line ) {
					continue; }
				if ( ! preg_match( '/^([A-Z0-9]{3}) ([1-9]\d{0,2}):([1-9]\d{0,2}) (.*)$/uD', $line, $match ) ) {
					throw new \RuntimeException( esc_html__( 'Invalid verse line in the Bible package.', 'daily-scripture' ) );
				}
				if ( 'schlachter-1951' === $id && 'MAT' === $match[1] && '21' === $match[2] && '44' === $match[3] && '' === $match[4] ) {
					// The fingerprinted eBible source has an empty versification marker here.
					$books['MAT'][21][44] = '';
				} else {
					$this->add( $books, $match[1], (int) $match[2], (int) $match[3], $match[4] );
				}
			}
		} else {
			if ( ! class_exists( '\DOMDocument' ) || preg_match( '/<!\s*(DOCTYPE|ENTITY)/i', $body ) ) {
				throw new \RuntimeException( esc_html__( 'Secure DOM/XML support is required.', 'daily-scripture' ) );
			}
			$previous = libxml_use_internal_errors( true );
			try {
				$doc                     = new \DOMDocument();
				$doc->resolveExternals   = false;
				$doc->substituteEntities = false;
				if ( ! $doc->loadXML( $body, LIBXML_NONET ) || $doc->doctype ) {
					throw new \RuntimeException( esc_html__( 'Invalid Bible XML.', 'daily-scripture' ) );
				}
				$codes = array_keys( TranslationManager::books() );
				foreach ( $doc->getElementsByTagName( 'BIBLEBOOK' ) as $book ) {
					$number = (int) $book->getAttribute( 'bnumber' );
					if ( $number < 1 || $number > 66 ) {
						continue; }
					foreach ( $book->getElementsByTagName( 'CHAPTER' ) as $chapter ) {
						foreach ( $chapter->getElementsByTagName( 'VERS' ) as $verse ) {
							// Preserve explicit line breaks while excluding non-verse captions.
							foreach ( iterator_to_array( $verse->getElementsByTagName( 'BR' ) ) as $br ) {
								$br->parentNode->replaceChild( $doc->createTextNode( "\n" ), $br );
							}
							$code           = $codes[ $number - 1 ];
							$chapter_number = (int) $chapter->getAttribute( 'cnumber' );
							$verse_number   = (int) $verse->getAttribute( 'vnumber' );
							$part           = $verse->getAttribute( 'aix' );
							$coordinate     = $code . '.' . $chapter_number . '.' . $verse_number;
							if ( isset( $books[ $code ][ $chapter_number ][ $verse_number ] ) ) {
								// Reviewed Zefania splits some verses into a/b parts, sometimes in b/a order.
								// Keep every part in its original source order, without replacing earlier text.
								if ( ! preg_match( '/^[a-z]$/D', $part ) || isset( $parts_seen[ $coordinate ][ $part ] ) || ( isset( $parts_seen[ $coordinate ][''] ) && 'ACT.8.1' !== $coordinate ) ) {
									throw new \RuntimeException( esc_html__( 'Duplicate or invalid verse part.', 'daily-scripture' ) );
								}
								$books[ $code ][ $chapter_number ][ $verse_number ] .= "\n" . YearValidator::text( $verse->textContent );
							} elseif ( 'EZE.33.15' === $coordinate && '' === $verse->textContent ) {
									// This fingerprinted source combines 14–15 in verse 14; retain its empty marker.
									$books[ $code ][ $chapter_number ][ $verse_number ] = '';
							} else {
								$this->add( $books, $code, $chapter_number, $verse_number, $verse->textContent );
							}
							$parts_seen[ $coordinate ][ $part ] = true;
						}
					}
				}
			} finally {
				libxml_clear_errors();
				libxml_use_internal_errors( $previous );
			}
		}
		$count    = 0;
		$chapters = 0;
		foreach ( $books as $book ) {
			$chapters += count( $book );
			foreach ( $book as $verses ) {
				$count += count( $verses ); }
		}
		if ( array_keys( $books ) !== array_keys( TranslationManager::books() ) || 1189 !== $chapters || $edition['verses'] !== $count ) {
			throw new \RuntimeException( esc_html__( 'The Bible edition is structurally incomplete.', 'daily-scripture' ) );
		}
		return array(
			'schema'    => 1,
			'id'        => $id,
			'sha256'    => $edition['sha256'],
			'installed' => gmdate( 'c' ),
			'verses'    => $count,
			'chapters'  => $chapters,
			'books'     => $books,
		);
	}
	/**
	 * Append a unique verse without changing its text.
	 *
	 * @param array  $books Accumulated verses.
	 * @param string $book Canonical code.
	 * @param int    $chapter Chapter.
	 * @param int    $verse Verse.
	 * @param string $text Original text.
	 * @return void
	 * @throws \RuntimeException For duplicate or invalid coordinates.
	 */
	private function add( array &$books, string $book, int $chapter, int $verse, string $text ): void {
		if ( ! isset( TranslationManager::books()[ $book ] ) || $chapter < 1 || $chapter > 150 || $verse < 1 || $verse > 176 || isset( $books[ $book ][ $chapter ][ $verse ] ) ) {
			throw new \RuntimeException( esc_html__( 'Invalid or duplicate Bible reference.', 'daily-scripture' ) );
		}
		$books[ $book ][ $chapter ][ $verse ] = YearValidator::text( $text );
	}
	/**
	 * Read just the reviewed entry without extracting archive paths.
	 *
	 * @param string $body Archive bytes.
	 * @param string $entry Exact reviewed member name.
	 * @return string
	 * @throws \RuntimeException On archive limits or errors.
	 */
	private function unzip( string $body, string $entry ): string {
		if ( ! class_exists( '\ZipArchive' ) ) {
			throw new \RuntimeException( esc_html__( 'ZIP support is missing. Please upload the original TXT/XML file.', 'daily-scripture' ) );
		}
		$file   = LocalFiles::directory( 'downloads' ) . '/.bible-' . wp_generate_uuid4() . '.bin';
		$zip    = new \ZipArchive();
		$opened = false;
		try {
			if ( strlen( $body ) !== file_put_contents( $file, $body, LOCK_EX ) ) {
				throw new \RuntimeException( esc_html__( 'Temporary storage failed.', 'daily-scripture' ) );
			}
			chmod( $file, 0600 );
			$opened = true === $zip->open( $file );
			if ( ! $opened || $zip->numFiles > 12 ) {
				throw new \RuntimeException( esc_html__( 'Invalid Bible archive.', 'daily-scripture' ) );
			}
			$found = 0;
			for ( $i = 0; $i < $zip->numFiles; ++$i ) {
				$stat = $zip->statIndex( $i );
				if ( ! $stat || preg_match( '~(^/|\\\\|(^|/)\.\.(/|$)|^[A-Za-z]:)~', $stat['name'] ) ) {
					throw new \RuntimeException( esc_html__( 'Unsafe archive paths.', 'daily-scripture' ) );
				}
				if ( $stat['name'] === $entry ) {
					++$found;
					if ( $stat['size'] > self::MAX_BYTES || ! empty( $stat['encryption_method'] ) ) {
						throw new \RuntimeException( esc_html__( 'Archive content is too large or encrypted.', 'daily-scripture' ) );
					}
				}
			}
			$payload = 1 === $found ? $zip->getFromName( $entry, self::MAX_BYTES + 1 ) : false;
			if ( ! is_string( $payload ) || strlen( $payload ) > self::MAX_BYTES ) {
				throw new \RuntimeException( esc_html__( 'The verified text file is missing from the archive.', 'daily-scripture' ) );
			}
			return $payload;
		} finally {
			if ( $opened ) {
				$zip->close(); }
			if ( is_file( $file ) ) {
				wp_delete_file( $file ); }
		}
	}
	/**
	 * Read one installed edition; files are never evaluated as PHP.
	 *
	 * @param string $id Edition.
	 * @return array|null
	 * @throws \RuntimeException On corrupt storage.
	 */
	public function read( string $id ): ?array {
		$edition = TranslationManager::edition( $id );
		$file    = LocalFiles::directory( 'bibles' ) . '/' . $id . '.json.php';
		if ( is_link( $file ) ) {
			throw new \RuntimeException( esc_html__( 'Unsafe Bible file.', 'daily-scripture' ) ); }
		if ( ! file_exists( $file ) ) {
			return null; }
		if ( ! is_file( $file ) || filesize( $file ) > 16000000 ) {
			throw new \RuntimeException( esc_html__( 'Invalid Bible storage.', 'daily-scripture' ) ); }
		$data   = file_get_contents( $file );
		$record = is_string( $data ) && str_starts_with( $data, LocalFiles::GUARD ) ? json_decode( substr( $data, strlen( LocalFiles::GUARD ) ), true ) : null;
		if ( ! is_array( $record ) || 1 !== ( $record['schema'] ?? null ) || ( $record['id'] ?? '' ) !== $id || ( $record['sha256'] ?? '' ) !== $edition['sha256'] || ! is_array( $record['books'] ?? null ) || 66 !== count( $record['books'] ) ) {
			throw new \RuntimeException( esc_html__( 'Bible data is damaged or outdated. Please reinstall the edition.', 'daily-scripture' ) );
		}
		return $record;
	}
	/**
	 * Resolve a bounded range within one chapter; never silently skip a verse.
	 *
	 * @param string $id Edition.
	 * @param string $book Book code.
	 * @param int    $chapter Chapter.
	 * @param int    $from First verse.
	 * @param int    $to Last verse.
	 * @return array Verse-number keyed texts; known combined verses have a range key.
	 * @throws \RuntimeException For absent data or an unsupported range.
	 */
	public function passage( string $id, string $book, int $chapter, int $from, int $to ): array {
		if ( ! isset( TranslationManager::books()[ $book ] ) || $chapter < 1 || $chapter > 150 || $from < 1 || $to < $from || $to > 176 || $to - $from >= 50 ) {
			throw new \RuntimeException( esc_html__( 'Please choose a valid passage of up to 50 verses within one chapter.', 'daily-scripture' ) );
		}
		$record = $this->read( $id );
		if ( null === $record ) {
			throw new \RuntimeException( esc_html__( 'This Bible edition is not installed locally yet.', 'daily-scripture' ) );
		}
		if ( 'schlachter-1951' === $id && 'MAT' === $book && 21 === $chapter && $from <= 44 && $to >= 44 ) {
			throw new \RuntimeException( esc_html__( 'The Schlachter 1951 source file has no verse text for Matthew 21:44. Following verses use different numbering in the original text. Please choose a range without this empty verse or compare the source.', 'daily-scripture' ) );
		}
		$combined = 'menge-1939' === $id && 'EZE' === $book && 33 === $chapter;
		if ( $combined && ( ( $from <= 14 && 14 === $to ) || 15 === $from ) ) {
			throw new \RuntimeException( esc_html__( 'This Menge edition combines Ezekiel 33:14–15. Please select both verses together.', 'daily-scripture' ) );
		}
		$items = array();
		for ( $verse = $from; $verse <= $to; ++$verse ) {
			if ( $combined && 15 === $verse && $from <= 14 ) {
				continue; }
			$text = $record['books'][ $book ][ $chapter ][ $verse ] ?? null;
			if ( ! is_string( $text ) || '' === trim( $text ) ) {
				throw new \RuntimeException( esc_html__( 'The selected passage is incomplete in this edition. Please check the verse numbering.', 'daily-scripture' ) );
			}
			$items[ $combined && 14 === $verse ? '14–15' : $verse ] = YearValidator::text( $text );
		}
		return $items;
	}
	/**
	 * Delete just one reviewed edition under the same installation lock.
	 *
	 * @param string $id Edition.
	 * @return void
	 */
	public function delete( string $id ): void {
		TranslationManager::edition( $id );
		LocalFiles::locked(
			$id,
			/**
			 * Delete the selected edition while holding its exclusive lock.
			 *
			 * @param string $file Guarded edition path.
			 * @return void
			 */
			static function ( $file ) {
				if ( is_file( $file ) ) {
					wp_delete_file( $file ); }
				if ( file_exists( $file ) ) {
					throw new \RuntimeException( esc_html__( 'The Bible edition could not be deleted.', 'daily-scripture' ) ); }
			}
		);
	}
}
