<?php
/**
 * Shared, validated presentation options.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

defined( 'ABSPATH' ) || exit;

/** Presentation settings independent of imported source text. */
final class Presentation {
	/**
	 * Selectable layouts and display options.
	 *
	 * @return array
	 */
	public static function choices(): array {
		return array(
			'layout'       => array(
				'card'      => __( 'Card', 'daily-scripture' ),
				'minimal'   => __( 'Plain', 'daily-scripture' ),
				'accent'    => __( 'Accent bar', 'daily-scripture' ),
				'editorial' => __( 'Reading columns', 'daily-scripture' ),
				'paper'     => __( 'Reading sheet', 'daily-scripture' ),
				'ribbon'    => __( 'Title band', 'daily-scripture' ),
				'outline'   => __( 'Outline', 'daily-scripture' ),
				'divided'   => __( 'Split verses', 'daily-scripture' ),
				'journal'   => __( 'Journal', 'daily-scripture' ),
				'quiet'     => __( 'Quiet focus', 'daily-scripture' ),
			),
			'density'      => array(
				'standard' => __( 'Standard', 'daily-scripture' ),
				'compact'  => __( 'Compact – sidebar / footer', 'daily-scripture' ),
			),
			'theme'        => array(
				'light'  => __( 'Light', 'daily-scripture' ),
				'dark'   => __( 'Dark', 'daily-scripture' ),
				'auto'   => __( 'Automatic (device setting)', 'daily-scripture' ),
				'custom' => __( 'Custom colors', 'daily-scripture' ),
			),
			'heading_size' => array(
				'small'  => __( 'Small', 'daily-scripture' ),
				'medium' => __( 'Medium', 'daily-scripture' ),
				'large'  => __( 'Large', 'daily-scripture' ),
				'xlarge' => __( 'Extra large', 'daily-scripture' ),
			),
		);
	}

	/**
	 * Defaults for upgrades and new installations.
	 *
	 * @return array
	 */
	public static function defaults(): array {
		return array(
			'title_herrnhuter' => '',
			'title_bible2'     => '',
			'date_format'      => '',
			'type_scale'       => 100,
			'expert_mode'      => 0,
			'font_heading'     => '',
			'font_verse'       => '',
			'font_reference'   => '',
			'font_meta'        => '',
			'font_date'        => '',

			'size_heading'     => 28,
			'size_verse'       => 22,
			'size_reference'   => 18,
			'size_meta'        => 14,
			'size_date'        => 16,
			'color_heading'    => '',
			'color_verse'      => '',
			'color_reference'  => '',
			'color_meta'       => '',
			'color_date'       => '',
			'layout'           => 'card',
			'density'          => 'standard',
			'theme'            => 'light',
			'heading_size'     => 'large',
			'color_background' => '#ffffff',
			'color_text'       => '#202b36',
			'color_accent'     => '#245c73',
		);
	}

	/**
	 * Sanitize only display settings; callers can pass per-instance overrides.
	 *
	 * @param array $input Candidate options.
	 * @return array
	 */
	public static function sanitize( array $input ): array {
		$result = self::defaults();
		foreach ( self::choices() as $key => $choices ) {
			if ( isset( $input[ $key ] ) && is_string( $input[ $key ] ) && isset( $choices[ $input[ $key ] ] ) ) {
				$result[ $key ] = $input[ $key ];
			}
		}
		foreach ( array( 'color_background', 'color_text', 'color_accent', 'color_heading', 'color_verse', 'color_reference', 'color_meta', 'color_date' ) as $key ) {
			$color = isset( $input[ $key ] ) && is_string( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : null;
			if ( $color ) {
				$result[ $key ] = $color;
			}
		}
		if ( isset( $input['date_format'] ) && is_string( $input['date_format'] ) ) {
			$result['date_format'] = substr( sanitize_text_field( $input['date_format'] ), 0, 80 );
		}
		foreach ( self::ranges() as $key => $range ) {
			if ( isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) && is_numeric( $input[ $key ] ) ) {
				$result[ $key ] = max( $range[0], min( $range[1], (int) $input[ $key ] ) );
			}
		}
		foreach ( array( 'heading', 'verse', 'reference', 'meta', 'date' ) as $part ) {
			$result[ 'font_' . $part ] = self::sanitize_font( $input[ 'font_' . $part ] ?? '' );
		}
		foreach ( array( 'title_herrnhuter', 'title_bible2' ) as $key ) {
			$result[ $key ] = self::sanitize_heading( $input[ $key ] ?? '' );
		}
		$result['expert_mode'] = isset( $input['expert_mode'] ) && in_array( $input['expert_mode'], array( 1, '1', true ), true ) ? 1 : 0;
		return $result;
	}

	/**
	 * Accept a positive CSS length or a named design token with optional length fallback.
	 * Arbitrary CSS functions, declarations and plugin-internal tokens are excluded.
	 *
	 * @param mixed $value Candidate CSS font size.
	 * @return string Valid size or empty inheritance marker.
	 */
	public static function sanitize_font( $value ): string {
		if ( ! is_string( $value ) || strlen( $value ) > 120 ) {
			return '';
		}
		$value  = trim( $value );
		$length = '(?:[0-9]+(?:\.[0-9]+)?|\.[0-9]+)(?:px|rem|em|%)';
		if ( preg_match( '/^' . $length . '$/D', $value ) ) {
			return (float) $value > 0 && (float) $value <= 1000 ? $value : '';
		}
		if ( preg_match( '/^var\((--[a-zA-Z_][a-zA-Z0-9_-]*)(?:,\s*(' . $length . '))?\)$/D', $value, $matches ) && 0 !== strpos( strtolower( $matches[1] ), '--ds-' ) ) {
			if ( isset( $matches[2] ) && '' === self::sanitize_font( $matches[2] ) ) {
				return '';
			}
			return 'var(' . $matches[1] . ( isset( $matches[2] ) ? ', ' . $matches[2] : '' ) . ')';
		}
		return '';
	}

