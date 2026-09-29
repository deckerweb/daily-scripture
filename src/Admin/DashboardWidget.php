<?php
/**
 * Personal, space-conscious dashboard presentation.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

use Deckerweb\DailyScripture\Core\{Settings, Renderer};

defined( 'ABSPATH' ) || exit;

/** Dashboard preferences belong to the current user and website only. */
final class DashboardWidget {
	/** User-option key; WordPress adds the current website prefix. */
	private const OPTION = 'daily_scripture_dashboard';

	/** Register widget, scoped assets and authenticated form handler. @return void */
	public function register(): void {
		add_action( 'wp_dashboard_setup', array( $this, 'setup' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_post_daily_scripture_dashboard', array( $this, 'save' ) );
	}

	/** Register only when the website administrator enabled the widget. @return void */
	public function setup(): void {
		if ( Settings::get( 'dashboard_widget', 1 ) && current_user_can( 'read' ) ) {
			wp_add_dashboard_widget( 'daily_scripture_widget', __( 'Daily Scripture', 'daily-scripture' ), array( $this, 'render' ) );
		}
	}

	/**
	 * Load dashboard-only adjustments without affecting any frontend output.
	 *
	 * @param string $hook Current admin screen.
	 * @return void
	 */
	public function assets( string $hook ): void {
		if ( 'index.php' === $hook && Settings::get( 'dashboard_widget', 1 ) ) {
			wp_enqueue_style( 'daily-scripture-dashboard', DAILY_SCRIPTURE_URL . 'assets/css/dashboard.css', array( 'daily-scripture' ), DAILY_SCRIPTURE_VERSION );
		}
	}

	/**
	 * Strictly allow known preference values.
	 *
	 * @param mixed $input Candidate preferences.
	 * @return array
	 */
	public static function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : array();
		return array(
			'size'    => in_array( $input['size'] ?? '', array( 'small', 'medium', 'large' ), true ) ? $input['size'] : 'medium',
			'spacing' => in_array( $input['spacing'] ?? '', array( 'tight', 'relaxed' ), true ) ? $input['spacing'] : 'tight',
			'layout'  => in_array( $input['layout'] ?? '', array( 'minimal', 'card' ), true ) ? $input['layout'] : 'minimal',
		);
	}

	/**
	 * Small balanced typography independent of the website's expert settings.
	 *
	 * @param array $preferences Validated or candidate preferences.
	 * @return array Shared renderer overrides.
	 */
	public static function display( array $preferences ): array {
		$preferences = self::sanitize( $preferences );
		$sizes       = array(
			'small'  => array( 17, 14, 12, 12, 12 ),
			'medium' => array( 20, 16, 13, 12, 12 ),
			'large'  => array( 22, 18, 14, 13, 13 ),
		);
		$display     = array(
			'expert_mode' => 1,
			'type_scale'  => 100,
			'theme'       => 'light',
			'density'     => 'compact',
			'layout'      => $preferences['layout'],
		);
		foreach ( array( 'heading', 'verse', 'reference', 'meta', 'date' ) as $index => $part ) {
			$display[ 'font_' . $part ]  = $sizes[ $preferences['size'] ][ $index ] . 'px';
			$display[ 'color_' . $part ] = '';
		}
		return $display;
	}

	/** Render complete verse pairs, personal controls and a quiet brand link. @return void */
	public function render(): void {
		$preferences = self::sanitize( get_user_option( self::OPTION ) );
		echo '<div class="ds-dashboard ds-dashboard--' . esc_attr( $preferences['spacing'] ) . '">';
		echo ( new Renderer() )->render( '', null, self::display( $preferences ), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared renderer escapes all output.
		echo '<div class="ds-dashboard-brand"><img src="' . esc_url( DAILY_SCRIPTURE_URL . 'assets/brand/icon.svg' ) . '" width="24" height="24" alt=""><span>Daily Scripture</span>';
		if ( current_user_can( 'manage_options' ) ) {
			echo '<a href="' . esc_url( admin_url( 'admin.php?page=daily-scripture' ) ) . '">' . esc_html__( 'Plugin-Einstellungen', 'daily-scripture' ) . '</a>';
		}
		echo '</div><details class="ds-dashboard-controls"><summary>' . esc_html__( 'Ansicht anpassen', 'daily-scripture' ) . '</summary><p>' . esc_html__( 'Dein Dashboard, deine Lesegröße. Diese Auswahl gilt nur für dich auf dieser Website – die öffentliche Versausgabe bleibt unverändert.', 'daily-scripture' ) . '</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="daily_scripture_dashboard">';
		wp_nonce_field( 'daily_scripture_dashboard' );
		$fields = array(
			'size'    => array(
				__( 'Schriftgröße', 'daily-scripture' ),
				array(
					'small'  => __( 'Sehr kompakt · Verse 14 px', 'daily-scripture' ),
					'medium' => __( 'Kompakt · Verse 16 px (empfohlen)', 'daily-scripture' ),
					'large'  => __( 'Bequem lesen · Verse 18 px', 'daily-scripture' ),
				),
			),
			'spacing' => array(
				__( 'Abstände', 'daily-scripture' ),
				array(
					'tight'   => __( 'Wenig Freiraum', 'daily-scripture' ),
					'relaxed' => __( 'Etwas mehr Luft', 'daily-scripture' ),
				),
			),
			'layout'  => array(
				__( 'Darstellung', 'daily-scripture' ),
				array(
					'minimal' => __( 'Schlicht ohne Rahmen', 'daily-scripture' ),
					'card'    => __( 'Dezente Karten', 'daily-scripture' ),
				),
			),
		);
		foreach ( $fields as $key => $field ) {
			echo '<p><label for="ds-dashboard-' . esc_attr( $key ) . '">' . esc_html( $field[0] ) . '</label><select id="ds-dashboard-' . esc_attr( $key ) . '" name="preferences[' . esc_attr( $key ) . ']">';
			foreach ( $field[1] as $value => $label ) {
				echo '<option value="' . esc_attr( $value ) . '" ' . selected( $preferences[ $key ], $value, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select></p>';
		}
		echo '<p><button class="button button-primary" type="submit">' . esc_html__( 'Meine Ansicht speichern', 'daily-scripture' ) . '</button></p></form></details></div>';
	}

	/** Save preferences for this authenticated user, never an arbitrary user ID. @return void */
	public function save(): void {
		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'daily-scripture' ), '', array( 'response' => 403 ) );
		}
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) ) {
			wp_die( esc_html__( 'Bitte das Formular im Dashboard verwenden.', 'daily-scripture' ), '', array( 'response' => 405 ) );
		}
		check_admin_referer( 'daily_scripture_dashboard' );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Strict enum validation immediately follows unslashing.
		$input = isset( $_POST['preferences'] ) && is_array( $_POST['preferences'] ) ? wp_unslash( $_POST['preferences'] ) : array();
		update_user_option( get_current_user_id(), self::OPTION, self::sanitize( $input ), false );
		wp_safe_redirect( admin_url( 'index.php#daily_scripture_widget' ) );
		exit;
	}
}
