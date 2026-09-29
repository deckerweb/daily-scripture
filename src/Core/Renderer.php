<?php
/**
 * Identical output for shortcode, Gutenberg and dashboard.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

defined( 'ABSPATH' ) || exit;

/** Renders source texts verbatim as escaped text and always as complete pairs. */
final class Renderer {
	/**
	 * Render a day's local source records.
	 *
	 * @param string                  $source Source identifier or both.
	 * @param \DateTimeInterface|null $date Date; defaults to the WordPress timezone.
	 * @param array                   $display Optional layout, density and theme overrides.
	 * @param bool|null               $admin Explicit context; Gutenberg always uses frontend presentation.
	 * @return string Escaped HTML.
	 */
	public function render( string $source = '', ?\DateTimeInterface $date = null, array $display = array(), ?bool $admin = null ): string {
		$source = '' !== $source ? $source : (string) Settings::get( 'default_source', 'herrnhuter' );
		if ( ! in_array( $source, array( 'herrnhuter', 'bible2', 'both' ), true ) ) {
			$source = 'herrnhuter';
		}
		return $this->render_sets( ( new DataManager() )->get( $source, $date ), $display, $admin, $source );
	}

	/**
	 * Render validated provider envelopes with the same markup in previews.
	 *
	 * @param array     $sets Validated source envelopes or clearly labelled preview samples.
	 * @param array     $display Unsaved or per-instance presentation settings.
	 * @param bool|null $admin Optional explicit display context.
	 * @param string    $source Wrapper source identifier.
	 * @return string Escaped HTML.
	 */
	public function render_sets( array $sets, array $display = array(), ?bool $admin = null, string $source = 'preview' ): string {
		$presentation = Presentation::resolve( $display, $admin );
		ob_start();
		echo '<div class="' . esc_attr( $presentation['classes'] ) . '" style="' . esc_attr( $presentation['style'] ) . '" data-ds-component="1" data-source="' . esc_attr( $source ) . '">';
		foreach ( $sets as $set ) {
			$day = new \DateTimeImmutable( $set['date'] . ' 12:00:00', wp_timezone() );
			echo '<section class="daily-scripture__source"><header class="daily-scripture__header"><h3 class="daily-scripture__title">' . esc_html( ! empty( $set['preview_sample'] ) ? $set['label'] : Presentation::heading( $set['source'], $display ) ) . '</h3>';
			echo '<time class="daily-scripture__date" datetime="' . esc_attr( $set['date'] ) . '">' . esc_html( wp_date( $presentation['format'], $day->getTimestamp(), wp_timezone() ) ) . '</time></header>';
			if ( 'ok' !== $set['status'] || 2 !== count( $set['items'] ) ) {
				echo '<p>' . esc_html__( 'Für dieses Datum sind keine freigegebenen lokalen Daten verfügbar.', 'daily-scripture' ) . '</p></section>';
				continue;
			}
			echo '<div class="daily-scripture__verses">';
			foreach ( $set['items'] as $item ) {
				echo '<blockquote class="daily-scripture__item">';
				if ( ! empty( $item['intro'] ) ) {
					echo '<p class="daily-scripture__text">' . esc_html( $item['intro'] ) . '</p>';
				}
				echo '<p class="daily-scripture__text">' . esc_html( $item['text'] ) . '</p>';
				echo '<cite class="daily-scripture__reference"><a href="' . esc_url( ReferenceLink::url( $item['reference'], (string) ( $display['bibleserver_translation'] ?? '' ) ) ) . '">' . esc_html( $item['reference'] ) . '</a></cite></blockquote>';
			}
			echo '</div><footer class="daily-scripture__meta">';
			if ( 'herrnhuter' === $set['source'] ) {
				echo '<p class="daily-scripture__copyright"><a href="https://www.herrnhuter.de/">© Evangelische Brüder-Unität – Herrnhuter Brüdergemeine</a><br><a href="https://www.losungen.de/">' . esc_html__( 'Weitere Informationen finden Sie hier.', 'daily-scripture' ) . '</a></p>';
			} else {
				echo '<p>' . esc_html( $set['edition'] ) . '</p>';
				// Keep all imported rights text accessible even without JavaScript.
				echo '<details class="daily-scripture__license" data-close-label="' . esc_attr__( 'Schließen', 'daily-scripture' ) . '"><summary>' . esc_html__( 'Copyright und Lizenzhinweise', 'daily-scripture' ) . '</summary>';
				echo '<p class="daily-scripture__copyright">' . esc_html( $set['copyright'] ) . '</p></details>';
				echo '<p><a href="https://bible2.net/">' . esc_html__( 'Zusammenstellung der Bibelstellen durch das Projekt „Bible 2.0“', 'daily-scripture' ) . '</a> · <a href="https://bible2.net/en/copyright">' . esc_html__( 'Lizenzinformationen', 'daily-scripture' ) . '</a></p>';
			}
			echo '</footer></section>';
		}
		echo '</div>';
		return (string) ob_get_clean();
	}
}
