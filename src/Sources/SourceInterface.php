<?php
/**
 * Contract for daily paired verse sources.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Sources;

/** Contract for daily paired verse sources. */
interface SourceInterface {
	/**
	 * Return the stable source key.
	 *
	 * @return string
	 */
	public function id(): string;
	/**
	 * Return the publisher display name.
	 *
	 * @return string
	 */
	public function label(): string;
	/**
	 * Read a daily source envelope.
	 *
	 * @param \DateTimeInterface $date Requested date.
	 * @return array
	 */
	public function get_for_date( \DateTimeInterface $date ): array;
	/**
	 * List validated installed years.
	 *
	 * @return array
	 */
	public function installed_years(): array;
}
