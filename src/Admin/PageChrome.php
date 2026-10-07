<?php
/**
 * Shared identity, navigation and credits for plugin administration.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

defined( 'ABSPATH' ) || exit;

/** Keep every plugin screen within one consistent visual shell. */
final class PageChrome {
	/**
	 * Enqueue shared styles only for the five plugin screens.
	 *
	 * @param string $hook Current screen hook.
	 * @return void
	 */
	public function assets( string $hook ): void {
		$hooks = array( 'toplevel_page_daily-scripture' );
		foreach ( array( 'data', 'bibles', 'shortcodes', 'help' ) as $page ) {
			$hooks[] = 'daily-scripture_page_daily-scripture-' . $page;
		}
		if ( in_array( $hook, $hooks, true ) ) {
			wp_enqueue_script( 'daily-scripture-changelog', DAILY_SCRIPTURE_URL . 'assets/changelog.js', array(), DAILY_SCRIPTURE_VERSION, true );
			wp_enqueue_style( 'daily-scripture-admin', DAILY_SCRIPTURE_URL . 'assets/css/admin.css', array(), DAILY_SCRIPTURE_VERSION );
		}
	}

	/**
	 * Render the same branded header with a unique page title and active navigation.
	 *
	 * @param string $page Current plugin page slug.
	 * @param string $title Visible page title.
	 * @param string $intro Introductory copy.
	 * @return void
	 */
	public static function header( string $page, string $title, string $intro ): void {
		$pages = array(
			'daily-scripture'            => __( 'Design', 'daily-scripture' ),
			'daily-scripture-data'       => __( 'Data sources', 'daily-scripture' ),
			'daily-scripture-bibles'     => __( 'Bible library', 'daily-scripture' ),
			'daily-scripture-shortcodes' => __( 'Shortcodes', 'daily-scripture' ),
			'daily-scripture-help'       => __( 'Help', 'daily-scripture' ),
		);
		echo '<header class="ds-admin-header"><img class="ds-admin-logo" src="' . esc_url( DAILY_SCRIPTURE_URL . 'assets/brand/icon.svg' ) . '" width="72" height="72" alt=""><div><p class="ds-admin-eyebrow">Daily Scripture <span> / ' . esc_html( $pages[ $page ] ?? '' ) . '</span></p><h1>' . esc_html( $title ) . '</h1><p class="ds-admin-intro">' . esc_html( $intro ) . '</p></div></header><hr class="wp-header-end">';
		echo '<nav class="ds-admin-nav" aria-label="' . esc_attr__( 'Daily Scripture – sections', 'daily-scripture' ) . '">';
		foreach ( $pages as $slug => $label ) {
			echo '<a href="' . esc_url( admin_url( 'admin.php?page=' . $slug ) ) . '"' . ( $slug === $page ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>';
		}
		echo '</nav>';
	}

	/**
	 * Render plugin credits, clearly separate from Bible text attribution.
	 *
	 * @return void
	 */
	public static function footer(): void {
		echo '<footer class="ds-admin-footer" aria-label="' . esc_attr__( 'About the plugin', 'daily-scripture' ) . '"><div><strong>Daily Scripture</strong> <span>' . esc_html__( 'Version', 'daily-scripture' ) . ' ' . esc_html( DAILY_SCRIPTURE_VERSION ) . '</span> · <a href="' . esc_url( Changelog::url() ) . '" data-ds-changelog>' . esc_html__( 'Changelog', 'daily-scripture' ) . '</a> · <a href="' . esc_url( 'https://github.com/deckerweb/daily-scripture/wiki/' . ( Changelog::is_german() ? 'Deutsch' : 'English' ) ) . '">' . esc_html__( 'Documentation', 'daily-scripture' ) . '</a><p>' . esc_html__( 'Words to brighten your day.', 'daily-scripture' ) . '</p></div><div><span>© ' . esc_html( wp_date( 'Y' ) ) . ' <a href="https://github.com/deckerweb">David Decker · deckerweb</a></span><a href="https://github.com/deckerweb/daily-scripture">' . esc_html__( 'Plugin website', 'daily-scripture' ) . '</a></div></footer>';
		Changelog::dialog();
	}
}