	/**
	 * Plain-text headings are independent of the immutable imported verses.
	 *
	 * @param mixed $value Candidate heading.
	 * @return string
	 */
	public static function sanitize_heading( $value ): string {
		return is_string( $value ) ? mb_substr( sanitize_text_field( $value ), 0, 160 ) : '';
	}

	/**
	 * Resolve a block override, site heading, then the site-language default.
	 * Empty values intentionally inherit, including in existing saved blocks.
	 *
	 * @param string $source Provider identifier.
	 * @param array  $overrides Optional per-block headings.
	 * @return string
	 */
	public static function heading( string $source, array $overrides = array() ): string {
		$key   = 'title_' . $source;
		$title = self::sanitize_heading( $overrides[ $key ] ?? '' );
		if ( '' === $title ) {
			$title = self::sanitize_heading( Settings::get( $key, '' ) );
		}
		return '' !== $title ? $title : self::default_heading( $source );
	}

	/**
	 * Default follows the website language, not an editor's profile language.
	 *
	 * @param string $source Provider identifier.
	 * @return string
	 */
	public static function default_heading( string $source ): string {
		if ( 'herrnhuter' === $source ) {
			return __( 'Die Losungen', 'daily-scripture' );
		}
		$site_locale = get_option( 'WPLANG', defined( 'WPLANG' ) ? WPLANG : 'en_US' );
		// Publisher titles are selected by site language, independently of the editor locale.
		return 0 === strpos( (string) $site_locale, 'de' ) ? 'Das Wort für heute' : 'The Word for Today';
	}

	/**
	 * Numeric limits shared by the form, importer and renderer.
	 *
	 * @return array
	 */
	public static function ranges(): array {
		return array(
			'type_scale'     => array( 85, 160 ),
			'size_heading'   => array( 16, 64 ),
			'size_verse'     => array( 14, 48 ),
			'size_reference' => array( 12, 36 ),
			'size_meta'      => array( 12, 28 ),
			'size_date'      => array( 12, 28 ),
		);
	}

	/**
	 * Resolve options into safe classes and CSS variables.
	 *
	 * @param array     $overrides Per-instance overrides.
	 * @param bool|null $admin Explicit context for isolated frontend preview.
	 * @return array
	 */
	public static function resolve( array $overrides = array(), ?bool $admin = null ): array {
		/**
		* Retain only explicit nonempty instance overrides.
		*
		* @param mixed $value Candidate setting override.
		* @return bool
		*/
		$has_override = static fn( $value ) => '' !== $value;
		$options      = self::sanitize( array_merge( Settings::all(), array_filter( $overrides, $has_override ) ) );
		// A preview may explicitly clear an optional format or expert color.
		foreach ( array( 'date_format', 'color_heading', 'color_verse', 'color_reference', 'color_meta', 'color_date', 'font_heading', 'font_verse', 'font_reference', 'font_meta', 'font_date' ) as $key ) {
			if ( array_key_exists( $key, $overrides ) && '' === $overrides[ $key ] ) {
				$options[ $key ] = '';
			}
		}
		$admin   = $admin ?? is_admin();
		$classes = 'daily-scripture';
		foreach ( array( 'layout', 'density', 'theme', 'heading_size' ) as $key ) {
			$classes .= ' daily-scripture--' . $key . '-' . $options[ $key ];
		}
		$style = '';
		if ( 'custom' === $options['theme'] ) {
			$style = '--ds-bg:' . $options['color_background'] . ';--ds-text:' . $options['color_text'] . ';--ds-accent:' . $options['color_accent'] . ';';
		}
		$defaults = self::defaults();
		$ratio    = $options['type_scale'] / 100;
		foreach ( array( 'heading', 'verse', 'reference', 'meta', 'date' ) as $part ) {
			$size = $options['expert_mode'] ? $options[ 'size_' . $part ] : $defaults[ 'size_' . $part ];
			$font = $options['expert_mode'] ? $options[ 'font_' . $part ] : '';
			if ( '' === $font ) {
				$font = number_format( $size * $ratio, 2, '.', '' ) . 'px';
			} else {
				// A missing site token remains readable in the dashboard and isolated preview.
				if ( 0 === strpos( $font, 'var(' ) && false === strpos( $font, ',' ) ) {
					$font = substr( $font, 0, -1 ) . ', ' . $size . 'px)';
				}
				$font = 'calc(' . $font . ' * ' . number_format( $ratio, 2, '.', '' ) . ')';
			}
			$style .= '--ds-' . $part . '-size:' . $font . ';';
			$color  = $options['expert_mode'] ? $options[ 'color_' . $part ] : '';
			if ( $color ) {
				$style .= '--ds-' . $part . '-color:' . $color . ';';
			}
		}
		if ( $admin ) {
			global $_wp_admin_css_colors;
			$scheme   = get_user_option( 'admin_color' );
			$colors   = $_wp_admin_css_colors[ $scheme ]->colors ?? array();
			$accent   = isset( $colors[2] ) ? sanitize_hex_color( $colors[2] ) : '#2271b1';
			$classes .= ' daily-scripture--admin';
			$style   .= '--ds-admin-accent:' . ( $accent ? $accent : '#2271b1' ) . ';';
		}
		return array(
			'classes' => $classes,
			'style'   => $style,
			'format'  => '' !== $options['date_format'] ? $options['date_format'] : get_option( 'date_format', 'F j, Y' ),
		);
	}
}
