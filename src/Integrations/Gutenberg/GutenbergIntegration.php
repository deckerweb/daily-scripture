<?php
/**
 * Dynamic block with native InspectorControls and shared server rendering.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Integrations\Gutenberg;

use Deckerweb\DailyScripture\Core\Renderer;
use Deckerweb\DailyScripture\Core\Presentation;
use Deckerweb\DailyScripture\Core\Settings;

defined( 'ABSPATH' ) || exit;

/** Register frontend-identical editor previews and per-block settings. */
final class GutenbergIntegration {
	/**
	 * Register block metadata and refresh site defaults when opening the editor.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_defaults' ) );
	}

	/**
	 * Register attributes identically on server and client, retaining old blocks.
	 *
	 * @return void
	 */
	public function block(): void {
		wp_register_script( 'daily-scripture-block', DAILY_SCRIPTURE_URL . 'assets/block.js', array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor', 'wp-server-side-render' ), DAILY_SCRIPTURE_VERSION, true );
		wp_set_script_translations( 'daily-scripture-block', 'daily-scripture', DAILY_SCRIPTURE_DIR . 'languages' );
		$attributes = array();
		foreach ( array( 'source', 'layout', 'density', 'theme', 'title_herrnhuter', 'title_bible2' ) as $key ) {
			$attributes[ $key ] = array(
				'type'    => 'string',
				'default' => '',
			);
		}
		register_block_type(
			'daily-scripture/today',
			array(
				'api_version'     => 3,
				'editor_script'   => 'daily-scripture-block',
				'render_callback' => static fn( $attributes ) => ( new Renderer() )->render( sanitize_key( $attributes['source'] ?? '' ), null, $attributes, false ),
				'attributes'      => $attributes,
				'supports'        => array( 'html' => false ),
			)
		);
	}

	/**
	 * Supply placeholders, not stored block values, so empty overrides inherit.
	 *
	 * @return void
	 */
	public function editor_defaults(): void {
		wp_localize_script(
			'daily-scripture-block',
			'dailyScriptureBlock',
			array(
				'source'           => Settings::get( 'default_source', 'herrnhuter' ),
				'title_herrnhuter' => Presentation::heading( 'herrnhuter' ),
				'title_bible2'     => Presentation::heading( 'bible2' ),
				'choices'          => Presentation::choices(),
			)
		);
	}
}
