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
			array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=daily-scripture' ) ) . '">' . esc_html__( 'Einstellungen', 'daily-scripture' ) . '</a>' );
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
			wp_die( esc_html__( 'Keine Berechtigung für diese Aktion.', 'daily-scripture' ), '', array( 'response' => 403 ) );
		}
	}

	/**
	 * Register menu pages.
	 *
	 * @return void
	 */
	public function menu(): void {
		add_menu_page( 'Daily Scripture', 'Daily Scripture', 'manage_options', 'daily-scripture', array( $this, 'settings_page' ), 'dashicons-book-alt', 58 );
		add_submenu_page( 'daily-scripture', __( 'Datenquellen', 'daily-scripture' ), __( 'Datenquellen', 'daily-scripture' ), 'manage_options', 'daily-scripture-data', array( $this, 'data_page' ) );
		add_submenu_page( 'daily-scripture', __( 'Shortcodes', 'daily-scripture' ), __( 'Shortcodes', 'daily-scripture' ), 'manage_options', 'daily-scripture-shortcodes', array( new Documentation(), 'shortcodes' ) );
		add_submenu_page( 'daily-scripture', __( 'Hilfe', 'daily-scripture' ), __( 'Hilfe', 'daily-scripture' ), 'manage_options', 'daily-scripture-help', array( $this, 'help_page' ) );
	}

	/**
	 * Register the WordPress settings API sanitizer.
	 *
	 * @return void
	 */
	public function settings(): void {
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
		<?php PageChrome::header( 'daily-scripture-data', __( 'Datenquellen und Jahresdaten', 'daily-scripture' ), __( 'Gut versorgt durchs Jahr: Lade neue Tagesverse, behalte deine Quellen im Blick und verwalte deine Jahrespakete.', 'daily-scripture' ) ); ?>
		<?php if ( is_array( $notice ) ) : ?>
			<div class="notice <?php echo ! empty( $notice['error'] ) ? 'notice-error' : 'notice-success'; ?>"><p><?php echo esc_html( $notice['message'] ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Versdaten werden ausschließlich unter wp-content/uploads/daily-scripture/ gespeichert. Pro Quelle und Jahr wird eine Ausgabe verwaltet. Vorhandene Jahre werden nur nach ausdrücklicher Auswahl ersetzt.', 'daily-scripture' ); ?></p>
		<h2><?php esc_html_e( 'Installierte Jahre', 'daily-scripture' ); ?></h2>
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
			<th><?php esc_html_e( 'Jahr', 'daily-scripture' ); ?></th><th><?php esc_html_e( 'Ausgabe / Tage', 'daily-scripture' ); ?></th><th><?php esc_html_e( 'Importiert (UTC)', 'daily-scripture' ); ?></th><th><?php esc_html_e( 'Status', 'daily-scripture' ); ?></th><th><?php esc_html_e( 'Aktion', 'daily-scripture' ); ?></th>
			</tr></thead><tbody>
			<?php
			if ( ! $rows ) :
				?>
				<tr><td colspan="5"><?php esc_html_e( 'Noch keine Jahresdaten installiert.', 'daily-scripture' ); ?></td></tr><?php endif; ?>
			<?php foreach ( $rows as $year => $row ) : ?>
				<tr><td><?php echo esc_html( (string) $year ); ?></td><td><?php echo esc_html( $row['record'] ? $row['record']['edition'] . ' / ' . count( $row['record']['days'] ) : '—' ); ?></td>
				<td><?php echo esc_html( $row['record']['imported_at'] ?? '—' ); ?></td>
				<td><?php echo esc_html( '' !== $row['error'] ? $row['error'] : ( YearValidator::allowed( $source, $year ) ? __( 'Vollständig / verfügbar', 'daily-scripture' ) : __( 'Außerhalb des zulässigen Jahresbereichs; Ausgabe gesperrt. Bitte löschen.', 'daily-scripture' ) ) ); ?></td>
				<td><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="daily_scripture_delete"><input type="hidden" name="source" value="<?php echo esc_attr( $source ); ?>"><input type="hidden" name="year" value="<?php echo esc_attr( (string) $year ); ?>">
				<?php wp_nonce_field( 'daily_scripture_delete_' . $source . '_' . $year ); ?>
				<label><input type="checkbox" name="confirm_delete" value="1" required> <?php esc_html_e( 'Löschen bestätigen', 'daily-scripture' ); ?></label>
				<button class="button" type="submit"><?php esc_html_e( 'Jahr löschen', 'daily-scripture' ); ?></button></form></td></tr>
			<?php endforeach; ?>
			</tbody></table></div>
		<?php endforeach; ?>
		<h2><?php esc_html_e( 'Bereit für den Jahreswechsel?', 'daily-scripture' ); ?></h2><ul>
		<?php foreach ( ( new Lifecycle() )->status() as $source => $years ) : ?>
			<?php foreach ( $years as $year => $status ) : ?>
				<li><?php echo esc_html( $this->sources()[ $source ] . ' · ' . $year . ': ' . $status ); ?></li>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</ul>
		<?php ( new DataDownloads() )->annual(); ?>
		<h2><?php esc_html_e( 'Offizielle Jahresdatei importieren', 'daily-scripture' ); ?></h2>
		<p><a href="https://www.losungen.de/download/">Die Losungen: <?php esc_html_e( 'Download', 'daily-scripture' ); ?></a> · <a href="https://bible2.net/de/downloadtheword">Bible 2.0: <?php esc_html_e( 'Download', 'daily-scripture' ); ?></a> · <a href="https://bible2.net/service/TheWord/twd11/current?format=html"><?php esc_html_e( 'Bible-2.0-Dateiverzeichnis (Alternative)', 'daily-scripture' ); ?></a></p>
		<p><?php esc_html_e( 'Unterstützt: offizielles Losungen-XML/ZIP und Bible-2.0-TWD 1.1/XML/ZIP, maximal 8 MiB. Die gesamte Datei wird vor dem Speichern geprüft. Es werden keine Texte von Bibleserver abgerufen.', 'daily-scripture' ); ?></p>
		<p><?php esc_html_e( 'Die Losungen: nur Originaldateien von losungen.de, unveränderte Verspaare, kostenlose nichtkommerzielle Nutzung und zulässiger Jahresbereich. Die Veröffentlichung ist der Herausgeberin zu melden. Bible 2.0: zusätzlich die Rechte der jeweiligen Bibelausgabe beachten.', 'daily-scripture' ); ?> <a href="https://www.losungen.de/digital/nutzungsbedingungen/"><?php esc_html_e( 'Losungen-Nutzungsbedingungen', 'daily-scripture' ); ?></a> · <a href="https://bible2.net/en/copyright"><?php esc_html_e( 'Bible-2.0-Lizenzinformationen', 'daily-scripture' ); ?></a></p>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="daily_scripture_import">
		<?php wp_nonce_field( 'daily_scripture_import' ); ?>
		<p><label for="ds-import-source"><?php esc_html_e( 'Quelle', 'daily-scripture' ); ?></label> <select id="ds-import-source" name="source">
		<?php
		foreach ( $this->sources() as $source => $label ) :
			?>
			<option value="<?php echo esc_attr( $source ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
		</select></p>
		<p><label for="ds-year"><?php esc_html_e( 'Jahr', 'daily-scripture' ); ?></label> <input id="ds-year" type="number" min="1900" max="2199" name="year" value="<?php echo esc_attr( wp_date( 'Y' ) ); ?>" required></p>
		<p><label for="ds-file"><?php esc_html_e( 'Jahresdatei', 'daily-scripture' ); ?></label> <input id="ds-file" type="file" name="annual_file" accept=".xml,.twd,.zip" required></p>
		<p><label><input type="checkbox" name="rights_confirmed" value="1" required> <?php esc_html_e( 'Die Datei stammt vom offiziellen Anbieter. Ich habe die Nutzungsbedingungen der Quelle und Bibelausgabe geprüft und darf diese Texte hier veröffentlichen.', 'daily-scripture' ); ?></label></p>
		<p><label><input type="checkbox" name="replace" value="1"> <?php esc_html_e( 'Ein vorhandenes Jahr nach erfolgreicher Prüfung vollständig ersetzen.', 'daily-scripture' ); ?></label></p>
		<?php submit_button( __( 'Prüfen und importieren', 'daily-scripture' ) ); ?>
		</form>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=daily-scripture-bibles' ) ); ?>"><?php esc_html_e( 'Freie Bibelübersetzungen installieren und verwalten', 'daily-scripture' ); ?></a></p><?php PageChrome::footer(); ?></div>
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
			wp_die( esc_html__( 'Bitte das zugehörige Formular verwenden.', 'daily-scripture' ), '', array( 'response' => 405 ) );
		}
		// Nonces are checked in each action with its source/year-specific context.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$source = isset( $_POST['source'] ) && is_string( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : '';
		$raw    = isset( $_POST['year'] ) && is_string( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		if ( ! preg_match( '/^\d{4}$/D', $raw ) ) {
			throw new \RuntimeException( esc_html__( 'Bitte eine vierstellige Jahreszahl angeben.', 'daily-scripture' ) );
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
				throw new \RuntimeException( esc_html__( 'Bitte Herkunft und Nutzungsrechte bestätigen.', 'daily-scripture' ) );
			}
			$file = $_FILES['annual_file'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated below; original bytes must not be sanitized.
			if ( ! is_array( $file ) || UPLOAD_ERR_OK !== ( $file['error'] ?? null ) || ! is_string( $file['tmp_name'] ?? null ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
				throw new \RuntimeException( esc_html__( 'Upload fehlgeschlagen. Bitte eine Datei auswählen und die PHP-Uploadgrenzen prüfen.', 'daily-scripture' ) );
			}
			$record = ( new Importer() )->parse( $file['tmp_name'], $source, $year );
			( new YearStore() )->save( $record, $this->post_flag( 'replace' ) );
			$this->finish( __( 'Das vollständige Jahr wurde erfolgreich importiert.', 'daily-scripture' ) );
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
				throw new \RuntimeException( esc_html__( 'Bitte das Löschen dieses Jahres bestätigen.', 'daily-scripture' ) );
			}
			( new YearStore() )->delete( $source, $year );
			$this->finish( __( 'Die Jahresdaten wurden gelöscht.', 'daily-scripture' ) );
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
