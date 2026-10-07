<?php
/**
 * Capability-checked administration and POST-only data actions.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

use Deckerweb\DailyScripture\Core\Settings;
use Deckerweb\DailyScripture\Core\Presentation;
use Deckerweb\DailyScripture\Core\Lifecycle;
use Deckerweb\DailyScripture\Core\ReferenceLink;
use Deckerweb\DailyScripture\Data\Importer;
use Deckerweb\DailyScripture\Data\YearStore;
use Deckerweb\DailyScripture\Data\YearValidator;

defined( 'ABSPATH' ) || exit;

/** Settings, import forms and annual inventory. */
final class Admin {
	/**
	 * Register administrative hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		( new DesignStudio() )->register();
		add_filter( 'plugin_action_links_' . plugin_basename( DAILY_SCRIPTURE_FILE ), array( $this, 'action_links' ) );
		add_action( 'admin_enqueue_scripts', array( new PageChrome(), 'assets' ) );
		( new DataDownloads() )->register();
		add_action( 'admin_enqueue_scripts', array( new Documentation(), 'assets' ) );
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
		add_action( 'admin_post_daily_scripture_import', array( $this, 'import' ) );
		add_action( 'admin_post_daily_scripture_delete', array( $this, 'delete' ) );
	}

	/**
	 * Link the installed-plugins screen to the main configuration page.
	 *
	 * @param array $links Existing plugin actions.
	 * @return array
	 */
	public function action_links( array $links ): array {
		if ( current_user_can( 'manage_options' ) ) {
			array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=daily-scripture' ) ) . '">' . esc_html__( 'Settings', 'daily-scripture' ) . '</a>' );
		}
		return $links;
	}

