<?php
/**
 * Native Elementor style controls and responsive CSS generation.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations\Elementor;

use Deckerweb\DailyScripture\Integrations\BuilderStyles;
use Elementor\Controls_Manager as Controls;
use Elementor\Group_Control_Typography as Typography;
use Elementor\Group_Control_Border as Border;

defined( 'ABSPATH' ) || exit;

/** Explicit user styles outrank component safeguards without removing them globally. */
final class ElementorStyles {
	/**
	 * Native style tab: defaults remain empty, so plugin presets still apply.
	 *
	 * @param \Elementor\Widget_Base $widget Widget receiving the controls.
	 * @param bool                   $passage Whether to show passage-specific controls.
	 * @return void
	 */
	public static function register( \Elementor\Widget_Base $widget, bool $passage = false ): void {
		foreach ( BuilderStyles::parts( $passage ) as $part => $definition ) {
			$selector = implode(
				', ',
				array_map( /**
							* Scope a shared child selector to this Elementor widget instance.
							*
							* @param string $child Component child selector key.
							* @return string
							*/
					static fn( $child ) => '{{WRAPPER}} ' . BuilderStyles::selector( $child ),
					$definition[1]
				)
			);
			$widget->start_controls_section(
				'ds_style_' . $part,
				array(
					'label' => $definition[0],
					'tab'   => Controls::TAB_STYLE,
				)
			);
			$fields = array();
			foreach ( array(
				'font_family'     => 'font-family: "{{VALUE}}", sans-serif',
				'font_size'       => 'font-size: {{SIZE}}{{UNIT}}',
				'font_weight'     => 'font-weight: {{VALUE}}',
				'font_style'      => 'font-style: {{VALUE}}',
				'line_height'     => 'line-height: {{SIZE}}{{UNIT}}',
				'letter_spacing'  => 'letter-spacing: {{SIZE}}{{UNIT}}',
				'word_spacing'    => 'word-spacing: {{SIZE}}{{UNIT}}',
				'text_transform'  => 'text-transform: {{VALUE}}',
				'text_decoration' => 'text-decoration: {{VALUE}}',
			) as $key => $declaration ) {
				$fields[ $key ] = array( 'selectors' => array( '{{SELECTOR}}' => $declaration . ' !important;' ) );
			}
			$widget->add_group_control(
				Typography::get_type(),
				array(
					'name'           => 'ds_' . $part,
					'selector'       => $selector,
					'fields_options' => $fields,
				)
			);
			$widget->add_control(
				'ds_' . $part . '_color',
				array(
					'label'     => __( 'Color', 'daily-scripture' ),
					'type'      => Controls::COLOR,
					'selectors' => array( $selector => 'color: {{VALUE}} !important;' ),
				)
			);
			$widget->add_responsive_control(
				'ds_' . $part . '_align',
				array(
					'label'     => __( 'Alignment', 'daily-scripture' ),
					'type'      => Controls::SELECT,
					'options'   => array(
						''       => __( 'Default', 'daily-scripture' ),
						'start'  => __( 'Start', 'daily-scripture' ),
						'center' => __( 'Center', 'daily-scripture' ),
						'end'    => __( 'End', 'daily-scripture' ),
					),
					'selectors' => array( $selector => 'text-align: {{VALUE}} !important;' ),
				)
			);
			$widget->end_controls_section();
		}
		$root = '{{WRAPPER}} ' . BuilderStyles::selector();
		$box  = '{{WRAPPER}} ' . BuilderStyles::selector( '.daily-scripture__source' );
		$widget->start_controls_section(
			'ds_style_box',
			array(
				'label' => __( 'Surface & spacing', 'daily-scripture' ),
				'tab'   => Controls::TAB_STYLE,
			)
		);
		foreach ( array(
			'background' => array( __( 'Background', 'daily-scripture' ), '--ds-bg' ),
			'accent'     => array( __( 'Accent color', 'daily-scripture' ), '--ds-accent' ),
		) as $key => $definition ) {
			$widget->add_control(
				'ds_' . $key,
				array(
					'label'     => $definition[0],
					'type'      => Controls::COLOR,
					'selectors' => array( $root => $definition[1] . ': {{VALUE}} !important;' ),
				)
			);
		}
		$widget->add_group_control(
			Border::get_type(),
			array(
				'name'     => 'ds_border',
				'selector' => $box,
			)
		);
		foreach ( array(
			'padding' => array( __( 'Padding', 'daily-scripture' ), 'padding' ),
			'radius'  => array( __( 'Rounded corners', 'daily-scripture' ), 'border-radius' ),
		) as $key => $definition ) {
			$widget->add_responsive_control(
				'ds_' . $key,
				array(
					'label'      => $definition[0],
					'type'       => Controls::DIMENSIONS,
					'size_units' => array( 'px', 'em', 'rem', '%' ),
					'selectors'  => array( $box => $definition[1] . ': {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
				)
			);
		}
		foreach ( BuilderStyles::spacing( $passage ) as $key => $definition ) {
			$widget->add_responsive_control(
				'ds_gap_' . $key,
				array(
					'label'      => $definition[0],
					'type'       => Controls::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 160,
						),
					),
					'selectors'  => array( '{{WRAPPER}} ' . BuilderStyles::selector( $definition[1] ) => $definition[2] . ': {{SIZE}}{{UNIT}} !important;' ),
				)
			);
		}
		$widget->add_control(
			'ds_style_help',
			array(
				'type' => Controls::RAW_HTML,
				'raw'  => esc_html__( 'Blank values inherit plugin defaults. Custom values apply only to this element. Use the device icon for responsive values; outer margins are under Advanced.', 'daily-scripture' ),
			)
		);
		$widget->end_controls_section();
	}
}
