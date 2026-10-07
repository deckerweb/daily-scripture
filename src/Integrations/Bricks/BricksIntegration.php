<?php
/**
 * Optional Bricks element registration after the active theme has loaded.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations\Bricks;

defined( 'ABSPATH' ) || exit;

/** Avoid checking theme constants prematurely during plugins_loaded. */
final class BricksIntegration {
	/**
	 * Attach the documented registration hook without requiring Bricks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action(
			'init',
			/**
			 * Register native elements once the optional Bricks API is available.
			 *
			 * @return void
			 */
			static function () {
				if ( class_exists( '\Bricks\Elements' ) && class_exists( '\Bricks\Element' ) ) {
					\Bricks\Elements::register_element( __DIR__ . '/ScriptureElement.php', 'daily-scripture', ScriptureElement::class );
					\Bricks\Elements::register_element( __DIR__ . '/PassageElement.php', 'daily-scripture-passage', PassageElement::class );
				}
			},
			11
		);
	}
}
