<?php
/**
 * Shared provider for validated local years.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Sources;

use Deckerweb\DailyScripture\Data\YearStore;
use Deckerweb\DailyScripture\Data\YearValidator;

defined( 'ABSPATH' ) || exit;

/** Supplies complete pairs only; damaged data never reaches the renderer. */
abstract class LocalProvider implements SourceInterface {
	/**
	 * Return valid installed calendar years.
	 *
	 * @return array
	 */
	public function installed_years(): array {
		try {
			/**
			* Keep only fully validated installed annual records.
			*
			* @param array $row Annual inventory row.
			* @return bool
			*/
			$valid_record = static fn( $row ) => null !== $row['record'];
			return array_keys( array_filter( ( new YearStore() )->inventory( $this->id() ), $valid_record ) );
		} catch ( \RuntimeException $error ) {
			return array();
		}
	}

	/**
	 * Load a date using the common envelope.
	 *
	 * @param \DateTimeInterface $date Requested date.
	 * @return array
	 */
	public function get_for_date( \DateTimeInterface $date ): array {
		$result = array(
			'source' => $this->id(),
			'label'  => $this->label(),
			'date'   => $date->format( 'Y-m-d' ),
			'items'  => array(),
			'status' => 'missing',
		);
		$year   = (int) $date->format( 'Y' );
		if ( ! YearValidator::allowed( $this->id(), $year ) ) {
			$result['status'] = 'restricted';
			return $result;
		}
		try {
			$record = ( new YearStore() )->read( $this->id(), $year );
			if ( $record ) {
				$result['items']     = $record['days'][ $result['date'] ] ?? array();
				$result['edition']   = $record['edition'];
				$result['copyright'] = $record['copyright'];
				$result['status']    = $result['items'] ? 'ok' : 'missing';
			}
		} catch ( \RuntimeException $error ) {
			$result['status'] = 'invalid';
		}
		return $result;
	}
}
