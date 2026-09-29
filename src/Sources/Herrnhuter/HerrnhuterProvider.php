<?php
/**
 * Local source adapter.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Sources\Herrnhuter;

use Deckerweb\DailyScripture\Sources\LocalProvider;

defined( 'ABSPATH' ) || exit;

/** Provider identity; all reads use the shared validated store. */
final class HerrnhuterProvider extends LocalProvider {
	/**
	 * Source key.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'herrnhuter';
	}

	/**
	 * Publisher display name.
	 *
	 * @return string
	 */
	public function label(): string {
		return 'Die Losungen';
	}
}
