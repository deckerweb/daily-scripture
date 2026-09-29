<?php
/**
 * Semantic styling targets shared by the native builders.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations;

defined( 'ABSPATH' ) || exit;

/** Native builder CSS is scoped to one element and never changes source records. */
final class BuilderStyles {
	/**
	 * Text roles and their descendants requiring explicit native style overrides.
	 *
	 * @param bool $passage Whether the component displays a fixed passage.
	 * @return array
	 */
	public static function parts( bool $passage = false ): array {
		$parts = array(
			'heading'   => array( __( 'Überschrift', 'daily-scripture' ), array( '.daily-scripture__title' ) ),
			'date'      => array( __( 'Datum', 'daily-scripture' ), array( '.daily-scripture__date' ) ),
			'verse'     => array( __( 'Bibelverse', 'daily-scripture' ), array( '.daily-scripture__text' ) ),
			'reference' => array( __( 'Bibelstellen', 'daily-scripture' ), array( '.daily-scripture__reference', '.daily-scripture__reference a' ) ),
			'meta'      => array( __( 'Lizenz & Zusatzinfos', 'daily-scripture' ), array( '.daily-scripture__meta', '.daily-scripture__meta p', '.daily-scripture__meta a', '.daily-scripture__meta details', '.daily-scripture__meta summary' ) ),
		);
		if ( $passage ) {
			unset( $parts['date'] ); }
		return $parts;
	}

	/**
	 * Individual spacing properties, naturally responsive in each builder.
	 *
	 * @param bool $passage Whether the component displays a fixed passage.
	 * @return array
	 */
	public static function spacing( bool $passage = false ): array {
		$spacing = array(
			'sources'   => array( __( 'Zwischen den Quellen', 'daily-scripture' ), '', 'gap' ),
			'sections'  => array( __( 'Zwischen Kopf, Versen und Hinweisen', 'daily-scripture' ), '.daily-scripture__source', 'gap' ),
			'verses'    => array( __( 'Zwischen den Versen', 'daily-scripture' ), '.daily-scripture__verses', 'gap' ),
			'reference' => array( __( 'Vor der Bibelstelle', 'daily-scripture' ), '.daily-scripture__reference', 'margin-top' ),
			'meta'      => array( __( 'Innenabstand über den Hinweisen', 'daily-scripture' ), '.daily-scripture__meta', 'padding-top' ),
		);
		if ( $passage ) {
			unset( $spacing['sources'] );
			$spacing['verses'][1] = '.daily-scripture__text + .daily-scripture__text';
			$spacing['verses'][2] = 'margin-top';
		}
		return $spacing;
	}

	/**
	 * Scope a descendant selector to our component.
	 *
	 * @param string $child Descendant selector, or empty for component root.
	 * @return string
	 */
	public static function selector( string $child = '' ): string {
		return '.daily-scripture[data-ds-component]' . ( '' !== $child ? ' ' . $child : '' );
	}
}
