<?php
/**
 * Original-file adapters for the two publishers.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Data;

defined( 'ABSPATH' ) || exit;

// Native DOM/ZipArchive property names are prescribed by PHP.
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
// Local uploads only; using HTTP here would violate the offline import contract.
// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

/** Parses bounded files in memory; never extracts ZIP paths or fetches XML entities. */
final class Importer {
	/** Maximum input and decompressed XML size (8 MiB). */
	public const MAX_BYTES = 8388608;

	/**
	 * Read an uploaded file or a local test fixture.
	 *
	 * @param string $path File path supplied by trusted calling code.
	 * @param string $source Selected source.
	 * @param int    $year Selected year.
	 * @return array Validated storage record.
	 * @throws \RuntimeException For unsupported, incomplete or unsafe files.
	 */
	public function parse( string $path, string $source, int $year ): array {
		YearValidator::identity( $source, $year );
		if ( ! YearValidator::allowed( $source, $year ) ) {
			throw new \RuntimeException( esc_html__( 'Die Losungen dürfen nur für das Vorjahr, das laufende Jahr und das Folgejahr importiert werden.', 'daily-scripture' ) );
		}
		if ( ! is_file( $path ) || ! is_readable( $path ) || filesize( $path ) > self::MAX_BYTES || 0 === filesize( $path ) ) {
			throw new \RuntimeException( esc_html__( 'Datei nicht lesbar, leer oder größer als 8 MiB.', 'daily-scripture' ) );
		}
		$xml = file_get_contents( $path );
		if ( false === $xml ) {
			throw new \RuntimeException( esc_html__( 'Die Datei konnte nicht gelesen werden.', 'daily-scripture' ) );
		}
		$hash = hash( 'sha256', $xml );
		if ( 'PK' === substr( $xml, 0, 2 ) ) {
			$xml = $this->unzip( $path, $source );
		}
		if ( ! class_exists( '\DOMDocument' ) ) {
			throw new \RuntimeException( esc_html__( 'Die PHP-Erweiterung DOM/XML wird benötigt.', 'daily-scripture' ) );
		}
		// Reject DTDs entirely: no external requests, entity substitution or entity bombs.
		if ( false !== strpos( $xml, "\0" ) || preg_match( '/<!\s*(DOCTYPE|ENTITY)/i', $xml ) ) {
			throw new \RuntimeException( esc_html__( 'XML mit DTD, Entitäten oder UTF-16-Kodierung wird nicht unterstützt. Bitte eine offizielle UTF-8-Datei verwenden.', 'daily-scripture' ) );
		}
		if ( substr_count( $xml, '<' ) > 20000 ) {
			throw new \RuntimeException( esc_html__( 'Die XML-Datei enthält zu viele Elemente.', 'daily-scripture' ) );
		}
		$previous = libxml_use_internal_errors( true );
		try {
			$document                     = new \DOMDocument();
			$document->resolveExternals   = false;
			$document->substituteEntities = false;
			$loaded                       = $document->loadXML( $xml, LIBXML_NONET );
			if ( ! $loaded || $document->doctype || ! $document->documentElement ) {
				throw new \RuntimeException( esc_html__( 'Die Datei enthält kein gültiges, unterstütztes XML.', 'daily-scripture' ) );
			}
		} finally {
			libxml_clear_errors();
			libxml_use_internal_errors( $previous );
		}
		$record = 'herrnhuter' === $source ? $this->herrnhuter( $document ) : $this->bible2( $document, $year );
		YearValidator::days( $record['days'], $source, $year );
		$record['schema']      = 1;
		$record['source']      = $source;
		$record['year']        = $year;
		$record['sha256']      = $hash;
		$record['imported_at'] = gmdate( 'c' );
		return $record;
	}

