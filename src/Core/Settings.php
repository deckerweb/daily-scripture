<?php
/**
 * Site settings and backwards-compatible defaults.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

defined( 'ABSPATH' ) || exit;

/** Site settings and backwards-compatible defaults. */
final class Settings {
	/**
	 * Return default settings.
	 *
	 * @return array
	 */
	public static function defaults(): array {
		return array_merge(
			Presentation::defaults(),
			array(
				'default_source'           => 'herrnhuter',
				'dashboard_widget'         => 1,
				'bibleserver_translation'  => 'LUT',
				'remove_data_on_uninstall' => 0,
			)
		);
	}

	/**
	 * Read saved settings safely, including legacy translation codes.
	 *
	 * @return array
	 */
	public static function all(): array {
		$saved                               = get_option( 'daily_scripture_settings', array() );
		$settings                            = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
		$settings['bibleserver_translation'] = is_string( $settings['bibleserver_translation'] ) ? strtoupper( $settings['bibleserver_translation'] ) : 'LUT';
		return $settings;
	}

	/**
	 * Read a single setting.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $fallback Fallback value.
	 * @return mixed
	 */
	public static function get( string $key, $fallback = null ) {
		$settings = self::all();
		return $settings[ $key ] ?? $fallback;
	}
}
