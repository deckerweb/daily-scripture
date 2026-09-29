<?php
/**
 * Independent local Bible passages with shared presentation and editor previews.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Bible;

use Deckerweb\DailyScripture\Core\{Presentation, ReferenceLink};
defined( 'ABSPATH' ) || exit;

/** A separate block/shortcode; never a replacement provider for official daily texts. */
final class Passage {
	/** Register frontend shortcode and dynamic Gutenberg block. @return void */
	public function register(): void {
		add_shortcode( 'daily_scripture_passage', array( $this, 'render' ) );
		add_action( 'init', array( $this, 'block' ) );
	}
	/** Register the editor-only script and REST-validated block attributes. @return void */
	public function block(): void {
		wp_register_script( 'daily-scripture-passage', DAILY_SCRIPTURE_URL . 'assets/passage-block.js', array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ), DAILY_SCRIPTURE_VERSION, true );
		wp_set_script_translations( 'daily-scripture-passage', 'daily-scripture', dirname( DAILY_SCRIPTURE_FILE ) . '/languages' );
		wp_localize_script(
			'daily-scripture-passage',
			'dailyScripturePassage',
			array(
				'books'    => TranslationManager::books(),
				'editions' => array_map( static fn( $edition ) => $edition['label'], ( new TranslationManager() )->bundled() ),
				'layouts'  => Presentation::choices()['layout'],
			)
		);
		$attributes = array();
		foreach ( array(
			'translation' => 'luther-1912',
			'book'        => 'JOH',
			'title'       => '',
			'layout'      => '',
			'theme'       => '',
			'density'     => '',
		) as $key => $default ) {
			$attributes[ $key ] = array(
				'type'    => 'string',
				'default' => $default,
			); }
		foreach ( array(
			'chapter' => 3,
			'from'    => 16,
			'to'      => 16,
		) as $key => $default ) {
			$attributes[ $key ] = array(
				'type'    => 'integer',
				'default' => $default,
				'minimum' => 1,
				'maximum' => 176,
			); }
		register_block_type(
			'daily-scripture/passage',
			array(
				'api_version'     => 3,
				'editor_script'   => 'daily-scripture-passage',
				'attributes'      => $attributes,
				'render_callback' => array( $this, 'render' ),
				'supports'        => array( 'html' => false ),
			)
		);
	}
	/**
	 * Render one local range using the established semantic component markup.
	 *
	 * @param mixed $attributes Shortcode or block attributes.
	 * @return string Escaped component output.
	 * @throws \RuntimeException Caught locally and converted to a display notice.
	 */
	public function render( $attributes = array() ): string {
		$attributes = is_array( $attributes ) ? $attributes : array();
		$defaults   = array(
			'translation' => 'luther-1912',
			'book'        => 'JOH',
			'chapter'     => 3,
			'from'        => 16,
			'to'          => 16,
			'title'       => '',
			'layout'      => '',
			'theme'       => '',
			'density'     => '',
		);
		$args       = array_intersect_key( array_merge( $defaults, $attributes ), $defaults );
		if ( ! array_key_exists( 'to', $attributes ) ) {
			$args['to'] = $args['from']; }
		try {
			foreach ( $args as $value ) {
				if ( ! is_scalar( $value ) ) {
					throw new \RuntimeException( esc_html__( 'Ungültige Bibelstellen-Einstellung.', 'daily-scripture' ) ); }
			}
			foreach ( array( 'chapter', 'from', 'to' ) as $key ) {
				if ( ! preg_match( '/^[1-9]\d{0,2}$/D', (string) $args[ $key ] ) ) {
					throw new \RuntimeException( esc_html__( 'Kapitel und Verse müssen positive ganze Zahlen sein.', 'daily-scripture' ) ); }
			}
			$edition   = TranslationManager::edition( (string) $args['translation'] );
			$book      = strtoupper( (string) $args['book'] );
			$verses    = ( new Library() )->passage( (string) $args['translation'], $book, (int) $args['chapter'], (int) $args['from'], (int) $args['to'] );
			$reference = TranslationManager::books()[ $book ] . ' ' . $args['chapter'] . ',' . $args['from'] . ( (int) $args['from'] !== (int) $args['to'] ? '–' . $args['to'] : '' );
			$title     = Presentation::sanitize_heading( (string) $args['title'] );
			$title     = '' === $title ? $reference : $title;
			$style     = Presentation::resolve( $args, false );
			$html      = '<div class="' . esc_attr( $style['classes'] ) . '" style="' . esc_attr( $style['style'] ) . '" data-ds-component="1" data-source="passage"><section class="daily-scripture__source"><header class="daily-scripture__header"><h3 class="daily-scripture__title">' . esc_html( $title ) . '</h3></header><div class="daily-scripture__verses"><blockquote class="daily-scripture__item">';
			foreach ( $verses as $number => $text ) {
				$html .= '<p class="daily-scripture__text"><sup>' . esc_html( (string) $number ) . '</sup> ' . esc_html( $text ) . '</p>'; }
			$html .= '<cite class="daily-scripture__reference"><a href="' . esc_url( ReferenceLink::url( $reference ) ) . '">' . esc_html( $reference ) . '</a></cite></blockquote></div><footer class="daily-scripture__meta"><p>' . esc_html( $edition['label'] . ' · ' . TranslationManager::license_label( $edition ) ) . '</p><p><a href="' . esc_url( $edition['source'] ) . '">' . esc_html__( 'Textquelle & Ausgabe', 'daily-scripture' ) . '</a></p>' . TranslationManager::attribution_html( $edition ) . '</footer></section></div>';
			return $html;
		} catch ( \RuntimeException $error ) {
			return '<div class="daily-scripture-passage-notice" role="status"><p>' . esc_html( $error->getMessage() ) . '</p></div>';
		}
	}
}
