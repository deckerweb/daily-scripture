<?php
/**
 * Native Bricks element with server-rendered local daily verses.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations\Bricks;

use Deckerweb\DailyScripture\Integrations\BuilderSettings;
use Deckerweb\DailyScripture\Core\Plugin;

defined( 'ABSPATH' ) || exit;

/** Loaded by Bricks only after its parent class exists. */
final class ScriptureElement extends \Bricks\Element {
	/**
	 * Standard element category.
	 *
	 * @var string
	 */
	public $category = 'general';

	/**
	 * Stable element name in saved Bricks data.
	 *
	 * @var string
	 */
	public $name = 'daily-scripture';

	/**
	 * Built-in Themify icon.
	 *
	 * @var string
	 */
	public $icon = 'ti-book';

	/**
	 * Builder panel label.
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Daily Scripture', 'daily-scripture' );
	}

	/**
	 * Searchable element terms.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'bible', 'bibel', 'losungen', 'scripture', 'wort' );
	}

	/**
	 * Content panel sections.
	 *
	 * @return void
	 */
	public function set_control_groups() {
		$this->control_groups['source']  = array(
			'title' => __( 'Quelle & Überschriften', 'daily-scripture' ),
			'tab'   => 'content',
		);
		$this->control_groups['display'] = array(
			'title' => __( 'Darstellung', 'daily-scripture' ),
			'tab'   => 'content',
		);
		BricksStyles::groups( $this );
	}

	/**
	 * Native controls, preserving empty values for site inheritance.
	 *
	 * @return void
	 */
	public function set_controls() {
		foreach ( BuilderSettings::controls() as $key => $field ) {
			$control = array(
				'tab'     => 'content',
				'group'   => $field['group'],
				'label'   => $field['label'],
				'type'    => isset( $field['options'] ) ? 'select' : 'text',
				'default' => '',
			);
			if ( isset( $field['options'] ) ) {
				$control['options']     = $field['options'];
				$control['placeholder'] = __( 'Website-Einstellung', 'daily-scripture' );
			} else {
				$control['placeholder'] = $field['placeholder'];
				$control['description'] = __( 'Leer übernimmt die globale Überschrift. Nur für die zugehörige Quelle; maximal 160 Zeichen.', 'daily-scripture' );
			}
			$this->controls[ $key ] = $control;
		}
		$this->controls['dailyScriptureHelp'] = array(
			'tab'     => 'content',
			'group'   => 'display',
			'type'    => 'info',
			'content' => esc_html__( 'Die Plugin-Vorgaben sind dein Ausgangspunkt. Unter Stil kannst du Typografie, Farben und Abstände für dieses Element gestalten. Tagesdaten und Lizenzhinweise bleiben unverändert.', 'daily-scripture' ),
		);
		BricksStyles::controls( $this );
	}

	/**
	 * Shared assets for frontend and builder canvas.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		( new Plugin() )->styles();
	}

	/**
	 * Preserve the Bricks root attributes around the common provider markup.
	 *
	 * @return void
	 */
	public function render() {
		echo '<div ' . $this->render_attributes( '_root' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Bricks escapes its framework root attributes.
		echo BuilderSettings::render( $this->settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared renderer escapes all dynamic output.
		echo '</div>';
	}
}
