<?php
/**
 * One control contract for optional page builders.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations;

use Deckerweb\DailyScripture\Core\Presentation;
use Deckerweb\DailyScripture\Core\Renderer;

defined( 'ABSPATH' ) || exit;

/** Keep builder settings independent from licenses, imported data and admin options. */
final class BuilderSettings {
	/**
	 * Control definitions shared by Elementor and Bricks.
	 *
	 * @return array
	 */
	public static function controls(): array {
		$inherit = array( '' => __( 'Website-Einstellung', 'daily-scripture' ) );
		$fields  = array(
			'source' => array(
				'label'   => __( 'Datenquelle', 'daily-scripture' ),
				'options' => $inherit + array(
					'herrnhuter' => 'Die Losungen',
					'bible2'     => 'Bible 2.0',
					'both'       => __( 'Beide Quellen', 'daily-scripture' ),
				),
				'group'   => 'source',
			),
		);
		foreach ( array(
			'herrnhuter' => __( 'Überschrift für Die Losungen', 'daily-scripture' ),
			'bible2'     => __( 'Überschrift für Bible 2.0', 'daily-scripture' ),
		) as $provider => $label ) {
			$fields[ 'title_' . $provider ] = array(
				'label'       => $label,
				'placeholder' => Presentation::heading( $provider ),
				'group'       => 'source',
			);
		}
		foreach ( array(
			'layout'  => __( 'Layout', 'daily-scripture' ),
			'density' => __( 'Ansicht', 'daily-scripture' ),
			'theme'   => __( 'Farbschema', 'daily-scripture' ),
		) as $key => $label ) {
			$fields[ $key ] = array(
				'label'   => $label,
				'options' => $inherit + Presentation::choices()[ $key ],
				'group'   => 'display',
			);
		}
		return $fields;
	}

	/**
	 * Shared frontend output, including during builder AJAX previews.
	 *
	 * @param mixed $settings Untrusted builder settings.
	 * @return string Escaped provider output.
	 */
	public static function render( $settings ): string {
		$settings = is_array( $settings ) ? $settings : array();
		$display  = array();
		foreach ( self::controls() as $key => $definition ) {
			$value = $settings[ $key ] ?? '';
			if ( isset( $definition['options'] ) ) {
				$display[ $key ] = is_string( $value ) && isset( $definition['options'][ $value ] ) ? $value : '';
			} else {
				$display[ $key ] = Presentation::sanitize_heading( $value );
			}
		}
		// Native builder typography must also work in their live CSS engines.
		return str_replace( 'data-ds-component', 'data-ds-builder data-ds-component', ( new Renderer() )->render( $display['source'], null, $display, false ) );
	}
}
