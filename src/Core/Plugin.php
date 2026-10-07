<?php
/**
 * Connect the source core to WordPress integrations.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

use Deckerweb\DailyScripture\Admin\Admin;
use Deckerweb\DailyScripture\Integrations\Gutenberg\GutenbergIntegration;
use Deckerweb\DailyScripture\Integrations\Bricks\BricksIntegration;
use Deckerweb\DailyScripture\Integrations\Elementor\ElementorIntegration;

defined( 'ABSPATH' ) || exit;

/** Connect the source core to WordPress integrations. */
final class Plugin {
	/**
	 * Register plugin services and presentation hooks.
	 *
	 * @return void
	 */
	public function boot(): void {
		load_plugin_textdomain( 'daily-scripture', false, dirname( plugin_basename( DAILY_SCRIPTURE_FILE ) ) . '/languages' );
		( new Admin() )->register();
		( new \Deckerweb\DailyScripture\Bible\Passage() )->register();
		( new Lifecycle() )->register();
		( new GitHubUpdates() )->register();
		( new SiteHealth() )->register();
		$this->register_shortcode();
		$this->register_dashboard_widget();
		( new GutenbergIntegration() )->register();
		( new BricksIntegration() )->register();
		( new ElementorIntegration() )->register();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'styles' ) );
		add_action( 'enqueue_block_assets', array( $this, 'editor_styles' ) );
	}

	/**
	 * Register the shared shortcode renderer.
	 *
	 * @return void
	 */
	private function register_shortcode(): void {
		add_shortcode(
			'daily_scripture',
			/**
			 * Resolve shortcode attributes and return escaped shared verse output.
			 *
			 * @param array|string $atts WordPress shortcode attributes.
			 * @return string
			 */
			static function ( $atts ) {
				$atts = shortcode_atts(
					array(
						'source'  => '',
						'layout'  => '',
						'density' => '',
						'theme'   => '',
					),
					$atts,
					'daily_scripture'
				);
				return ( new Renderer() )->render( sanitize_key( $atts['source'] ), null, $atts );
			}
		);
	}

	/**
	 * Register the optional dashboard view.
	 *
	 * @return void
	 */
	private function register_dashboard_widget(): void {
		( new \Deckerweb\DailyScripture\Admin\DashboardWidget() )->register();
	}

	/**
	 * Load presentation assets only on plugin screens and the enabled dashboard.
	 *
	 * @param string $hook Current admin screen hook.
	 * @return void
	 */
	public function admin_styles( string $hook ): void {
		if ( str_contains( $hook, 'daily-scripture' ) || ( 'index.php' === $hook && \Deckerweb\DailyScripture\Admin\DashboardWidget::enabled() ) ) {
			$this->styles();
		}
	}

	/**
	 * Supply shared CSS to the block editor iframe without loading it on every page.
	 *
	 * @return void
	 */
	public function editor_styles(): void {
		if ( is_admin() ) {
			$this->styles();
		}
	}

	/**
	 * Enqueue shared typography and the progressively enhanced license dialog.
	 *
	 * @return void
	 */
	public function styles(): void {
		wp_enqueue_script( 'daily-scripture-license-dialog', DAILY_SCRIPTURE_URL . 'assets/license-dialog.js', array(), DAILY_SCRIPTURE_VERSION, true );
		wp_enqueue_style( 'daily-scripture', DAILY_SCRIPTURE_URL . 'assets/css/daily-scripture.css', array(), DAILY_SCRIPTURE_VERSION );
	}
}
