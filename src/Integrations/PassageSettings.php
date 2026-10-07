<?php
/**
 * Shared controls and rendering for native Bible passage integrations.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations;

use Deckerweb\DailyScripture\Bible\{Passage, TranslationManager};
use Deckerweb\DailyScripture\Core\Presentation;

defined( 'ABSPATH' ) || exit;

/** Keep passage selection consistent across builders. */
final class PassageSettings {
	/**
	 * Describe content controls without coupling to a builder API.
	 *
	 * @return array
	 */
	public static function controls(): array {
		$fields = array(
			'translation' => array(
				'label'   => __( 'Translation', 'daily-scripture' ),
				'type'    => 'select',
				'default' => 'luther-1912',
				'options' => array_map( /**
										 * Expose the edition display label to builder controls.
										 *
										 * @param array $edition Reviewed edition metadata.
										 * @return string
										 */
					static fn( $edition ) => $edition['label'],
					( new TranslationManager() )->bundled()
				),
			),
			'book'        => array(
				'label'   => __( 'Book', 'daily-scripture' ),
				'type'    => 'select',
				'default' => 'JOH',
				'options' => TranslationManager::books(),
			),
			'chapter'     => array(
				'label'   => __( 'Chapter', 'daily-scripture' ),
				'type'    => 'number',
				'default' => 3,
			),
			'from'        => array(
				'label'   => __( 'First verse', 'daily-scripture' ),
				'type'    => 'number',
				'default' => 16,
			),
			'to'          => array(
				'label'   => __( 'Last verse', 'daily-scripture' ),
				'type'    => 'number',
				'default' => 16,
			),
			'title'       => array(
				'label'       => __( 'Custom heading', 'daily-scripture' ),
				'type'        => 'text',
				'default'     => '',
				'placeholder' => __( 'Bible reference as title', 'daily-scripture' ),
			),
		);
		foreach ( $fields as &$field ) {
			$field['group'] = 'source';
		}
		unset( $field );
		foreach ( array(
			'layout'  => __( 'Layout', 'daily-scripture' ),
			'density' => __( 'Spacing mode', 'daily-scripture' ),
			'theme'   => __( 'Color scheme', 'daily-scripture' ),
		) as $key => $label ) {
			$fields[ $key ] = array(
				'label'   => $label,
				'type'    => 'select',
				'default' => '',
				'group'   => 'display',
				'options' => array( '' => __( 'Site setting', 'daily-scripture' ) ) + Presentation::choices()[ $key ],
			);
		}
		return $fields;
	}
	/**
	 * Render bounded, validated selections with native style support.
	 *
	 * @param mixed $settings Untrusted saved builder values.
	 * @return string Escaped shared passage markup.
	 */
	public static function render( $settings ): string {
		$settings = is_array( $settings ) ? $settings : array();
		$values   = array();
		foreach ( self::controls() as $key => $field ) {
			$values[ $key ] = $settings[ $key ] ?? $field['default'];
		}
		return str_replace( 'data-ds-component', 'data-ds-builder data-ds-component', ( new Passage() )->render( $values ) );
	}
}
