<?php
/**
 * Native Bricks element with server-rendered local Bible passages.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations\Bricks;

use Deckerweb\DailyScripture\Integrations\PassageSettings;
use Deckerweb\DailyScripture\Core\Plugin;

defined( 'ABSPATH' ) || exit;

/** Loaded by Bricks only after its parent class exists. */
final class PassageElement extends \Bricks\Element {
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
	public $name = 'daily-scripture-passage';

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
		return __( 'Bible passage · Daily Scripture', 'daily-scripture' );
	}

	/**
	 * Searchable element terms.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'bible', 'bibel', 'bibelstelle', 'scripture', 'luther', 'menge' );
	}

	/**
	 * Content panel sections.
	 *
	 * @return void
	 */
	public function set_control_groups() {
		$this->control_groups['source']  = array(
			'title' => __( 'Passage & translation', 'daily-scripture' ),
			'tab'   => 'content',
		);
		$this->control_groups['display'] = array(
			'title' => __( 'Presentation', 'daily-scripture' ),
			'tab'   => 'content',
		);
		BricksStyles::groups( $this, true );
	}

	/**
	 * Native controls, preserving empty values for site inheritance.
	 *
	 * @return void
	 */
	public function set_controls() {
		foreach ( PassageSettings::controls() as $key => $field ) {
			$control = array(
				'tab'     => 'content',
				'group'   => $field['group'],
				'label'   => $field['label'],
				'type'    => $field['type'],
				'default' => $field['default'],
			);
			if ( isset( $field['options'] ) ) {
				$control['options']     = $field['options'];
				$control['placeholder'] = __( 'Site setting', 'daily-scripture' );
			} else {
				$control['placeholder'] = $field['placeholder'] ?? '';
				$control['description'] = 'title' === $key ? __( 'Leave blank to use the Bible reference as the heading. Up to 160 characters.', 'daily-scripture' ) : __( 'Choose up to 50 verses within one chapter.', 'daily-scripture' );
			}
			if ( 'number' === $field['type'] ) {
				$control['min']  = 1;
				$control['max']  = 'chapter' === $key ? 150 : 176;
				$control['step'] = 1; }
			$this->controls[ $key ] = $control;
		}
		$this->controls['dailyScriptureHelp'] = array(
			'tab'     => 'content',
			'group'   => 'display',
			'type'    => 'info',
			'content' => esc_html__( 'Plugin defaults are your starting point. Under Style, adjust typography, colors and spacing for this element. Install the selected edition under Daily Scripture → Bible library first.', 'daily-scripture' ),
		);
		BricksStyles::controls( $this, true );
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
		echo PassageSettings::render( $this->settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared renderer escapes all dynamic output.
		echo '</div>';
	}
}
