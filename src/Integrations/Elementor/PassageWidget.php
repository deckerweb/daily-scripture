<?php
/**
 * Native Elementor widget using the shared passage renderer.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations\Elementor;

use Deckerweb\DailyScripture\Integrations\PassageSettings;

defined( 'ABSPATH' ) || exit;

/** Loaded only after Elementor exposes its widget API. */
final class PassageWidget extends \Elementor\Widget_Base {
	/**
	 * Stable saved widget identifier.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'daily-scripture-passage';
	}

	/**
	 * Panel title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Bible passage · Daily Scripture', 'daily-scripture' );
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
		return array( 'bible', 'bibel', 'bibelstelle', 'scripture', 'luther', 'menge' );
	}

	/**
	 * Local library changes must refresh Elementor output.
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
			'source'  => __( 'Passage & translation', 'daily-scripture' ),
			'display' => __( 'Presentation', 'daily-scripture' ),
		) as $group => $label ) {
			$this->start_controls_section( 'daily_scripture_' . $group, array( 'label' => $label ) );
			foreach ( PassageSettings::controls() as $key => $field ) {
				if ( $field['group'] !== $group ) {
					continue;
				}
				$control = array(
					'label'       => $field['label'],
					'type'        => isset( $field['options'] ) ? \Elementor\Controls_Manager::SELECT : ( 'number' === $field['type'] ? \Elementor\Controls_Manager::NUMBER : \Elementor\Controls_Manager::TEXT ),
					'default'     => $field['default'],
					'label_block' => true,
				);
				if ( isset( $field['options'] ) ) {
					$control['options'] = $field['options'];
				} else {
					$control['placeholder'] = $field['placeholder'] ?? '';
					$control['description'] = 'title' === $key ? __( 'Leave blank to use the Bible reference as the heading. Up to 160 characters.', 'daily-scripture' ) : __( 'Choose up to 50 verses within one chapter.', 'daily-scripture' );
				}
				if ( 'number' === $field['type'] ) {
					$control['min']  = 1;
					$control['max']  = 'chapter' === $key ? 150 : 176;
					$control['step'] = 1; }
				$this->add_control( $key, $control );
			}
			$this->add_control(
				'daily_scripture_help_' . $group,
				array(
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'raw'             => esc_html__( 'Plugin defaults are your starting point. Under Style, adjust typography, colors and spacing for this element. Install the selected edition under Daily Scripture → Bible library first.', 'daily-scripture' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				)
			);
			$this->end_controls_section();
		}
		ElementorStyles::register( $this, true );
	}

	/**
	 * Used by Elementor for frontend and server-rendered editor preview alike.
	 *
	 * @return void
	 */
	protected function render(): void {
		echo PassageSettings::render( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared renderer escapes all output.
	}
}
