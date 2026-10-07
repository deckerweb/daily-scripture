<?php
/**
 * Optional native Elementor widget registration.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations\Elementor;

defined( 'ABSPATH' ) || exit;

/** Register lazily, independent of plugin activation order. */
final class ElementorIntegration {
	/**
	 * Listen even when Elementor has not finished loading yet.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action(
			'elementor/widgets/register',
			/**
			 * Register both widgets with the optional Elementor manager.
			 *
			 * @param object $manager Elementor widget manager supplied by its registration hook.
			 * @return void
			 */
			static function ( $manager ) {
				if ( class_exists( '\Elementor\Widget_Base' ) && is_object( $manager ) && method_exists( $manager, 'register' ) ) {
					$manager->register( new ScriptureWidget() );
					$manager->register( new PassageWidget() );
				}
			}
		);
	}
}
