<?php
/**
 * Native Bricks styling controls, including its configured breakpoints.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations\Bricks;

use Deckerweb\DailyScripture\Integrations\BuilderStyles;

defined( 'ABSPATH' ) || exit;

/** Let Bricks generate per-element and responsive rules through its native engine. */
final class BricksStyles {
	/**
	 * Add style groups alongside the builder's standard outer wrapper controls.
	 *
	 * @param \Bricks\Element $element Native element.
	 * @param bool            $passage Whether to show passage-specific controls.
	 * @return void
	 */
	public static function groups( \Bricks\Element $element, bool $passage = false ): void {
		foreach ( BuilderStyles::parts( $passage ) as $part => $definition ) {
			$element->control_groups[ 'ds_' . $part ] = array(
				'title' => $definition[0],
				'tab'   => 'style',
			);
		}
		$element->control_groups['ds_box'] = array(
			'title' => __( 'Verse surface & spacing', 'daily-scripture' ),
			'tab'   => 'style',
		);
	}

	/**
	 * Append native typography, color, border and spacing fields without defaults.
	 *
	 * @param \Bricks\Element $element Native element.
	 * @param bool            $passage Whether to show passage-specific controls.
	 * @return void
	 */
	public static function controls( \Bricks\Element $element, bool $passage = false ): void {
		foreach ( BuilderStyles::parts( $passage ) as $part => $definition ) {
			$css = array();
			foreach ( $definition[1] as $child ) {
				$css[] = array(
					'property'  => 'font',
					'selector'  => BuilderStyles::selector( $child ),
					'important' => true,
				);
			}
			$element->controls[ 'ds_' . $part . '_typography' ] = array(
				'tab'   => 'style',
				'group' => 'ds_' . $part,
				'label' => __( 'Typography & color', 'daily-scripture' ),
				'type'  => 'typography',
				'css'   => $css,
				'popup' => false,
			);
		}
		foreach ( array(
			'background' => array( __( 'Background', 'daily-scripture' ), '--ds-bg' ),
			'accent'     => array( __( 'Accent color', 'daily-scripture' ), '--ds-accent' ),
		) as $key => $definition ) {
			$element->controls[ 'ds_' . $key ] = array(
				'tab'   => 'style',
				'group' => 'ds_box',
				'label' => $definition[0],
				'type'  => 'color',
				'css'   => array(
					array(
						'property'  => $definition[1],
						'selector'  => BuilderStyles::selector(),
						'important' => true,
					),
				),
			);
		}
		$element->controls['ds_border']  = array(
			'tab'   => 'style',
			'group' => 'ds_box',
			'label' => __( 'Borders & corners', 'daily-scripture' ),
			'type'  => 'border',
			'css'   => array(
				array(
					'property'  => 'border',
					'selector'  => BuilderStyles::selector( '.daily-scripture__source' ),
					'important' => true,
				),
			),
		);
		$element->controls['ds_padding'] = array(
			'tab'   => 'style',
			'group' => 'ds_box',
			'label' => __( 'Padding', 'daily-scripture' ),
			'type'  => 'dimensions',
			'css'   => array(
				array(
					'property'  => 'padding',
					'selector'  => BuilderStyles::selector( '.daily-scripture__source' ),
					'important' => true,
				),
			),
		);
		foreach ( BuilderStyles::spacing( $passage ) as $key => $definition ) {
			$element->controls[ 'ds_gap_' . $key ] = array(
				'tab'   => 'style',
				'group' => 'ds_box',
				'label' => $definition[0],
				'type'  => 'number',
				'units' => true,
				'min'   => 0,
				'css'   => array(
					array(
						'property'  => $definition[2],
						'selector'  => BuilderStyles::selector( $definition[1] ),
						'important' => true,
					),
				),
			);
		}
		$element->controls['ds_style_help'] = array(
			'tab'     => 'style',
			'group'   => 'ds_box',
			'type'    => 'info',
			'content' => esc_html__( 'Blank values inherit plugin defaults. Custom values apply only to this element. Use Bricks breakpoints for responsive settings. Outer margins remain in general layout settings.', 'daily-scripture' ),
		);
	}
}
