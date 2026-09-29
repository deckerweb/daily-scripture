<?php
/**
 * Configuration studio and read-only preview endpoints.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

use Deckerweb\DailyScripture\Core\Settings;
use Deckerweb\DailyScripture\Core\Presentation;
use Deckerweb\DailyScripture\Core\ReferenceLink;
use Deckerweb\DailyScripture\Core\DataManager;
use Deckerweb\DailyScripture\Core\Renderer;

defined( 'ABSPATH' ) || exit;

/** Accessible grouped controls, layout thumbnails and shared-renderer previews. */
final class DesignStudio {
	/**
	 * Attach read-only AJAX operations and settings exports.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'wp_ajax_daily_scripture_preview', array( $this, 'preview' ) );
		add_action( 'wp_ajax_daily_scripture_validate_settings', array( $this, 'validate_import' ) );
		add_action( 'admin_post_daily_scripture_export_settings', array( new SettingsTransfer(), 'download' ) );
	}

	/**
	 * Load studio assets only on this settings screen.
	 *
	 * @param string $hook Admin screen hook.
	 * @return void
	 */
	public function assets( string $hook ): void {
		if ( 'toplevel_page_daily-scripture' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'daily-scripture-studio', DAILY_SCRIPTURE_URL . 'assets/css/studio.css', array(), DAILY_SCRIPTURE_VERSION );
		wp_enqueue_script( 'daily-scripture-studio', DAILY_SCRIPTURE_URL . 'assets/studio.js', array(), DAILY_SCRIPTURE_VERSION, true );
		wp_localize_script(
			'daily-scripture-studio',
			'dailyScriptureStudio',
			array(
				'url'       => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'daily_scripture_studio' ),
				'fontError' => __( 'Bitte eine positive Größe mit px, em, rem oder % oder var(--name) eingeben.', 'daily-scripture' ),
				'dirty'     => __( 'Ungespeicherte Änderungen', 'daily-scripture' ),
				'invalid'   => __( 'Bitte die markierten Eingabewerte prüfen.', 'daily-scripture' ),
				'loading'   => __( 'Deine Vorschau wird aktualisiert …', 'daily-scripture' ),
				'ready'     => __( 'Vorschau aktuell. Änderungen werden erst beim Speichern veröffentlicht.', 'daily-scripture' ),
				'error'     => __( 'Die Vorschau konnte nicht geladen werden. Bitte erneut versuchen.', 'daily-scripture' ),
				'imported'  => __( 'Import ins Formular übernommen – noch nicht gespeichert. Bitte Vorschau und Einstellungen prüfen.', 'daily-scripture' ),
				'fileError' => __( 'Bitte eine JSON-Datei bis 64 KiB auswählen.', 'daily-scripture' ),
				'contrast'  => __( 'Niedriger Textkontrast in der Vorschau. Bitte Farben prüfen.', 'daily-scripture' ),
			)
		);
	}

	/**
	 * Require administrator access and a valid nonce before any preview work.
	 *
	 * @return void
	 */
	private function authorize(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Keine Berechtigung.', 'daily-scripture' ) ), 403 );
		}
		check_ajax_referer( 'daily_scripture_studio', 'nonce' );
	}

	/**
	 * Validate configuration upload without updating WordPress options.
	 *
	 * @return void
	 */
	public function validate_import(): void {
		$this->authorize();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce checked in authorize; strict JSON validation follows, preserving exact encoded values.
		$json = isset( $_POST['document'] ) && is_string( $_POST['document'] ) ? wp_unslash( $_POST['document'] ) : '';
		try {
			wp_send_json_success( SettingsTransfer::decode( $json ) );
		} catch ( \RuntimeException $error ) {
			wp_send_json_error( array( 'message' => $error->getMessage() ), 400 );
		}
	}

	/**
	 * Render unsaved settings inside an isolated document, using original local data.
	 *
	 * @return void
	 */
	public function preview(): void {
		$this->authorize();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- authorize verifies the nonce.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- All settings are validated by the shared sanitizer immediately below.
		$input    = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
		$settings = ( new Admin() )->sanitize( $input );
		$admin    = isset( $_POST['context'] ) && 'dashboard' === $_POST['context'];
		$stress   = isset( $_POST['stress'] ) && '1' === $_POST['stress'];
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		wp_send_json_success( array( 'document' => $this->document( $settings, $admin, $stress ) ) );
	}

	/**
	 * Build a preview document. Fake text is explicitly labelled and never stored.
	 *
	 * @param array $settings Sanitized unsaved settings.
	 * @param bool  $admin Preview dashboard presentation.
	 * @param bool  $stress Simulate broad hostile theme selectors.
	 * @return string Complete escaped document.
	 */
	public function document( array $settings, bool $admin = false, bool $stress = false ): string {
		// Clearing an unsaved site heading previews its language default, not the saved title.
		foreach ( array( 'herrnhuter', 'bible2' ) as $provider ) {
			if ( '' === $settings[ 'title_' . $provider ] ) {
				$settings[ 'title_' . $provider ] = Presentation::default_heading( $provider );
			}
		}
		$sets = ( new DataManager() )->get( $settings['default_source'] );
		foreach ( $sets as &$set ) {
			if ( 'ok' !== $set['status'] ) {
				$set = array(
					'source'         => 'bible2',
					'preview_sample' => true,
					'label'          => __( 'Gestaltungsbeispiel', 'daily-scripture' ),
					'date'           => wp_date( 'Y-m-d' ),
					'status'         => 'ok',
					'edition'        => __( 'Beispieltext – keine Bibelübersetzung', 'daily-scripture' ),
					'copyright'      => __( 'Hier stehen später die vollständigen Copyright- und Lizenzhinweise der importierten Quelle.', 'daily-scripture' ),
					'items'          => array(
						array(
							'text'      => __( 'Ein ruhiger Moment für das Wort des Tages. So wirken längere Zeilen und ihre Abstände in deinem gewählten Layout.', 'daily-scripture' ),
							'reference' => __( 'Beispiel einer Bibelstellenangabe', 'daily-scripture' ),
						),
						array(
							'text'      => __( 'Auch der zweite Text hat seinen festen Platz. Beide Texte bleiben als Paar zusammen.', 'daily-scripture' ),
							'reference' => __( 'Zweite Stellenangabe', 'daily-scripture' ),
						),
					),
				);
			}
		}
		unset( $set );
		$css      = DAILY_SCRIPTURE_URL . 'assets/css/daily-scripture.css?ver=' . DAILY_SCRIPTURE_VERSION;
		$observer = DAILY_SCRIPTURE_URL . 'assets/preview-frame.js?ver=' . DAILY_SCRIPTURE_VERSION;
		$js       = DAILY_SCRIPTURE_URL . 'assets/license-dialog.js?ver=' . DAILY_SCRIPTURE_VERSION;
		$hostile  = $stress ? '.entry-content header,.entry-content footer{margin:100px auto!important;padding:40px!important;min-height:120px!important;max-width:450px!important}.entry-content section{display:flex;justify-content:space-between;min-height:800px}.entry-content blockquote{font-size:60px;margin:80px auto;width:50%}.entry-content p{font-size:40px;margin:60px!important}' : '';
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet,WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Isolated srcdoc document has no WordPress enqueue lifecycle; URLs reference only registered plugin assets.
		return '<!doctype html><html lang="' . esc_attr( get_bloginfo( 'language' ) ) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="' . esc_url( $css ) . '"><style>html{font-size:16px}body{margin:0;padding:12px;background:#eef1f4}*{box-sizing:border-box}' . $hostile . '</style></head><body><main class="entry-content">' . ( new Renderer() )->render_sets( $sets, $settings, $admin ) . '</main><script src="' . esc_url( $js ) . '"></script><script src="' . esc_url( $observer ) . '"></script></body></html>';
	}

	/**
	 * Render a labelled select.
	 *
	 * @param string $key Setting name.
	 * @param string $label Label.
	 * @param array  $choices Values and labels.
	 * @param array  $settings Current settings.
	 * @return void
	 */
	private function select( string $key, string $label, array $choices, array $settings ): void {
		echo '<label class="ds-field" for="ds-' . esc_attr( $key ) . '"><span>' . esc_html( $label ) . '</span><select id="ds-' . esc_attr( $key ) . '" name="daily_scripture_settings[' . esc_attr( $key ) . ']">';
		foreach ( $choices as $value => $text ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . selected( $settings[ $key ], $value, false ) . '>' . esc_html( $text ) . '</option>';
		}
		echo '</select></label>';
	}

	/**
	 * Render a text or number field with explicit limits.
	 *
	 * @param string $key Setting name.
	 * @param string $label Field label.
	 * @param array  $settings Current values.
	 * @param string $type Input type.
	 * @return void
	 */
	private function input( string $key, string $label, array $settings, string $type = 'text' ): void {
		$range = Presentation::ranges()[ $key ] ?? null;
		echo '<label class="ds-field" for="ds-' . esc_attr( $key ) . '"><span>' . esc_html( $label ) . '</span><input id="ds-' . esc_attr( $key ) . '" type="' . esc_attr( $type ) . '" name="daily_scripture_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $settings[ $key ] ) . '"';
		if ( $range ) {
			echo ' min="' . esc_attr( (string) $range[0] ) . '" max="' . esc_attr( (string) $range[1] ) . '" step="1" required';
		}
		if ( 'text' === $type ) {
			echo ' maxlength="' . ( 0 === strpos( $key, 'title_' ) ? '160' : '120' ) . '"';
			if ( 0 === strpos( $key, 'font_' ) ) {
				echo ' data-ds-font aria-describedby="ds-font-help" placeholder="' . esc_attr( (string) $settings[ 'size_' . substr( $key, 5 ) ] . 'px' ) . '"';
			}
			if ( 0 === strpos( $key, 'title_' ) ) {
				echo ' placeholder="' . esc_attr( Presentation::default_heading( substr( $key, 6 ) ) ) . '"';
			}
		}
		echo '></label>';
	}

	/**
	 * Main settings screen.
	 *
	 * @return void
	 */
	public function page(): void {
		$s       = Settings::all();
		$choices = Presentation::choices();
		?>
		<div class="wrap ds-studio ds-admin">
		<?php PageChrome::header( 'daily-scripture', __( 'Ein guter Platz für das tägliche Wort.', 'daily-scripture' ), __( 'Wähle einen Stil, gib den Versen Raum und sieh direkt, wie alles zusammenwirkt.', 'daily-scripture' ) ); ?>
		<div class="ds-actionbar"><div><strong>Daily Scripture</strong><span id="ds-save-status" role="status"><?php esc_html_e( 'Dein tägliches Wort. Dein Stil.', 'daily-scripture' ); ?></span></div><button type="submit" form="ds-settings-form" class="button button-primary button-hero"><?php esc_html_e( 'Einstellungen speichern', 'daily-scripture' ); ?></button></div>
		<?php settings_errors(); ?>
		<nav class="ds-studio__nav" aria-label="<?php esc_attr_e( 'Einstellungsbereiche', 'daily-scripture' ); ?>">
		<?php
		foreach ( array(
			'sources'  => __( 'Quellen', 'daily-scripture' ),
			'layouts'  => __( 'Layout', 'daily-scripture' ),
			'type'     => __( 'Schrift', 'daily-scripture' ),
			'colors'   => __( 'Farben', 'daily-scripture' ),
			'other'    => __( 'Sonstiges', 'daily-scripture' ),
			'transfer' => __( 'Import / Export', 'daily-scripture' ),
		) as $id => $label ) :
			?>
													<a href="#ds-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></a><?php endforeach; ?>
		</nav>
		<div class="ds-studio__grid"><form id="ds-settings-form" action="options.php" method="post">
		<?php settings_fields( 'daily_scripture' ); ?>
		<input type="hidden" name="daily_scripture_settings[heading_size]" value="<?php echo esc_attr( $s['heading_size'] ); ?>">
		<section class="ds-panel" id="ds-sources"><h2><?php esc_html_e( '1 · Quellen & Tagesauswahl', 'daily-scripture' ); ?></h2><p><?php esc_html_e( 'Welche Worte begleiten deine Besucher? Die Originaltexte bleiben bei jeder Gestaltung unverändert.', 'daily-scripture' ); ?></p>
		<?php
		$this->select(
			'default_source',
			__( 'Standardquelle', 'daily-scripture' ),
			array(
				'herrnhuter' => 'Die Losungen',
				'bible2'     => 'Bible 2.0',
				'both'       => __( 'Beide Quellen', 'daily-scripture' ),
			),
			$s
		);
		?>
		<?php $this->input( 'title_herrnhuter', __( 'Überschrift für Die Losungen', 'daily-scripture' ), $s ); ?>
		<?php $this->input( 'title_bible2', __( 'Überschrift für Bible 2.0', 'daily-scripture' ), $s ); ?>
		<p class="ds-help"><?php esc_html_e( 'Leer verwendet den angezeigten Standard. Diese Überschriften gelten im Dashboard und als Vorgabe für Gutenberg und Shortcodes. Im Block kannst du eigene Texte wählen. Die Quellen- und Lizenzhinweise bleiben erhalten.', 'daily-scripture' ); ?></p>
		<?php $this->select( 'bibleserver_translation', __( 'Übersetzung beim Öffnen eines Bibleserver-Links', 'daily-scripture' ), ReferenceLink::translations(), $s ); ?>
		<p class="ds-help"><?php esc_html_e( 'Betrifft nur das Linkziel, niemals den angezeigten Text.', 'daily-scripture' ); ?></p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=daily-scripture-data' ) ); ?>"><?php esc_html_e( 'Jahresdaten importieren und verwalten →', 'daily-scripture' ); ?></a></section>
		<section class="ds-panel" id="ds-layouts"><h2><?php esc_html_e( '2 · Ein Layout, das zu dir passt', 'daily-scripture' ); ?></h2><p><?php esc_html_e( 'Von klar und zurückhaltend bis zur markanten Lesekarte. Jede Variante hält beide Verse zusammen.', 'daily-scripture' ); ?></p>
		<fieldset><legend class="screen-reader-text"><?php esc_html_e( 'Layout auswählen', 'daily-scripture' ); ?></legend><div class="ds-layout-grid">
		<?php foreach ( $choices['layout'] as $key => $label ) : ?>
		<label class="ds-layout"><input type="radio" name="daily_scripture_settings[layout]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $s['layout'], $key ); ?>><span class="ds-layout__body"><span class="ds-mini ds-mini--<?php echo esc_attr( $key ); ?>" aria-hidden="true"><i></i><b></b><b></b><em></em></span><strong><?php echo esc_html( $label ); ?></strong></span></label>
		<?php endforeach; ?></div></fieldset>
		<?php $this->select( 'density', __( 'Abstände', 'daily-scripture' ), $choices['density'], $s ); ?>
		<p class="ds-help"><?php esc_html_e( 'Kompakt reduziert den Freiraum – nicht die Lesbarkeit. Gut für Seitenleiste und Fußbereich. Im Gutenberg-Block kannst du das Layout je Platzierung überschreiben.', 'daily-scripture' ); ?></p></section>
		<section class="ds-panel" id="ds-type"><h2><?php esc_html_e( '3 · Schrift mit gutem Rhythmus', 'daily-scripture' ); ?></h2><p><?php esc_html_e( 'Ein Regler, alles im Einklang: Schiebe nach rechts für größere Schrift, nach links für kleinere. Überschrift, Verse und Hinweise wachsen gemeinsam – ihre ausgewogenen Größenverhältnisse bleiben erhalten.', 'daily-scripture' ); ?></p>
		<?php $this->input( 'type_scale', __( 'Gesamtgröße · 100 % ist die empfohlene Ausgangsgröße', 'daily-scripture' ), $s, 'number' ); ?>
		<label class="ds-scale-label" for="ds-scale-slider"><?php esc_html_e( 'Gesamtgröße stufenlos ausprobieren', 'daily-scripture' ); ?></label><input id="ds-scale-slider" type="range" min="85" max="160" step="1" value="<?php echo esc_attr( (string) $s['type_scale'] ); ?>">
		<div class="ds-type-presets" aria-label="<?php esc_attr_e( 'Schriftgröße ausprobieren', 'daily-scripture' ); ?>"><button type="button" class="button" data-ds-scale="90"><?php esc_html_e( 'A · Kleiner', 'daily-scripture' ); ?></button><button type="button" class="button" data-ds-scale="100"><?php esc_html_e( 'Aa · Ausgewogen', 'daily-scripture' ); ?></button><button type="button" class="button" data-ds-scale="120"><?php esc_html_e( 'Aa · Größer', 'daily-scripture' ); ?></button></div>
		<p class="ds-type-tip"><?php esc_html_e( 'So einfach geht’s: Größe wählen → Vorschau ansehen → oben speichern. Bei 120 % ist jede Schrift ein Fünftel größer. Du kannst jederzeit zu 100 % zurückkehren.', 'daily-scripture' ); ?></p>
		<?php $this->input( 'date_format', __( 'Eigenes Datumsformat (optional)', 'daily-scripture' ), $s ); ?>
		<p class="ds-help"><?php esc_html_e( 'Leer übernimmt WordPress. Beispiele: d.m.Y oder j. F Y. Die Vorschau zeigt das Ergebnis.', 'daily-scripture' ); ?></p>
		<label class="ds-check"><input id="ds-expert-toggle" type="checkbox" name="daily_scripture_settings[expert_mode]" value="1" <?php checked( $s['expert_mode'] ); ?>> <?php esc_html_e( 'Expertenmodus: einzelne Größen und Farben fein abstimmen', 'daily-scripture' ); ?></label>
		<div id="ds-expert-fields"><p class="ds-help"><?php esc_html_e( 'Hier bestimmst du jede Schrift einzeln. Leer behält deine bisherige Pixelgröße. Beispiele: 28px, 1.75rem, 1.2em, 110% oder var(--text-xxl, 28px). Auch diese Werte werden mit der Gesamtgröße multipliziert. Ein leeres Farbfeld übernimmt das Farbschema.', 'daily-scripture' ); ?></p>
		<?php
		foreach ( array(
			'heading'   => __( 'Überschrift', 'daily-scripture' ),
			'verse'     => __( 'Bibelvers', 'daily-scripture' ),
			'reference' => __( 'Bibelstelle', 'daily-scripture' ),
			'meta'      => __( 'Lizenz & Zusatzinfos', 'daily-scripture' ),
			'date'      => __( 'Datum', 'daily-scripture' ),
		) as $part => $label ) :
			?>
		<fieldset class="ds-expert-row"><legend><?php echo esc_html( $label ); ?></legend><input type="hidden" name="daily_scripture_settings[size_<?php echo esc_attr( $part ); ?>]" value="<?php echo esc_attr( (string) $s[ 'size_' . $part ] ); ?>"><?php $this->input( 'font_' . $part, __( 'Größe mit Einheit oder CSS-Variable', 'daily-scripture' ), $s ); ?><?php $this->input( 'color_' . $part, __( 'Farbe (#RRGGBB oder leer)', 'daily-scripture' ), $s ); ?></fieldset>
		<?php endforeach; ?><div id="ds-font-help" class="ds-type-tip"><strong><?php esc_html_e( 'Welche Einheit passt zu dir?', 'daily-scripture' ); ?></strong><ul><li><?php esc_html_e( 'px: eine feste Ausgangsgröße – der vertraute Klassiker.', 'daily-scripture' ); ?></li><li><?php esc_html_e( 'rem: folgt der Grundschrift deiner Website. Bei 16 px Grundschrift sind 1.5rem = 24 px.', 'daily-scripture' ); ?></li><li><?php esc_html_e( 'em und %: beziehen sich auf die geerbte Schriftgröße des umgebenden Elements.', 'daily-scripture' ); ?></li><li><?php esc_html_e( 'var(--text-xxl): verwendet eine vorhandene CSS-Variable deines Themes oder Builders. Mit var(--text-xxl, 28px) legst du einen Ersatzwert fest.', 'daily-scripture' ); ?></li></ul><p><?php esc_html_e( 'Die CSS-Variable muss eine gültige Schriftgröße enthalten. Relative Einheiten sind anpassungsfähig, werden aber nicht automatisch je Bildschirmbreite kleiner. Eine responsive CSS-Variable kann das übernehmen. Die isolierte Vorschau lädt keine Bricks- oder Framework-Variablen und zeigt daher deren Ersatzwert. Prüfe solche Werte zusätzlich auf deiner Website. Das Dashboard-Widget verwendet seine eigenen Schriftgrößen.', 'daily-scripture' ); ?></p></div></div></section>
		<section class="ds-panel" id="ds-colors"><h2><?php esc_html_e( '4 · Farben mit Atmosphäre', 'daily-scripture' ); ?></h2>
		<?php $this->select( 'theme', __( 'Farbschema der Website', 'daily-scripture' ), $choices['theme'], $s ); ?>
		<div class="ds-color-row">
		<?php
		foreach ( array(
			'color_background' => __( 'Hintergrund', 'daily-scripture' ),
			'color_text'       => __( 'Text', 'daily-scripture' ),
			'color_accent'     => __( 'Links & Akzent', 'daily-scripture' ),
		) as $key => $label ) {
			$this->input( $key, $label, $s, 'color' ); }
		?>
		</div>
		<p class="ds-help"><?php esc_html_e( 'Eigene Farben wirken im gleichnamigen Schema. Automatisch folgt dem hellen oder dunklen Gerätemodus. Im Dashboard bleibt die WordPress-Fläche mit deinem persönlichen Admin-Akzent erhalten.', 'daily-scripture' ); ?></p></section>
		<section class="ds-panel" id="ds-other"><h2><?php esc_html_e( '5 · Sonstiges & Datenpflege', 'daily-scripture' ); ?></h2>
		<label class="ds-check"><input type="checkbox" name="daily_scripture_settings[dashboard_widget]" value="1" <?php checked( $s['dashboard_widget'] ); ?>> <?php esc_html_e( 'Die täglichen Verse auch im Dashboard anzeigen', 'daily-scripture' ); ?></label>
		<label class="ds-check"><input type="checkbox" name="daily_scripture_settings[remove_data_on_uninstall]" value="1" <?php checked( $s['remove_data_on_uninstall'] ); ?>> <?php esc_html_e( 'Beim Deinstallieren die Jahresdaten und Bibelausgaben dieser Website löschen', 'daily-scripture' ); ?></label>
		<p class="ds-help"><?php esc_html_e( 'Deaktivieren behält die Daten. Der Tages- und Jahreswechsel folgt der WordPress-Zeitzone; neue Jahre importierst du in der Datenverwaltung.', 'daily-scripture' ); ?></p></section>
		</form>
		<aside class="ds-preview-panel" aria-label="<?php esc_attr_e( 'Live-Vorschau', 'daily-scripture' ); ?>"><h2><?php esc_html_e( 'So wirken deine Verse', 'daily-scripture' ); ?></h2>
		<p><?php esc_html_e( 'Originaldaten von heute, sofern verfügbar. Sonst ein klar gekennzeichnetes Gestaltungsbeispiel.', 'daily-scripture' ); ?></p>
		<div class="ds-preview-controls"><label for="ds-preview-width"><?php esc_html_e( 'Breite', 'daily-scripture' ); ?></label><select id="ds-preview-width"><option value="wide"><?php esc_html_e( 'Verfügbarer Platz', 'daily-scripture' ); ?></option><option value="390"><?php esc_html_e( 'Mobil · 390 px', 'daily-scripture' ); ?></option><option value="300"><?php esc_html_e( 'Seitenleiste · 300 px', 'daily-scripture' ); ?></option></select>
		<label for="ds-preview-context"><?php esc_html_e( 'Bereich', 'daily-scripture' ); ?></label><select id="ds-preview-context"><option value="frontend"><?php esc_html_e( 'Website', 'daily-scripture' ); ?></option><option value="dashboard"><?php esc_html_e( 'Dashboard-Farben', 'daily-scripture' ); ?></option></select></div>
		<label class="ds-check"><input id="ds-preview-stress" type="checkbox"> <?php esc_html_e( 'Strenge Theme-Regeln simulieren', 'daily-scripture' ); ?></label>
		<p class="ds-help"><?php esc_html_e( 'Die Vorschau zeigt die Gestaltung des Plugins. Dein Theme kann die verfügbare Breite beeinflussen. Die Simulation prüft typische Abstandsprobleme. Die Dashboard-Vorschau zeigt die Admin-Farben; deine persönliche Lesegröße wählst du direkt im Widget.', 'daily-scripture' ); ?></p>
		<div class="ds-preview-viewport"><iframe id="ds-preview-frame" title="<?php esc_attr_e( 'Vorschau der täglichen Verse', 'daily-scripture' ); ?>" sandbox="allow-scripts" referrerpolicy="no-referrer"></iframe></div>
		<p id="ds-preview-status" role="status" aria-live="polite"></p><button type="button" class="button" id="ds-preview-refresh"><?php esc_html_e( 'Vorschau aktualisieren', 'daily-scripture' ); ?></button>
		<noscript><p><?php esc_html_e( 'Live-Vorschau und JSON-Übernahme benötigen JavaScript. Die Einstellungen lassen sich auch ohne JavaScript speichern.', 'daily-scripture' ); ?></p></noscript>
		</aside></div>
		<section class="ds-panel" id="ds-transfer"><h2><?php esc_html_e( '6 · Mitnehmen, sichern, wiederverwenden', 'daily-scripture' ); ?></h2><p><?php esc_html_e( 'Ein Design enthält Layout, Farben, Schriftgrößen und Datumsformat. Die vollständige Sicherung enthält zusätzlich Quellenwahl und weitere Website-Einstellungen. Bibeltexte, Jahrespakete, persönliche Dashboard-Einstellungen und Builder-Stile sind nicht enthalten.', 'daily-scripture' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="daily_scripture_export_settings"><?php wp_nonce_field( 'daily_scripture_export_settings' ); ?>
		<button class="button" name="kind" value="design"><?php esc_html_e( 'Gespeichertes Design als JSON', 'daily-scripture' ); ?></button> <button class="button" name="kind" value="settings"><?php esc_html_e( 'Alle gespeicherten Einstellungen als JSON', 'daily-scripture' ); ?></button></form>
		<div class="ds-import"><label for="ds-settings-file"><?php esc_html_e( 'JSON-Datei übernehmen (bis 64 KiB)', 'daily-scripture' ); ?></label> <input id="ds-settings-file" type="file" accept=".json,application/json"><button id="ds-import-settings" type="button" class="button"><?php esc_html_e( 'Prüfen & ins Formular übernehmen', 'daily-scripture' ); ?></button><p id="ds-import-status" role="status" aria-live="polite"></p>
		<p class="ds-help"><?php esc_html_e( 'Der Import wird zuerst ins Formular geladen. Prüfe ihn in Ruhe und speichere erst danach. Vollständige Importe können auch die Löschoption bei Deinstallation ändern.', 'daily-scripture' ); ?></p></div></section><?php PageChrome::footer(); ?></div>
		<?php
	}
}