	/**
	 * Read a single source document without extracting archive entries.
	 *
	 * @param string $path Archive path.
	 * @param string $source Selected source.
	 * @return string XML bytes.
	 * @throws \RuntimeException For unsupported or oversized archives.
	 */
	private function unzip( string $path, string $source ): string {
		if ( ! class_exists( '\ZipArchive' ) ) {
			throw new \RuntimeException( esc_html__( 'ZIP-Unterstützung fehlt. Bitte die XML-/TWD-Datei direkt hochladen.', 'daily-scripture' ) );
		}
		$zip = new \ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			throw new \RuntimeException( esc_html__( 'Das ZIP-Archiv ist beschädigt.', 'daily-scripture' ) );
		}
		try {
			if ( $zip->numFiles > 30 ) {
				throw new \RuntimeException( esc_html__( 'Das Archiv enthält zu viele Einträge.', 'daily-scripture' ) );
			}
			$matches = array();
			for ( $i = 0; $i < $zip->numFiles; ++$i ) {
				$entry = $zip->statIndex( $i );
				if ( ! $entry || preg_match( '~(^/|\\\\|(^|/)\.\.(/|$)|^[A-Za-z]:)~', $entry['name'] ) ) {
					throw new \RuntimeException( esc_html__( 'Unsichere Dateipfade im Archiv.', 'daily-scripture' ) );
				}
				$extension = strtolower( pathinfo( $entry['name'], PATHINFO_EXTENSION ) );
				if ( in_array( $extension, 'herrnhuter' === $source ? array( 'xml' ) : array( 'xml', 'twd' ), true ) ) {
					if ( $entry['size'] > self::MAX_BYTES || ! empty( $entry['encryption_method'] ) ) {
						throw new \RuntimeException( esc_html__( 'Die XML-Datei im Archiv ist zu groß oder verschlüsselt.', 'daily-scripture' ) );
					}
					$matches[] = $i;
				}
			}
			if ( 1 !== count( $matches ) ) {
				throw new \RuntimeException( esc_html__( 'Das Archiv muss genau eine XML-/TWD-Jahresdatei enthalten.', 'daily-scripture' ) );
			}
			$content = $zip->getFromIndex( $matches[0], self::MAX_BYTES + 1 );
			if ( false === $content || strlen( $content ) > self::MAX_BYTES ) {
				throw new \RuntimeException( esc_html__( 'Archivinhalt konnte nicht sicher gelesen werden.', 'daily-scripture' ) );
			}
			return $content;
		} finally {
			$zip->close();
		}
	}

	/**
	 * Select direct child elements, avoiding ambiguous descendant matching.
	 *
	 * @param \DOMNode $node Parent node.
	 * @param string   $name Element name.
	 * @return array
	 */
	private function children( \DOMNode $node, string $name ): array {
		$result = array();
		foreach ( $node->childNodes as $child ) {
			if ( $child instanceof \DOMElement && $name === $child->tagName ) {
				$result[] = $child;
			}
		}
		return $result;
	}

	/**
	 * Read exactly one element, retaining its original text content.
	 *
	 * @param \DOMNode $node Parent.
	 * @param string   $name Child name.
	 * @param bool     $optional Whether omission is allowed.
	 * @return string
	 * @throws \RuntimeException For missing/duplicate or unsupported markup.
	 */
	private function field( \DOMNode $node, string $name, bool $optional = false ): string {
		$nodes = $this->children( $node, $name );
		if ( $optional && ! $nodes ) {
			return '';
		}
		if ( 1 !== count( $nodes ) ) {
			throw new \RuntimeException( esc_html__( 'Ein erforderliches XML-Feld fehlt oder ist mehrfach vorhanden.', 'daily-scripture' ) );
		}
		foreach ( $nodes[0]->getElementsByTagName( '*' ) as $child ) {
			if ( ! ( 'text' === $name && 'em' === $child->tagName ) && ! ( 'copyright' === $name && 'biblecopyright' === $child->tagName ) ) {
				throw new \RuntimeException( esc_html__( 'Nicht unterstützte XML-Formatierung.', 'daily-scripture' ) );
			}
		}
		return YearValidator::text( $nodes[0]->textContent );
	}

	/**
	 * Insert a pair, rejecting duplicate dates before normalization.
	 *
	 * @param array  $days Accumulated records.
	 * @param string $date ISO date.
	 * @param array  $items Verse pair.
	 * @return void
	 * @throws \RuntimeException For duplicates or invalid dates.
	 */
	private function append( array &$days, string $date, array $items ): void {
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/D', $date ) || isset( $days[ $date ] ) ) {
			throw new \RuntimeException( esc_html__( 'Doppeltes oder ungültiges Tagesdatum.', 'daily-scripture' ) );
		}
		$days[ $date ] = $items;
	}

	/**
	 * Parse the official FreeXml/Losungen format.
	 *
	 * @param \DOMDocument $document XML document.
	 * @return array
	 * @throws \RuntimeException For wrong format.
	 */
	private function herrnhuter( \DOMDocument $document ): array {
		$root = $document->documentElement;
		if ( 'FreeXml' !== $root->tagName ) {
			throw new \RuntimeException( esc_html__( 'Erwartet wird die offizielle Losungen-XML-Datei (FreeXml).', 'daily-scripture' ) );
		}
		$days = array();
		foreach ( $this->children( $root, 'Losungen' ) as $day ) {
			$raw_date = $this->field( $day, 'Datum' );
			if ( ! preg_match( '/^(\d{4}-\d{2}-\d{2})(?:T00:00:00(?:\.000)?)?$/D', $raw_date, $match ) ) {
				throw new \RuntimeException( esc_html__( 'Ungültiges Datum in der Losungen-Datei.', 'daily-scripture' ) );
			}
			$this->append(
				$days,
				$match[1],
				array(
					array(
						'text'      => $this->field( $day, 'Losungstext' ),
						'reference' => $this->field( $day, 'Losungsvers' ),
					),
					array(
						'text'      => $this->field( $day, 'Lehrtext' ),
						'reference' => $this->field( $day, 'Lehrtextvers' ),
					),
				)
			);
		}
		return array(
			'days'      => $days,
			'edition'   => 'Die Losungen',
			'copyright' => '© Evangelische Brüder-Unität – Herrnhuter Brüdergemeine',
		);
	}

	/**
	 * Parse documented TWD 1.1; preserve introductory and copyright text.
	 *
	 * @param \DOMDocument $document XML document.
	 * @param int          $year Selected year.
	 * @return array
	 * @throws \RuntimeException For unsupported format or missing metadata.
	 */
	private function bible2( \DOMDocument $document, int $year ): array {
		$root = $document->documentElement;
		if ( 'thewordfile' !== $root->tagName || '1.1' !== $root->getAttribute( 'dtdvers' ) || (string) $year !== $root->getAttribute( 'year' ) ) {
			throw new \RuntimeException( esc_html__( 'Erwartet wird eine TWD-1.1-Datei für das ausgewählte Jahr.', 'daily-scripture' ) );
		}
		$heads = $this->children( $root, 'head' );
		if ( 1 !== count( $heads ) ) {
			throw new \RuntimeException( esc_html__( 'Die TWD-Datei enthält keinen eindeutigen Metadatenkopf.', 'daily-scripture' ) );
		}
		$days = array();
		foreach ( $this->children( $root, 'theword' ) as $day ) {
			$parols = $this->children( $day, 'parol' );
			if ( 2 !== count( $parols ) ) {
				throw new \RuntimeException( esc_html__( 'Jeder Bible-2.0-Tag muss genau zwei Bibelworte enthalten.', 'daily-scripture' ) );
			}
			$items = array();
			foreach ( $parols as $parol ) {
				$items[] = array(
					'intro'     => $this->field( $parol, 'intro', true ),
					'text'      => $this->field( $parol, 'text' ),
					'reference' => $this->field( $parol, 'ref' ),
				);
			}
			$this->append( $days, $day->getAttribute( 'date' ), $items );
		}
		return array(
			'days'      => $days,
			'edition'   => $this->field( $heads[0], 'biblename' ),
			'copyright' => $this->field( $heads[0], 'copyright' ),
		);
	}
}