	/**
	 * Require the same capability for every page and mutation.
	 *
	 * @return void
	 */
	private function authorize(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'daily-scripture' ), '', array( 'response' => 403 ) );
		}
	}

	/**
	 * Register menu pages.
	 *
	 * @return void
	 */
	public function menu(): void {
		add_menu_page( 'Daily Scripture', 'Daily Scripture', 'manage_options', 'daily-scripture', array( $this, 'settings_page' ), 'dashicons-book-alt', 58 );
		add_submenu_page( 'daily-scripture', __( 'Data sources', 'daily-scripture' ), __( 'Data sources', 'daily-scripture' ), 'manage_options', 'daily-scripture-data', array( $this, 'data_page' ) );
		add_submenu_page( 'daily-scripture', __( 'Shortcodes', 'daily-scripture' ), __( 'Shortcodes', 'daily-scripture' ), 'manage_options', 'daily-scripture-shortcodes', array( new Documentation(), 'shortcodes' ) );
		add_submenu_page( 'daily-scripture', __( 'Help', 'daily-scripture' ), __( 'Help', 'daily-scripture' ), 'manage_options', 'daily-scripture-help', array( $this, 'help_page' ) );
	}

	/**
	 * Register the WordPress settings API sanitizer.
	 *
	 * @return void
	 */
	public function settings(): void {
		register_setting(
			'daily_scripture',
			DashboardReadings::OPTION,
			array(
				'sanitize_callback' => array( DashboardReadings::class, 'save_configuration' ),
				'type'              => 'array',
			)
		);
		register_setting(
			'daily_scripture',
			'daily_scripture_settings',
			array(
				'sanitize_callback' => array( $this, 'sanitize' ),
				'type'              => 'array',
			)
		);
	}

	/**
	 * Sanitize settings without ever applying text sanitizers to imported verses.
	 *
	 * @param mixed $input Submitted settings.
	 * @return array
	 */
	public function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : array();
		$code  = is_string( $input['bibleserver_translation'] ?? null ) ? strtoupper( $input['bibleserver_translation'] ) : 'LUT';
		return array_merge(
			Presentation::sanitize( $input ),
			array(
				'default_source'           => in_array( $input['default_source'] ?? '', array( 'herrnhuter', 'bible2', 'both' ), true ) ? $input['default_source'] : 'herrnhuter',
				'dashboard_widget'         => empty( $input['dashboard_widget'] ) ? 0 : 1,
				'bibleserver_translation'  => isset( ReferenceLink::translations()[ $code ] ) ? $code : 'LUT',
				'remove_data_on_uninstall' => empty( $input['remove_data_on_uninstall'] ) ? 0 : 1,
			)
		);
	}

	/**
	 * Render settings with WordPress-provided CSRF protection.
	 *
	 * @return void
	 */
	public function settings_page(): void {
		$this->authorize();
		( new DesignStudio() )->page();
	}

	/**
	 * Source labels shared by forms.
	 *
	 * @return array
	 */
	private function sources(): array {
		return array(
			'herrnhuter' => 'Die Losungen',
			'bible2'     => 'Bible 2.0',
		);
	}

	/**
	 * Show inventories, readiness and local-file import controls.
	 *
	 * @return void
	 */
	public function data_page(): void {
		$this->authorize();
		$notice = get_transient( 'daily_scripture_notice_' . get_current_user_id() );
		delete_transient( 'daily_scripture_notice_' . get_current_user_id() );
		?>
		<div class="wrap ds-admin">
		<?php PageChrome::header( 'daily-scripture-data', __( 'Data sources and annual data', 'daily-scripture' ), __( 'Ready for the year: download daily readings, check your sources and manage annual packages.', 'daily-scripture' ) ); ?>
		<?php if ( is_array( $notice ) ) : ?>
			<div class="notice <?php echo ! empty( $notice['error'] ) ? 'notice-error' : 'notice-success'; ?>"><p><?php echo esc_html( $notice['message'] ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Verse data is stored exclusively under wp-content/uploads/daily-scripture/. One edition is stored per source and year. Existing years are replaced only when explicitly selected.', 'daily-scripture' ); ?></p>
		<h2><?php esc_html_e( 'Installed years', 'daily-scripture' ); ?></h2>
		<?php foreach ( $this->sources() as $source => $label ) : ?>
			<h3><?php echo esc_html( $label ); ?></h3>
			<?php
			try {
				$rows = ( new YearStore() )->inventory( $source );
			} catch ( \RuntimeException $error ) {
				echo '<p>' . esc_html( $error->getMessage() ) . '</p>';
				continue;
			}
			?>
			<div class="ds-admin-table"><table class="widefat striped"><thead><tr>
			<th><?php esc_html_e( 'Year', 'daily-scripture' ); ?></th><th><?php esc_html_e( 'Edition / days', 'daily-scripture' ); ?></th><th><?php esc_html_e( 'Imported (UTC)', 'daily-scripture' ); ?></th><th><?php esc_html_e( 'Status', 'daily-scripture' ); ?></th><th><?php esc_html_e( 'Action', 'daily-scripture' ); ?></th>
			</tr></thead><tbody>
			<?php
			if ( ! $rows ) :
				?>
				<tr><td colspan="5"><?php esc_html_e( 'No annual data is installed yet.', 'daily-scripture' ); ?></td></tr><?php endif; ?>
			<?php foreach ( $rows as $year => $row ) : ?>
				<tr><td><?php echo esc_html( (string) $year ); ?></td><td><?php echo esc_html( $row['record'] ? $row['record']['edition'] . ' / ' . count( $row['record']['days'] ) : '—' ); ?></td>
				<td><?php echo esc_html( $row['record']['imported_at'] ?? '—' ); ?></td>
				<td><?php echo esc_html( '' !== $row['error'] ? $row['error'] : ( YearValidator::allowed( $source, $year ) ? __( 'Complete / available', 'daily-scripture' ) : __( 'Outside the permitted year range; display is blocked. Please delete this year.', 'daily-scripture' ) ) ); ?></td>
				<td><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="daily_scripture_delete"><input type="hidden" name="source" value="<?php echo esc_attr( $source ); ?>"><input type="hidden" name="year" value="<?php echo esc_attr( (string) $year ); ?>">
				<?php wp_nonce_field( 'daily_scripture_delete_' . $source . '_' . $year ); ?>
				<label><input type="checkbox" name="confirm_delete" value="1" required> <?php esc_html_e( 'Confirm deletion', 'daily-scripture' ); ?></label>
				<button class="button" type="submit"><?php esc_html_e( 'Delete year', 'daily-scripture' ); ?></button></form></td></tr>
			<?php endforeach; ?>
			</tbody></table></div>
		<?php endforeach; ?>
		<h2><?php esc_html_e( 'Ready for the new year?', 'daily-scripture' ); ?></h2><ul>
		<?php foreach ( ( new Lifecycle() )->status() as $source => $years ) : ?>
			<?php foreach ( $years as $year => $status ) : ?>
				<li><?php echo esc_html( $this->sources()[ $source ] . ' · ' . $year . ': ' . $status ); ?></li>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</ul>
		<?php ( new DataDownloads() )->annual(); ?>
		<h2><?php esc_html_e( 'Import an official annual file', 'daily-scripture' ); ?></h2>
		<p><a href="https://www.losungen.de/download/">Die Losungen: <?php esc_html_e( 'Download', 'daily-scripture' ); ?></a> · <a href="https://bible2.net/de/downloadtheword">Bible 2.0: <?php esc_html_e( 'Download', 'daily-scripture' ); ?></a> · <a href="https://bible2.net/service/TheWord/twd11/current?format=html"><?php esc_html_e( 'Bible 2.0 file directory (alternative)', 'daily-scripture' ); ?></a></p>
		<p><?php esc_html_e( 'Supported: official Losungen XML/ZIP and Bible 2.0 TWD 1.1/XML/ZIP, up to 8 MiB. The entire file is checked before saving. No texts are fetched from Bibleserver.', 'daily-scripture' ); ?></p>
		<p><?php esc_html_e( 'Die Losungen: use only original files from losungen.de, unchanged verse pairs, free noncommercial use and the permitted year range. Notify the publisher of publication. Bible 2.0: also observe the rights of the respective Bible edition.', 'daily-scripture' ); ?> <a href="https://www.losungen.de/digital/nutzungsbedingungen/"><?php esc_html_e( 'Losungen terms of use', 'daily-scripture' ); ?></a> · <a href="https://bible2.net/en/copyright"><?php esc_html_e( 'Bible 2.0 license information', 'daily-scripture' ); ?></a></p>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="daily_scripture_import">
		<?php wp_nonce_field( 'daily_scripture_import' ); ?>
		<p><label for="ds-import-source"><?php esc_html_e( 'Source', 'daily-scripture' ); ?></label> <select id="ds-import-source" name="source">
		<?php
		foreach ( $this->sources() as $source => $label ) :
			?>
			<option value="<?php echo esc_attr( $source ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
		</select></p>
		<p><label for="ds-year"><?php esc_html_e( 'Year', 'daily-scripture' ); ?></label> <input id="ds-year" type="number" min="1900" max="2199" name="year" value="<?php echo esc_attr( wp_date( 'Y' ) ); ?>" required></p>
		<p><label for="ds-file"><?php esc_html_e( 'Annual file', 'daily-scripture' ); ?></label> <input id="ds-file" type="file" name="annual_file" accept=".xml,.twd,.zip" required></p>
		<p><label><input type="checkbox" name="rights_confirmed" value="1" required> <?php esc_html_e( 'This file comes from the official provider. I have reviewed the source and Bible edition terms and may publish these texts here.', 'daily-scripture' ); ?></label></p>
		<p><label><input type="checkbox" name="replace" value="1"> <?php esc_html_e( 'Replace an existing year completely after successful validation.', 'daily-scripture' ); ?></label></p>
		<?php submit_button( __( 'Validate and import', 'daily-scripture' ) ); ?>
		</form>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=daily-scripture-bibles' ) ); ?>"><?php esc_html_e( 'Install and manage freely licensed Bible editions', 'daily-scripture' ); ?></a></p><?php PageChrome::footer(); ?></div>
		<?php
	}

	/**
	 * Read strictly scalar POST identifiers after capability verification.
	 *
	 * @return array Source and year.
	 * @throws \RuntimeException For malformed input.
	 */
	private function request(): array {
		$this->authorize();
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) && is_string( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) ) {
			wp_die( esc_html__( 'Please use the corresponding form.', 'daily-scripture' ), '', array( 'response' => 405 ) );
		}
		// Nonces are checked in each action with its source/year-specific context.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$source = isset( $_POST['source'] ) && is_string( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : '';
		$raw    = isset( $_POST['year'] ) && is_string( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		if ( ! preg_match( '/^\d{4}$/D', $raw ) ) {
			throw new \RuntimeException( esc_html__( 'Please enter a four-digit year.', 'daily-scripture' ) );
		}
		$year = (int) $raw;
		YearValidator::identity( $source, $year );
		return array( $source, $year );
	}

	/**
	 * Parse and atomically install an uploaded source file.
	 *
	 * @throws \RuntimeException Caught locally and converted to an admin notice.
	 *
	 * @return void
	 */
	public function import(): void {
		try {
			list( $source, $year ) = $this->request();
			check_admin_referer( 'daily_scripture_import' );
			if ( ! $this->post_flag( 'rights_confirmed' ) ) {
				throw new \RuntimeException( esc_html__( 'Please confirm the source and permission to use the texts.', 'daily-scripture' ) );
			}
			$file = $_FILES['annual_file'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated below; original bytes must not be sanitized.
			if ( ! is_array( $file ) || UPLOAD_ERR_OK !== ( $file['error'] ?? null ) || ! is_string( $file['tmp_name'] ?? null ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
				throw new \RuntimeException( esc_html__( 'Upload failed. Please choose a file and check the PHP upload limits.', 'daily-scripture' ) );
			}
			$record = ( new Importer() )->parse( $file['tmp_name'], $source, $year );
			( new YearStore() )->save( $record, $this->post_flag( 'replace' ) );
			$this->finish( __( 'The complete year was imported successfully.', 'daily-scripture' ) );
		} catch ( \RuntimeException $error ) {
			$this->finish( $error->getMessage(), true );
		}
	}

	/**
	 * Delete the exact selected source/year after explicit confirmation.
	 *
	 * @throws \RuntimeException Caught locally and converted to an admin notice.
	 *
	 * @return void
	 */
	public function delete(): void {
		try {
			list( $source, $year ) = $this->request();
			check_admin_referer( 'daily_scripture_delete_' . $source . '_' . $year );
			if ( ! $this->post_flag( 'confirm_delete' ) ) {
				throw new \RuntimeException( esc_html__( 'Please confirm deletion of this year.', 'daily-scripture' ) );
			}
			( new YearStore() )->delete( $source, $year );
			$this->finish( __( 'The annual data was deleted.', 'daily-scripture' ) );
		} catch ( \RuntimeException $error ) {
			$this->finish( $error->getMessage(), true );
		}
	}

	/**
	 * Read a checkbox after the calling action has checked its nonce.
	 *
	 * @param string $key Checkbox name.
	 * @return bool
	 */
	private function post_flag( string $key ): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Called only after check_admin_referer in each action.
		return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) && '1' === sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
	}

	/**
	 * Redirect after POST using a private, short-lived user notice.
	 *
	 * @param string $message Result message.
	 * @param bool   $error Whether this is an error.
	 * @return void
	 */
	private function finish( string $message, bool $error = false ): void {
		set_transient(
			'daily_scripture_notice_' . get_current_user_id(),
			array(
				'message' => $message,
				'error'   => $error,
			),
			60
		);
		wp_safe_redirect( admin_url( 'admin.php?page=daily-scripture-data' ) );
		exit;
	}

	/**
	 * Render concise operating instructions.
	 *
	 * @return void
	 */
	public function help_page(): void {
		$this->authorize();
		( new Documentation() )->help();
	}
}
