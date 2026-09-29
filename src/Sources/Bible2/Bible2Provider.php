<?php
/**
 * Local source adapter.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Sources\Bible2;

use Deckerweb\DailyScripture\Sources\LocalProvider;

defined( 'ABSPATH' ) || exit;

/** Provider identity; all reads use the shared validated store. */
final class Bible2Provider extends LocalProvider {
	/**
	 * Source key.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'bible2';
	}

	/**
	 * Publisher display name.
	 *
	 * @return string
	 */
	public function label(): string {
		return 'Bible 2.0';
	}
}
