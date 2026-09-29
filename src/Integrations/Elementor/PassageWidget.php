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
		return __( 'Bibelstelle · Daily Scripture', 'daily-scripture' );
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
			'source'  => __( 'Bibelstelle & Übersetzung', 'daily-scripture' ),
			'display' => __( 'Darstellung', 'daily-scripture' ),
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
					$control['description'] = 'title' === $key ? __( 'Leer zeigt die Bibelstelle als Überschrift. Maximal 160 Zeichen.', 'daily-scripture' ) : __( 'Wähle bis zu 50 Verse innerhalb eines Kapitels.', 'daily-scripture' );
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
					'raw'             => esc_html__( 'Die Plugin-Vorgaben sind dein Ausgangspunkt. Unter Stil kannst du Typografie, Farben und Abstände für dieses Element gestalten. Installiere die gewünschte Übersetzung zuerst unter Daily Scripture → Bibelbibliothek.', 'daily-scripture' ),
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
