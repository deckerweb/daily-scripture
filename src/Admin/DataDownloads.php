<?php
/**
 * Capability- and nonce-protected package downloads and Bible library UI.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

use Deckerweb\DailyScripture\Downloads\AnnualPackages;
use Deckerweb\DailyScripture\Bible\{TranslationManager, Library, Passage};
defined( 'ABSPATH' ) || exit;

/** Explicit administrator actions; unavailable downloads always retain upload fallback. */
final class DataDownloads {
	/** Register the library page and a POST-only dispatcher. @return void */
	public function register(): void {
		add_action( 'admin_post_daily_scripture_packages', array( $this, 'handle' ) );
		add_action(
			'admin_menu',
			/**
			 * Register the administrator-only Bible library submenu.
			 *
			 * @return void
			 */
			function () {
				add_submenu_page( 'daily-scripture', __( 'Bible library', 'daily-scripture' ), __( 'Bible library', 'daily-scripture' ), 'manage_options', 'daily-scripture-bibles', array( $this, 'page' ), 2 );
			},
			11
		);
	}
	/**
	 * Start a uniquely identified action form.
	 *
	 * @param string $operation Action.
	 * @param string $id Source/edition.
	 * @return void
	 */
	private function form( string $operation, string $id ): void {
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="daily_scripture_packages"><input type="hidden" name="operation" value="' . esc_attr( $operation ) . '"><input type="hidden" name="id" value="' . esc_attr( $id ) . '">';
		wp_nonce_field( 'daily_scripture_packages_' . $operation . '_' . $id );
	}
	/** Render the annual catalogue panel within the existing data page. @return void */
	public function annual(): void {
		echo '<h2>' . esc_html__( 'Annual packages directly from the provider', 'daily-scripture' ) . '</h2><p>' . esc_html__( 'Check the official directory, then download and import an available package. Results are cached for six hours; a download may still be temporarily unavailable. Manual upload is always available.', 'daily-scripture' ) . '</p>';
		foreach ( array(
			'herrnhuter' => 'Die Losungen',
			'bible2'     => 'Bible 2.0',
		) as $source => $label ) {
			$catalogue = ( new AnnualPackages() )->catalogue( $source );
			echo '<section class="card"><h3>' . esc_html( $label ) . '</h3>';
			$status = ! $catalogue ? __( 'Not checked yet / check expired', 'daily-scripture' ) : ( $catalogue['error'] ? __( 'Download currently unavailable', 'daily-scripture' ) : __( 'Available in the official directory', 'daily-scripture' ) );
			echo '<p><strong>' . esc_html( $status ) . '</strong></p>';
			if ( $catalogue ) {
				echo '<p>' . esc_html( wp_date( get_option( 'date_format' ) . ' H:i', $catalogue['checked'] ) ) . '</p>';
				if ( $catalogue['error'] ) {
					echo '<p>' . esc_html( $catalogue['error'] ) . '</p>'; }
			}
			$this->form( 'check', $source );
			submit_button( __( 'Check download source', 'daily-scripture' ), 'secondary', 'check_' . $source, false );
			echo '</form>';
			if ( ! empty( $catalogue['items'] ) && empty( $catalogue['error'] ) ) {
				$this->form( 'annual', $source );
				echo '<p><label>' . esc_html__( 'Annual package', 'daily-scripture' ) . ' <select name="package">';
				foreach ( $catalogue['items'] as $key => $item ) {
					echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $item['label'] ) . '</option>'; }
				echo '</select></label></p><p><label><input type="checkbox" name="rights" value="1" required> ' . esc_html__( 'I have reviewed the source and Bible edition terms and may publish these texts here.', 'daily-scripture' ) . '</label></p>';
				echo '<p><a href="' . esc_url( 'herrnhuter' === $source ? 'https://www.losungen.de/digital/nutzungsbedingungen/' : 'https://bible2.net/en/copyright' ) . '">' . esc_html__( 'Open terms of use', 'daily-scripture' ) . '</a></p>';
				$this->replace();
				submit_button( __( 'Download & import', 'daily-scripture' ), 'primary', 'annual_' . $source, false );
				echo '</form>';
			}
			echo '<p>' . esc_html__( 'Missing next year? Once the provider publishes it, it appears after another check.', 'daily-scripture' ) . '</p></section>';
		}
	}
	/** Explicit replace opt-in shared by the download forms. @return void */
	private function replace(): void {
		echo '<p><label><input type="checkbox" name="replace" value="1"> ' . esc_html__( 'Replace existing data only after successful validation.', 'daily-scripture' ) . '</label></p>';
	}
	/** Render local edition management and a real passage preview. @return void */
	public function page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission.', 'daily-scripture' ) ); }
		echo '<div class="wrap ds-admin">';
		PageChrome::header( 'daily-scripture-bibles', __( 'Bible library', 'daily-scripture' ), __( 'Install historical translations and display selected Bible passages on your site.', 'daily-scripture' ) );
		$this->notice();
		echo '<p>' . esc_html__( 'Four historical, freely usable editions for selected passages. They are installed only on request and stored entirely locally. Official Losungen and Bible 2.0 daily readings stay unchanged.', 'daily-scripture' ) . '</p><p>' . esc_html__( 'Each edition includes the 66 books of the Old and New Testaments. Additional apocrypha and editorial headings are not imported. Verse numbering may differ between translations.', 'daily-scripture' ) . '</p>';
		foreach ( ( new TranslationManager() )->bundled() as $id => $edition ) {
			echo '<section class="card"><h2>' . esc_html( $edition['label'] ) . '</h2>';
			try {
				$record = ( new Library() )->read( $id );
				echo '<p><strong>' . esc_html( $record ? __( 'Installed locally', 'daily-scripture' ) : __( 'Not installed yet', 'daily-scripture' ) ) . '</strong>';
				if ( $record ) {
					echo ' · ' . esc_html( (string) $record['verses'] ) . ' ' . esc_html__( 'Verse positions', 'daily-scripture' ); }
				echo '</p>';
			} catch ( \RuntimeException $error ) {
				echo '<p>' . esc_html( $error->getMessage() ) . '</p>'; }
			echo '<p>' . esc_html( TranslationManager::license_label( $edition ) ) . ' · <a href="' . esc_url( $edition['source'] ) . '">' . esc_html__( 'Source & package', 'daily-scripture' ) . '</a> · <a href="' . esc_url( $edition['url'] ) . '">' . esc_html__( 'Download original package', 'daily-scripture' ) . '</a></p>';
			echo TranslationManager::attribution_html( $edition ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Registry renderer escapes every value.
			if ( 'schlachter-1951' === $id ) {
				echo '<p>' . esc_html__( 'This source file leaves Matthew 21:44 empty and numbers the following verses differently. Selecting the empty verse displays a notice.', 'daily-scripture' ) . '</p>';
			}
			if ( 'menge-1939' === $id ) {
				echo '<p>' . esc_html__( 'This edition combines Ezekiel 33:14–15. Please select both verses together; verse parts are joined in full.', 'daily-scripture' ) . '</p>';
				echo '<p><a href="https://www.toledot.info/die-welt-der-bibel.php?t=info%2Freformation%2Ftextueberarbeitung">' . esc_html__( 'Public-domain notice for the Zefania edition', 'daily-scripture' ) . '</a> · <a href="https://www.crosswire.org/sword/copyright/ModInfoCopyright.jsp?modName=GerMenge">' . esc_html__( 'CrossWire: Menge 1939 / public domain', 'daily-scripture' ) . '</a></p>';
			}
			$this->form( 'bible_install', $id );
			echo '<p><label>' . esc_html__( 'Optional: upload the original package manually', 'daily-scripture' ) . ' <input type="file" name="bible_file" accept=".zip,.txt,.xml"></label></p><p class="description">' . esc_html__( 'Without an uploaded file, the package is downloaded from the linked source. ZIP or its original TXT/XML file, up to 10 MB. Only the verified text revision is accepted; other revisions are rejected safely.', 'daily-scripture' ) . '</p>';
			$this->replace();
			submit_button( __( 'Install edition', 'daily-scripture' ), 'primary', 'install_' . $id, false );
			echo '</form>';
			$this->form( 'bible_delete', $id );
			echo '<p><label><input type="checkbox" name="confirm" value="1" required> ' . esc_html__( 'Confirm deletion of this local edition', 'daily-scripture' ) . '</label> <button class="button">' . esc_html__( 'Delete edition', 'daily-scripture' ) . '</button></p></form></section>';
		}
		echo '<h2>' . esc_html__( 'Your first Bible passage', 'daily-scripture' ) . '</h2><p>' . esc_html__( 'In Gutenberg, Elementor and Bricks, choose “Bible passage · Daily Scripture”. Select your edition and passage. Source notices stay visible; presentation follows your settings.', 'daily-scripture' ) . '</p><p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=daily-scripture-shortcodes' ) ) . '">' . esc_html__( 'Shortcodes & examples to copy', 'daily-scripture' ) . '</a></p>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer escapes all text and attributes.
		echo ( new Passage() )->render(
			array(
				'translation' => 'luther-1912',
				'book'        => 'JOH',
				'chapter'     => 3,
				'from'        => 16,
				'to'          => 17,
			)
		); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by renderer.
		PageChrome::footer();
		echo '</div>';
	}
	/** Output a single-use private notice. @return void */
	private function notice(): void {
		$key    = 'daily_scripture_library_notice_' . get_current_user_id();
		$notice = get_transient( $key );
		delete_transient( $key );
		if ( is_array( $notice ) ) {
			echo '<div class="notice ' . esc_attr( $notice['error'] ? 'notice-error' : 'notice-success' ) . '"><p>' . esc_html( $notice['message'] ) . '</p></div>'; }
	}
	/**
	 * Scalar POST reader used only after nonce validation for action data.
	 *
	 * @param string $key Field.
	 * @return string
	 */
	private function field( string $key ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Dispatcher validates nonce before consuming action data; identifiers select that nonce.
		return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	}
	/**
	 * Dispatch administrator-only POST actions and redirect with a private notice.
	 *
	 * @return void
	 * @throws \RuntimeException Caught locally and shown as a private notice.
	 */
	public function handle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission.', 'daily-scripture' ), '', array( 'response' => 403 ) ); }
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) && is_string( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) ) {
			wp_die( esc_html__( 'Please use the corresponding form.', 'daily-scripture' ), '', array( 'response' => 405 ) ); }
		$operation = $this->field( 'operation' );
		$id        = $this->field( 'id' );
		check_admin_referer( 'daily_scripture_packages_' . $operation . '_' . $id );
		$is_bible = in_array( $operation, array( 'bible_install', 'bible_delete' ), true );
		$error    = false;
		try {
			if ( 'check' === $operation ) {
				$result = ( new AnnualPackages() )->catalogue( $id, true );
				if ( $result['error'] ) {
					throw new \RuntimeException( $result['error'] ); }
				$message = __( 'Download source checked. Available packages are ready to select.', 'daily-scripture' );
			} elseif ( 'annual' === $operation ) {
				if ( '1' !== $this->field( 'rights' ) ) {
					throw new \RuntimeException( esc_html__( 'Please confirm permission to use the texts.', 'daily-scripture' ) ); }
				( new AnnualPackages() )->install( $id, $this->field( 'package' ), '1' === $this->field( 'replace' ) );
				$message = __( 'Annual package validated and installed locally.', 'daily-scripture' );
			} elseif ( 'bible_install' === $operation ) {
				$upload = $_FILES['bible_file'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Original file checked below and fingerprinted by library.
				$path   = '';
				if ( null !== $upload && ( ! is_array( $upload ) || UPLOAD_ERR_NO_FILE !== ( $upload['error'] ?? null ) ) ) {
					if ( ! is_array( $upload ) || UPLOAD_ERR_OK !== ( $upload['error'] ?? null ) || ! is_string( $upload['tmp_name'] ?? null ) || ! is_uploaded_file( $upload['tmp_name'] ) ) {
						throw new \RuntimeException( esc_html__( 'File upload failed.', 'daily-scripture' ) ); }
					$path = $upload['tmp_name'];
				}
				( new Library() )->install( $id, '1' === $this->field( 'replace' ), $path );
				$message = __( 'The verified Bible edition is now available locally.', 'daily-scripture' );
			} elseif ( 'bible_delete' === $operation ) {
				if ( '1' !== $this->field( 'confirm' ) ) {
					throw new \RuntimeException( esc_html__( 'Please confirm deletion.', 'daily-scripture' ) ); }
				( new Library() )->delete( $id );
				$message = __( 'The local Bible edition was deleted.', 'daily-scripture' );
			} else {
				throw new \RuntimeException( esc_html__( 'Unknown action.', 'daily-scripture' ) ); }
		} catch ( \RuntimeException $exception ) {
			$error   = true;
			$message = $exception->getMessage(); }
		set_transient(
			( $is_bible ? 'daily_scripture_library_notice_' : 'daily_scripture_notice_' ) . get_current_user_id(),
			array(
				'message' => $message,
				'error'   => $error,
			),
			MINUTE_IN_SECONDS
		);
		wp_safe_redirect( admin_url( 'admin.php?page=' . ( $is_bible ? 'daily-scripture-bibles' : 'daily-scripture-data' ) ) );
		exit;
	}
}
