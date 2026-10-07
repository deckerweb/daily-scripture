<?php
/**
 * Resolve registered source providers.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

use Deckerweb\DailyScripture\Sources\SourceInterface;
use Deckerweb\DailyScripture\Sources\Herrnhuter\HerrnhuterProvider;
use Deckerweb\DailyScripture\Sources\Bible2\Bible2Provider;

defined( 'ABSPATH' ) || exit;

/** Resolve registered source providers. */
final class DataManager {
	/**
	 * Registered source providers.
	 *
	 * @var array<string,SourceInterface>
	 */
	private array $sources = array();

	/**
	 * Register the two local source adapters.
	 *
	 * @return void
	 */
	public function __construct() {
		foreach ( array( new HerrnhuterProvider(), new Bible2Provider() ) as $source ) {
			$this->sources[ $source->id() ] = $source;
		}
	}

	/**
	 * Expose registered providers for integrations.
	 *
	 * @return array
	 */
	public function sources(): array {
		return $this->sources;
	}

	/**
	 * Retrieve one or both source pairs.
	 *
	 * @param string                  $source Source key or both.
	 * @param \DateTimeInterface|null $date Requested date, defaulting to site time.
	 * @return array
	 */
	public function get( string $source, ?\DateTimeInterface $date = null ): array {
		$date = $date ?? new \DateTimeImmutable( 'now', wp_timezone() );
		if ( 'both' === $source ) {
			return array_values(
				array_filter(
					array(
						$this->sources['herrnhuter']->get_for_date( $date ),
						$this->sources['bible2']->get_for_date( $date ),
					)
				)
			);
		}
		return isset( $this->sources[ $source ] ) ? array( $this->sources[ $source ]->get_for_date( $date ) ) : array();
	}
}
