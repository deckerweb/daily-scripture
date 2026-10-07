<?php
/**
 * Native Elementor widget using the shared daily renderer.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations\Elementor;

use Deckerweb\DailyScripture\Integrations\BuilderSettings;

defined( 'ABSPATH' ) || exit;

/** Loaded only after Elementor exposes its widget API. */
final class ScriptureWidget extends \Elementor\Widget_Base {
	/**
	 * Stable saved widget identifier.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'daily-scripture';
	}

	/**
	 * Panel title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Daily Scripture', 'daily-scripture' );
	}

	/**
	 * Built-in Elementor icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-content';
	}

	/**
	 * Keep the widget discoverable without adding an empty custom category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Widget panel search terms.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'bible', 'bibel', 'losungen', 'scripture', 'wort' );
	}

	/**
	 * Today's verses must never use Elementor's static widget output cache.
	 *
	 * @return bool
	 */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/**
	 * Register native controls; empty settings retain dynamic site inheritance.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		foreach ( array(
			'source'  => __( 'Source & headings', 'daily-scripture' ),
			'display' => __( 'Presentation', 'daily-scripture' ),
		) as $group => $label ) {
			$this->start_controls_section( 'daily_scripture_' . $group, array( 'label' => $label ) );
			foreach ( BuilderSettings::controls() as $key => $field ) {
				if ( $field['group'] !== $group ) {
					continue;
				}
				$control = array(
					'label'       => $field['label'],
					'type'        => isset( $field['options'] ) ? \Elementor\Controls_Manager::SELECT : \Elementor\Controls_Manager::TEXT,
					'default'     => '',
					'label_block' => true,
				);
				if ( isset( $field['options'] ) ) {
					$control['options'] = $field['options'];
				} else {
					$control['placeholder'] = $field['placeholder'];
					$control['description'] = __( 'Leave blank to inherit the global heading. Applies only to the matching source; up to 160 characters.', 'daily-scripture' );
				}
				$this->add_control( $key, $control );
			}
			$this->add_control(
				'daily_scripture_help_' . $group,
				array(
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'raw'             => esc_html__( 'Plugin defaults are your starting point. Under Style, adjust typography, colors and spacing for this element. Daily data and license notices stay unchanged.', 'daily-scripture' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				)
			);
			$this->end_controls_section();
		}
		ElementorStyles::register( $this );
	}

	/**
	 * Used by Elementor for frontend and server-rendered editor preview alike.
	 *
	 * @return void
	 */
	protected function render(): void {
		echo BuilderSettings::render( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared renderer escapes all output.
	}
}
