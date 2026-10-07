<?php
/**
 * Personal, space-conscious dashboard presentation.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

use Deckerweb\DailyScripture\Core\{Settings, Renderer};
use Deckerweb\DailyScripture\Bible\{Passage, Library};

defined( 'ABSPATH' ) || exit;

/** Personal site and network dashboards with separate preferences. */
final class DashboardWidget {
	/** User-option key; WordPress adds the current website prefix. */
	private const OPTION = 'daily_scripture_dashboard';

	/** Register widget, scoped assets and authenticated form handler. @return void */
	public function register(): void {
		add_action( 'wp_dashboard_setup', array( $this, 'setup' ) );
		add_action( 'wp_network_dashboard_setup', array( $this, 'setup_network' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_post_daily_scripture_dashboard', array( $this, 'save' ) );
	}

	/** Register only when the website administrator enabled the widget. @return void */
	public function setup(): void {
		if ( self::enabled() && current_user_can( 'read' ) ) {
			wp_add_dashboard_widget( 'daily_scripture_widget', __( 'Daily Scripture', 'daily-scripture' ), array( $this, 'render' ) );
		}
	}

	/**
	 * Read the website widget switch; the network widget is independently available.
	 *
	 * @return bool Whether this dashboard should include the widget.
	 */
	public static function enabled(): bool {
		if ( is_network_admin() ) {
			return current_user_can( 'manage_network' );
		}
		return (bool) Settings::get( 'dashboard_widget', 1 );
	}

	/**
	 * Validate network membership and native plugin activation.
	 *
	 * @param int $id Candidate website.
	 * @return bool Whether readings may be used in this network.
	 */
	public static function eligible_site( int $id ): bool {
		$site = get_site( $id );
		if ( ! $site || get_current_network_id() !== (int) $site->network_id || $site->deleted || $site->archived || $site->spam ) {
			return false;
		}
		$plugin  = plugin_basename( DAILY_SCRIPTURE_FILE );
		$network = get_network_option( get_current_network_id(), 'active_sitewide_plugins', array() );
		$active  = isset( $network[ $plugin ] ) ? array() : get_blog_option( $id, 'active_plugins', array() );
		return isset( $network[ $plugin ] ) || ( is_array( $active ) && in_array( $plugin, $active, true ) );
	}

	/**
	 * Fetch one bounded network page, never the entire network inventory.
	 *
	 * @return array Eligible site IDs on the requested page.
	 */
	public static function site_choices(): array {
		$page = isset( $_GET['ds_site_page'] ) && is_scalar( $_GET['ds_site_page'] ) ? min( 100000, max( 1, absint( $_GET['ds_site_page'] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only inventory pagination.
		$ids  = get_sites(
			array(
				'network_id' => get_current_network_id(),
				'number'     => 50,
				'offset'     => ( $page - 1 ) * 50,
				'fields'     => 'ids',
				'deleted'    => 0,
				'archived'   => 0,
				'spam'       => 0,
				'orderby'    => 'id',
				'order'      => 'ASC',
			)
		);
		return array_values( array_filter( $ids, array( self::class, 'eligible_site' ) ) );
	}

	/**
	 * Prefer a valid personal selection, then the main site or first active site.
	 *
	 * @return int Valid source site, or zero when this page has none.
	 */
	public static function source_site(): int {
		$raw = get_user_meta( get_current_user_id(), self::network_option(), true );
		$id  = is_array( $raw ) && is_scalar( $raw['site'] ?? null ) ? absint( $raw['site'] ) : 0;
		if ( $id && self::eligible_site( $id ) ) {
			return $id;
		}
		$main = get_main_site_id();
		if ( self::eligible_site( $main ) ) {
			return $main;
		}
		return (int) ( self::site_choices()[0] ?? 0 );
	}

	/** Register the personal reference-site widget for network administrators. @return void */
	public function setup_network(): void {
		if ( self::enabled() && current_user_can( 'manage_network' ) ) {
			wp_add_dashboard_widget( 'daily_scripture_widget', __( 'Daily Scripture', 'daily-scripture' ), array( $this, 'render_network' ) );
		}
	}

	/**
	 * Use the chosen website’s data and timezone, restoring the caller on errors.
	 *
	 * @return void
	 */
	public function render_network(): void {
		if ( ! current_user_can( 'manage_network' ) ) {
			return;
		}
		$site = self::source_site();
		if ( ! $site ) {
			echo '<p>' . esc_html__( 'No enabled website is available on this page.', 'daily-scripture' ) . '</p>';
			$this->site_pages();
			return;
		}
		switch_to_blog( $site );
		try {
			$this->render_content( true );
		} finally {
			restore_current_blog();
		}
	}

	/**
	 * Identify this user's display preferences for the current network.
	 *
	 * @return string Network-specific user-meta key.
	 */
	private static function network_option(): string {
		return 'daily_scripture_network_dashboard_' . get_current_network_id();
	}

	/**
	 * Load dashboard-only adjustments without affecting any frontend output.
	 *
	 * @param string $hook Current admin screen.
	 * @return void
	 */
	public function assets( string $hook ): void {
		if ( 'index.php' === $hook && self::enabled() ) {
			wp_enqueue_script( 'daily-scripture-dashboard', DAILY_SCRIPTURE_URL . 'assets/dashboard.js', array(), DAILY_SCRIPTURE_VERSION, true );
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
			$this->render_content( false );
	}

	/**
	 * Render a personal dashboard using the selected site's local data.
	 *
	 * @param bool $network Whether to use separate network preferences.
	 * @return void
	 */
	private function render_content( bool $network ): void {
		$raw           = $network ? get_user_meta( get_current_user_id(), self::network_option(), true ) : get_user_option( self::OPTION );
		$preferences   = self::sanitize( $raw );
		$definitions   = DashboardReadings::all();
		$reading_input = $raw;
		if ( $network && is_array( $raw ) && isset( $raw['site'] ) && get_current_blog_id() !== (int) $raw['site'] ) {
			unset( $reading_input['readings'] );
		}
		$selection = self::selection( $reading_input, $definitions );
		if ( $network ) {
			/* translators: %s: website name supplying the widget readings. */
			echo '<p class="ds-dashboard-context">' . esc_html( sprintf( __( 'Readings from: %s', 'daily-scripture' ), get_bloginfo( 'name' ) ) ) . '</p>';
			if ( is_array( $raw ) && ! empty( $raw['site'] ) && get_current_blog_id() !== (int) $raw['site'] ) {
				echo '<p>' . esc_html__( 'Your previous website is unavailable. A currently active website is shown instead.', 'daily-scripture' ) . '</p>';
			}
		}
		echo '<div class="ds-dashboard ds-dashboard--' . esc_attr( $preferences['spacing'] ) . '">';
		if ( ! $definitions ) {
			echo '<p class="ds-dashboard-empty">' . esc_html__( 'No dashboard readings have been configured yet.', 'daily-scripture' ) . '</p>';
		} elseif ( ! $selection ) {
			echo '<p class="ds-dashboard-empty">' . esc_html__( 'Choose the readings you would like to see in Display options.', 'daily-scripture' ) . '</p>';
		}
		foreach ( $selection as $id ) {
			$row = $definitions[ $id ];
			if ( in_array( $id, array( 'herrnhuter', 'bible2' ), true ) ) {
				echo ( new Renderer() )->render( $id, null, self::display( $preferences ), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared renderer escapes output and preserves complete pairs.
			} else {
				echo ( new Passage() )->render( array_merge( $row, self::display( $preferences ) ), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared passage renderer escapes text and rights notices.
			}
		}

		echo '<div class="ds-dashboard-brand"><img src="' . esc_url( DAILY_SCRIPTURE_URL . 'assets/brand/icon.svg' ) . '" width="24" height="24" alt=""><span>Daily Scripture</span>';
		if ( current_user_can( 'manage_options' ) ) {
			echo '<a href="' . esc_url( admin_url( 'admin.php?page=daily-scripture' ) ) . '">' . esc_html__( 'Plugin settings', 'daily-scripture' ) . '</a>';
		}
		$help = $network ? __( 'These choices apply only to you in this network dashboard; site dashboards and public output stay unchanged.', 'daily-scripture' ) : __( 'Your dashboard, your reading size. These choices apply only to you on this site; public verse output stays unchanged.', 'daily-scripture' );
		echo '</div><details class="ds-dashboard-controls"><summary>' . esc_html__( 'Display options', 'daily-scripture' ) . '</summary><p>' . esc_html( $help ) . '</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="daily_scripture_dashboard">';
		echo '<input type="hidden" name="dashboard_context" value="' . ( $network ? 'network' : 'site' ) . '">';
		if ( $network ) {
			$this->site_control();
		}
		$this->reading_controls( $definitions, $selection );
		wp_nonce_field( $network ? 'daily_scripture_network_dashboard_' . get_current_network_id() : 'daily_scripture_dashboard' );
		$fields = array(
			'size'    => array(
				__( 'Font size', 'daily-scripture' ),
				array(
					'small'  => __( 'Very compact · 14 px verses', 'daily-scripture' ),
					'medium' => __( 'Compact · 16 px verses (recommended)', 'daily-scripture' ),
					'large'  => __( 'Comfortable reading · 18 px verses', 'daily-scripture' ),
				),
			),
			'spacing' => array(
				__( 'Spacing', 'daily-scripture' ),
				array(
					'tight'   => __( 'Less space', 'daily-scripture' ),
					'relaxed' => __( 'More space', 'daily-scripture' ),
				),
			),
			'layout'  => array(
				__( 'Presentation', 'daily-scripture' ),
				array(
					'minimal' => __( 'Plain, without borders', 'daily-scripture' ),
					'card'    => __( 'Subtle cards', 'daily-scripture' ),
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
		echo '<p><button class="button button-primary" type="submit">' . esc_html__( 'Save my display options', 'daily-scripture' ) . '</button></p></form></details></div>';
	}

	/**
	 * Filter and order personal choices against this site's configured IDs.
	 *
	 * @param mixed $input Preferences, including optional selected IDs and order.
	 * @param array $definitions Configured website definitions.
	 * @return array Unique selected IDs in display order.
	 */
	public static function selection( $input, array $definitions ): array {
		if ( ! is_array( $input ) || ! array_key_exists( 'readings', $input ) ) {
			return array_slice( array_keys( $definitions ), 0, 1 );
		}
		$chosen = array();
		foreach ( array_slice( is_array( $input['readings'] ) ? $input['readings'] : array(), 0, 9 ) as $id ) {
			if ( is_string( $id ) && isset( $definitions[ $id ] ) && ! isset( $chosen[ $id ] ) ) {
				$order         = $input['order'][ $id ] ?? null;
				$chosen[ $id ] = is_scalar( $order ) && preg_match( '/^[1-8]$/D', (string) $order ) ? (int) $order : count( $chosen ) + 1;
			}
		}
		asort( $chosen, SORT_NUMERIC );
		return array_keys( $chosen );
	}

	/**
	 * Show accessible selections with a numeric no-JavaScript ordering fallback.
	 *
	 * @param array $definitions Website-owned reading definitions.
	 * @param array $selection Personal order.
	 * @return void
	 */
	private function reading_controls( array $definitions, array $selection ): void {
		echo '<fieldset class="ds-dashboard-readings"><legend>' . esc_html__( 'My readings', 'daily-scripture' ) . '</legend><input type="hidden" name="preferences[readings][]" value="">';
		$ids = array_unique( array_merge( $selection, array_keys( $definitions ) ) );
		$i   = 0;
		foreach ( $ids as $id ) {
			++$i;
			$label = DashboardReadings::label( $id, $definitions[ $id ] );
			echo '<div class="ds-dashboard-reading"><label><input type="checkbox" name="preferences[readings][]" value="' . esc_attr( $id ) . '" ' . checked( in_array( $id, $selection, true ), true, false ) . '> ' . esc_html( $label ) . '</label><label class="ds-reading-order">' . esc_html__( 'Order', 'daily-scripture' ) . '<input type="number" min="1" max="8" name="preferences[order][' . esc_attr( $id ) . ']" value="' . esc_attr( (string) $i ) . '"></label><button type="button" class="button ds-reading-up" aria-label="' . esc_attr( $label . ': ' . __( 'Move up', 'daily-scripture' ) ) . '" hidden>' . esc_html__( 'Move up', 'daily-scripture' ) . '</button><button type="button" class="button ds-reading-down" aria-label="' . esc_attr( $label . ': ' . __( 'Move down', 'daily-scripture' ) ) . '" hidden>' . esc_html__( 'Move down', 'daily-scripture' ) . '</button></div>';
		}
		echo '<p class="ds-reading-status screen-reader-text" role="status" aria-live="polite"></p></fieldset>';
	}

	/** Show a bounded website page and pagination for large networks. @return void */
	private function site_control(): void {
		$ids = self::site_choices();
		if ( ! in_array( get_current_blog_id(), $ids, true ) ) {
			array_unshift( $ids, get_current_blog_id() );
		}
		echo '<p><label for="ds-reading-site">' . esc_html__( 'Website supplying the readings', 'daily-scripture' ) . '</label><select id="ds-reading-site" name="preferences[site]">';
		foreach ( $ids as $id ) {
			echo '<option value="' . esc_attr( (string) $id ) . '" ' . selected( get_current_blog_id(), $id, false ) . '>' . esc_html( get_blog_option( $id, 'blogname', '' ) . ' · ' . get_site_url( $id ) ) . '</option>';
		}
		echo '</select><input type="hidden" name="preferences[reading_site]" value="' . esc_attr( (string) get_current_blog_id() ) . '"></p><p>' . esc_html__( 'After changing the website, save once to load its available readings, then choose your selection.', 'daily-scripture' ) . '</p>';
		$this->site_pages();
	}

	/** Render bounded read-only network inventory pagination. @return void */
	private function site_pages(): void {
		$page  = isset( $_GET['ds_site_page'] ) && is_scalar( $_GET['ds_site_page'] ) ? max( 1, absint( $_GET['ds_site_page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only pagination.
		$count = get_sites(
			array(
				'network_id' => get_current_network_id(),
				'count'      => true,
				'deleted'    => 0,
				'archived'   => 0,
				'spam'       => 0,
			)
		);
		if ( $count > 50 ) {
			echo '<p>';
			foreach ( array(
				$page - 1 => __( 'Previous websites', 'daily-scripture' ),
				$page + 1 => __( 'More websites', 'daily-scripture' ),
			) as $target => $label ) {
				if ( $target > 0 && ( $target - 1 ) * 50 < $count ) {
					echo '<a href="' . esc_url( add_query_arg( 'ds_site_page', $target, network_admin_url( 'index.php' ) ) . '#daily_scripture_widget' ) . '">' . esc_html( $label ) . '</a> ';
				}
			}
			echo '</p>';
		}
	}

	/** Save preferences for this authenticated user, never an arbitrary user ID. @return void */
	public function save(): void {
		// Missing context preserves compatibility with existing site-dashboard forms.
		$context = isset( $_POST['dashboard_context'] ) && is_string( $_POST['dashboard_context'] ) ? sanitize_key( wp_unslash( $_POST['dashboard_context'] ) ) : 'site';
		$network = 'network' === $context;
		if ( ! in_array( $context, array( 'site', 'network' ), true ) || ( $network && ! is_multisite() ) || ! current_user_can( $network ? 'manage_network' : 'read' ) ) {
			wp_die( esc_html__( 'You do not have permission.', 'daily-scripture' ), '', array( 'response' => 403 ) );
		}
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) ) {
			wp_die( esc_html__( 'Please use the dashboard form.', 'daily-scripture' ), '', array( 'response' => 405 ) );
		}
		check_admin_referer( $network ? 'daily_scripture_network_dashboard_' . get_current_network_id() : 'daily_scripture_dashboard' );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Strict enum validation immediately follows unslashing.
		$input = isset( $_POST['preferences'] ) && is_array( $_POST['preferences'] ) ? wp_unslash( $_POST['preferences'] ) : array();
		if ( $network ) {

			$site = is_scalar( $input['site'] ?? null ) ? absint( $input['site'] ) : 0;
			if ( ! self::eligible_site( $site ) ) {
				wp_die( esc_html__( 'This website is not available in the current network.', 'daily-scripture' ), '', array( 'response' => 400 ) );
			}
			switch_to_blog( $site );
			try {
				$definitions = DashboardReadings::all();
				if ( ( is_scalar( $input['reading_site'] ?? null ) ? absint( $input['reading_site'] ) : 0 ) !== $site ) {
					unset( $input['readings'] );
				}
				$clean = array_merge(
					self::sanitize( $input ),
					array(
						'site'     => $site,
						'readings' => self::selection( $input, $definitions ),
					)
				);
			} finally {
				restore_current_blog();
			}
			update_user_meta( get_current_user_id(), self::network_option(), $clean );
		} else {
			update_user_option( get_current_user_id(), self::OPTION, array_merge( self::sanitize( $input ), array( 'readings' => self::selection( $input, DashboardReadings::all() ) ) ), false );
		}
		wp_safe_redirect( $network ? network_admin_url( 'index.php#daily_scripture_widget' ) : admin_url( 'index.php#daily_scripture_widget' ) );
		exit;
	}
}
