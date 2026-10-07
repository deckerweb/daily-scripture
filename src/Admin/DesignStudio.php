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
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_script( 'daily-scripture-color-picker', DAILY_SCRIPTURE_URL . 'assets/color-picker.js', array( 'wp-components', 'wp-element', 'wp-i18n' ), DAILY_SCRIPTURE_VERSION, true );
		wp_set_script_translations( 'daily-scripture-color-picker', 'daily-scripture', DAILY_SCRIPTURE_DIR . 'languages' );
		wp_enqueue_style( 'daily-scripture-studio', DAILY_SCRIPTURE_URL . 'assets/css/studio.css', array(), DAILY_SCRIPTURE_VERSION );
		wp_enqueue_script( 'daily-scripture-studio', DAILY_SCRIPTURE_URL . 'assets/studio.js', array(), DAILY_SCRIPTURE_VERSION, true );
		wp_localize_script(
			'daily-scripture-studio',
			'dailyScriptureStudio',
			array(
				'url'       => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'daily_scripture_studio' ),
				'fontError' => __( 'Please enter a positive size with px, em, rem, %, or var(--name).', 'daily-scripture' ),
				'dirty'     => __( 'Unsaved changes', 'daily-scripture' ),
				'invalid'   => __( 'Please check the highlighted values.', 'daily-scripture' ),
				'loading'   => __( 'Updating your preview …', 'daily-scripture' ),
				'ready'     => __( 'Preview updated. Changes apply only after saving.', 'daily-scripture' ),
				'error'     => __( 'The preview could not be loaded. Please try again.', 'daily-scripture' ),
				'imported'  => __( 'Import loaded into the form, but not saved. Please check the preview and settings.', 'daily-scripture' ),
				'fileError' => __( 'Please select a JSON file up to 64 KiB.', 'daily-scripture' ),
				'contrast'  => __( 'Low text contrast in the preview. Please check the colors.', 'daily-scripture' ),
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
			wp_send_json_error( array( 'message' => __( 'You do not have permission.', 'daily-scripture' ) ), 403 );
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
					'label'          => __( 'Design sample', 'daily-scripture' ),
					'date'           => wp_date( 'Y-m-d' ),
					'status'         => 'ok',
					'edition'        => __( 'Sample text, not a Bible translation', 'daily-scripture' ),
					'copyright'      => __( 'The imported source’s complete copyright and license notices will appear here.', 'daily-scripture' ),
					'items'          => array(
						array(
							'text'      => __( 'A quiet moment for the word of the day. See how longer lines and their spacing look in your chosen layout.', 'daily-scripture' ),
							'reference' => __( 'Sample Bible reference', 'daily-scripture' ),
						),
						array(
							'text'      => __( 'The second text has its own place too. Both texts stay together as a pair.', 'daily-scripture' ),
							'reference' => __( 'Second reference', 'daily-scripture' ),
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
		$color = 0 === strpos( $key, 'color_' );
		$type  = $color ? 'text' : $type;
		$range = Presentation::ranges()[ $key ] ?? null;
		echo '<label class="ds-field" for="ds-' . esc_attr( $key ) . '"><span>' . esc_html( $label ) . '</span><input id="ds-' . esc_attr( $key ) . '" type="' . esc_attr( $type ) . '" name="daily_scripture_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $settings[ $key ] ) . '"';
		if ( $color ) {
			echo ' data-ds-color autocomplete="off" spellcheck="false" maxlength="7"';
			if ( in_array( $key, array( 'color_background', 'color_text', 'color_accent' ), true ) ) {
				echo ' required pattern="#[a-fA-F0-9]{3}([a-fA-F0-9]{3})?"';
			}
		}
		if ( $range ) {
			echo ' min="' . esc_attr( (string) $range[0] ) . '" max="' . esc_attr( (string) $range[1] ) . '" step="1" required';
		}
		if ( 'text' === $type && ! $color ) {
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
		<?php PageChrome::header( 'daily-scripture', __( 'A good place for the daily word.', 'daily-scripture' ), __( 'Daily readings for your WordPress website, also in Multisite. Choose your sources, shape the design and preview the result.', 'daily-scripture' ) ); ?>
		<div class="ds-actionbar"><div><strong>Daily Scripture</strong><span id="ds-save-status" role="status"><?php esc_html_e( 'Your daily word. Your style.', 'daily-scripture' ); ?></span></div><button type="submit" form="ds-settings-form" class="button button-primary button-hero"><?php esc_html_e( 'Save settings', 'daily-scripture' ); ?></button></div>
		<?php settings_errors(); ?>
		<nav class="ds-studio__nav" aria-label="<?php esc_attr_e( 'Settings sections', 'daily-scripture' ); ?>">
		<?php
		foreach ( array(
			'sources'   => __( 'Sources', 'daily-scripture' ),
			'layouts'   => __( 'Layout', 'daily-scripture' ),
			'type'      => __( 'Typography', 'daily-scripture' ),
			'colors'    => __( 'Colors', 'daily-scripture' ),
			'dashboard' => __( 'Dashboard', 'daily-scripture' ),
			'other'     => __( 'Other settings', 'daily-scripture' ),
			'transfer'  => __( 'Import / Export', 'daily-scripture' ),
		) as $id => $label ) :
			?>
													<a href="#ds-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></a><?php endforeach; ?>
		</nav>
		<div class="ds-studio__grid"><form id="ds-settings-form" action="options.php" method="post">
		<?php settings_fields( 'daily_scripture' ); ?>
		<input type="hidden" name="daily_scripture_settings[heading_size]" value="<?php echo esc_attr( $s['heading_size'] ); ?>">
		<section class="ds-panel" id="ds-sources"><h2><?php esc_html_e( '1 · Sources & daily readings', 'daily-scripture' ); ?></h2><p><?php esc_html_e( 'Which words will accompany your visitors? Original texts stay unchanged in every design.', 'daily-scripture' ); ?></p>
		<?php
		$this->select(
			'default_source',
			__( 'Default source', 'daily-scripture' ),
			array(
				'herrnhuter' => 'Die Losungen',
				'bible2'     => 'Bible 2.0',
				'both'       => __( 'Both sources', 'daily-scripture' ),
			),
			$s
		);
		?>
		<?php $this->input( 'title_herrnhuter', __( 'Heading for Die Losungen', 'daily-scripture' ), $s ); ?>
		<?php $this->input( 'title_bible2', __( 'Heading for Bible 2.0', 'daily-scripture' ), $s ); ?>
		<p class="ds-help"><?php esc_html_e( 'Leave blank to use the displayed default. These headings apply to the dashboard and serve as defaults for Gutenberg and shortcodes. Blocks can use their own headings. Source and license notices stay visible.', 'daily-scripture' ); ?></p>
		<?php $this->select( 'bibleserver_translation', __( 'Translation when opening a Bibleserver link', 'daily-scripture' ), ReferenceLink::translations(), $s ); ?>
		<p class="ds-help"><?php esc_html_e( 'Changes only the link destination, never the displayed text.', 'daily-scripture' ); ?></p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=daily-scripture-data' ) ); ?>"><?php esc_html_e( 'Import and manage annual data →', 'daily-scripture' ); ?></a></section>
		<section class="ds-panel" id="ds-layouts"><h2><?php esc_html_e( '2 · A layout that fits', 'daily-scripture' ); ?></h2><p><?php esc_html_e( 'From quiet and simple to a distinctive reading card. Every layout keeps both verses together.', 'daily-scripture' ); ?></p>
		<fieldset><legend class="screen-reader-text"><?php esc_html_e( 'Choose layout', 'daily-scripture' ); ?></legend><div class="ds-layout-grid">
		<?php foreach ( $choices['layout'] as $key => $label ) : ?>
		<label class="ds-layout"><input type="radio" name="daily_scripture_settings[layout]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $s['layout'], $key ); ?>><span class="ds-layout__body"><span class="ds-mini ds-mini--<?php echo esc_attr( $key ); ?>" aria-hidden="true"><i></i><b></b><b></b><em></em></span><strong><?php echo esc_html( $label ); ?></strong></span></label>
		<?php endforeach; ?></div></fieldset>
		<?php $this->select( 'density', __( 'Spacing', 'daily-scripture' ), $choices['density'], $s ); ?>
		<p class="ds-help"><?php esc_html_e( 'Compact reduces space while keeping text readable. Good for sidebars and footers. Gutenberg blocks can override the layout for each placement.', 'daily-scripture' ); ?></p></section>
		<section class="ds-panel" id="ds-type"><h2><?php esc_html_e( '3 · Typography with a steady rhythm', 'daily-scripture' ); ?></h2><p><?php esc_html_e( 'One slider keeps everything in proportion: move right for larger type, left for smaller. Headings, verses and notices scale together with balanced size ratios.', 'daily-scripture' ); ?></p>
		<?php $this->input( 'type_scale', __( 'Overall size · 100% is the recommended starting point', 'daily-scripture' ), $s, 'number' ); ?>
		<label class="ds-scale-label" for="ds-scale-slider"><?php esc_html_e( 'Try the overall size continuously', 'daily-scripture' ); ?></label><input id="ds-scale-slider" type="range" min="85" max="160" step="1" value="<?php echo esc_attr( (string) $s['type_scale'] ); ?>">
		<div class="ds-type-presets" aria-label="<?php esc_attr_e( 'Try font size', 'daily-scripture' ); ?>"><button type="button" class="button" data-ds-scale="90"><?php esc_html_e( 'A · Smaller', 'daily-scripture' ); ?></button><button type="button" class="button" data-ds-scale="100"><?php esc_html_e( 'Aa · Balanced', 'daily-scripture' ); ?></button><button type="button" class="button" data-ds-scale="120"><?php esc_html_e( 'Aa · Larger', 'daily-scripture' ); ?></button></div>
		<p class="ds-type-tip"><?php esc_html_e( 'Choose a size → check the preview → save at the top. At 120%, all type is one fifth larger. You can return to 100% at any time.', 'daily-scripture' ); ?></p>
		<?php $this->input( 'date_format', __( 'Custom date format (optional)', 'daily-scripture' ), $s ); ?>
		<p class="ds-help"><?php esc_html_e( 'Leave blank to use WordPress. Examples: m/d/Y or F j, Y. The preview shows the result.', 'daily-scripture' ); ?></p>
		<label class="ds-check"><input id="ds-expert-toggle" type="checkbox" name="daily_scripture_settings[expert_mode]" value="1" <?php checked( $s['expert_mode'] ); ?>> <?php esc_html_e( 'Expert mode: adjust individual sizes and colors', 'daily-scripture' ); ?></label>
		<div id="ds-expert-fields"><p class="ds-help"><?php esc_html_e( 'Set each text size separately. Blank fields keep your previous pixel size. Examples: 28px, 1.75rem, 1.2em, 110%, or var(--text-xxl, 28px). These values are also multiplied by the overall size. Blank color fields inherit the color scheme.', 'daily-scripture' ); ?></p>
		<?php
		foreach ( array(
			'heading'   => __( 'Heading', 'daily-scripture' ),
			'verse'     => __( 'Bible verse', 'daily-scripture' ),
			'reference' => __( 'Bible passage', 'daily-scripture' ),
			'meta'      => __( 'License & additional information', 'daily-scripture' ),
			'date'      => __( 'Date', 'daily-scripture' ),
		) as $part => $label ) :
			?>
		<fieldset class="ds-expert-row"><legend><?php echo esc_html( $label ); ?></legend><input type="hidden" name="daily_scripture_settings[size_<?php echo esc_attr( $part ); ?>]" value="<?php echo esc_attr( (string) $s[ 'size_' . $part ] ); ?>"><?php $this->input( 'font_' . $part, __( 'Size with unit or CSS variable', 'daily-scripture' ), $s ); ?><?php $this->input( 'color_' . $part, __( 'Color (#RRGGBB or blank)', 'daily-scripture' ), $s ); ?></fieldset>
		<?php endforeach; ?><div id="ds-font-help" class="ds-type-tip"><strong><?php esc_html_e( 'Which unit fits?', 'daily-scripture' ); ?></strong><ul><li><?php esc_html_e( 'px: a fixed starting size, the familiar choice.', 'daily-scripture' ); ?></li><li><?php esc_html_e( 'rem: follows your site’s base font size. At 16 px, 1.5rem = 24 px.', 'daily-scripture' ); ?></li><li><?php esc_html_e( 'em and %: refer to the inherited font size of the surrounding element.', 'daily-scripture' ); ?></li><li><?php esc_html_e( 'var(--text-xxl): uses an existing theme or builder CSS variable. Set a fallback with var(--text-xxl, 28px).', 'daily-scripture' ); ?></li></ul><p><?php esc_html_e( 'The CSS variable must contain a valid font size. Relative units adapt to their surroundings but do not automatically shrink at narrower widths. A responsive CSS variable can do that. The isolated preview does not load Bricks or framework variables and shows their fallback instead. Check these values on your site too. The dashboard widget uses its own font sizes.', 'daily-scripture' ); ?></p></div></div></section>
		<section class="ds-panel" id="ds-colors"><h2><?php esc_html_e( '4 · Colors with atmosphere', 'daily-scripture' ); ?></h2>
		<?php $this->select( 'theme', __( 'Site color scheme', 'daily-scripture' ), $choices['theme'], $s ); ?>
		<div class="ds-color-row">
		<?php
		foreach ( array(
			'color_background' => __( 'Background', 'daily-scripture' ),
			'color_text'       => __( 'Text', 'daily-scripture' ),
			'color_accent'     => __( 'Links & accent', 'daily-scripture' ),
		) as $key => $label ) {
			$this->input( $key, $label, $s, 'color' ); }
		?>
		</div>
		<p class="ds-help"><?php esc_html_e( 'Custom colors apply in the custom scheme. Automatic follows the device’s light or dark mode. The dashboard keeps the WordPress surface and your personal admin accent.', 'daily-scripture' ); ?></p></section>
		<section class="ds-panel" id="ds-dashboard"><h2><?php esc_html_e( '5 · Dashboard readings', 'daily-scripture' ); ?></h2>
		<label class="ds-check"><input type="checkbox" name="daily_scripture_settings[dashboard_widget]" value="1" <?php checked( $s['dashboard_widget'] ); ?>> <?php esc_html_e( 'Also display daily verses in the dashboard', 'daily-scripture' ); ?></label>
		<?php DashboardReadings::fields(); ?>
		<p class="ds-help"><?php esc_html_e( 'Save your settings first. Then choose your personal readings and their order in the dashboard widget.', 'daily-scripture' ); ?></p>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'index.php#daily_scripture_widget' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open dashboard (new tab)', 'daily-scripture' ); ?></a></p></section>
		<section class="ds-panel" id="ds-other"><h2><?php esc_html_e( '6 · Other settings & data cleanup', 'daily-scripture' ); ?></h2>
		<label class="ds-check"><input type="checkbox" name="daily_scripture_settings[remove_data_on_uninstall]" value="1" <?php checked( $s['remove_data_on_uninstall'] ); ?>> <?php esc_html_e( 'Delete this site’s settings, personal dashboard preferences, annual data and Bible editions when uninstalling', 'daily-scripture' ); ?></label>
		<p class="ds-help"><?php esc_html_e( 'Deactivation preserves data. Day and year changes follow the WordPress timezone; import new years on the data sources page.', 'daily-scripture' ); ?></p></section>
		</form>
		<aside class="ds-preview-panel" aria-label="<?php esc_attr_e( 'Live preview', 'daily-scripture' ); ?>"><h2><?php esc_html_e( 'How your verses look', 'daily-scripture' ); ?></h2>
		<p><?php esc_html_e( 'Today’s original data, when available. Otherwise a clearly labeled design sample.', 'daily-scripture' ); ?></p>
		<div class="ds-preview-controls"><label for="ds-preview-width"><?php esc_html_e( 'Width', 'daily-scripture' ); ?></label><select id="ds-preview-width"><option value="wide"><?php esc_html_e( 'Available space', 'daily-scripture' ); ?></option><option value="390"><?php esc_html_e( 'Mobile · 390 px', 'daily-scripture' ); ?></option><option value="300"><?php esc_html_e( 'Sidebar · 300 px', 'daily-scripture' ); ?></option></select>
		<label for="ds-preview-context"><?php esc_html_e( 'Context', 'daily-scripture' ); ?></label><select id="ds-preview-context"><option value="frontend"><?php esc_html_e( 'Site', 'daily-scripture' ); ?></option><option value="dashboard"><?php esc_html_e( 'Dashboard colors', 'daily-scripture' ); ?></option></select></div>
		<label class="ds-check"><input id="ds-preview-stress" type="checkbox"> <?php esc_html_e( 'Simulate strict theme rules', 'daily-scripture' ); ?></label>
		<p class="ds-help"><?php esc_html_e( 'The preview shows the plugin’s design. Your theme may affect the available width. The simulation checks common spacing problems. The dashboard preview shows admin colors; choose your personal reading size in the widget itself.', 'daily-scripture' ); ?></p>
		<div class="ds-preview-viewport"><iframe id="ds-preview-frame" title="<?php esc_attr_e( 'Daily verse preview', 'daily-scripture' ); ?>" sandbox="allow-scripts" referrerpolicy="no-referrer"></iframe></div>
		<p id="ds-preview-status" role="status" aria-live="polite"></p><button type="button" class="button" id="ds-preview-refresh"><?php esc_html_e( 'Refresh preview', 'daily-scripture' ); ?></button>
		<noscript><p><?php esc_html_e( 'Live preview and JSON import require JavaScript. Settings can also be saved without JavaScript.', 'daily-scripture' ); ?></p></noscript>
		</aside></div>
		<section class="ds-panel" id="ds-transfer"><h2><?php esc_html_e( '7 · Transfer, back up, reuse', 'daily-scripture' ); ?></h2><p><?php esc_html_e( 'A design includes layout, colors, font sizes and date format. A full backup also includes the source selection and other site settings. Bible texts, annual packages, personal dashboard preferences and builder styles are not included.', 'daily-scripture' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="daily_scripture_export_settings"><?php wp_nonce_field( 'daily_scripture_export_settings' ); ?>
		<button class="button" name="kind" value="design"><?php esc_html_e( 'Saved design as JSON', 'daily-scripture' ); ?></button> <button class="button" name="kind" value="settings"><?php esc_html_e( 'All saved settings as JSON', 'daily-scripture' ); ?></button></form>
		<div class="ds-import"><label for="ds-settings-file"><?php esc_html_e( 'Import JSON file (up to 64 KiB)', 'daily-scripture' ); ?></label> <input id="ds-settings-file" type="file" accept=".json,application/json"><button id="ds-import-settings" type="button" class="button"><?php esc_html_e( 'Validate & load into form', 'daily-scripture' ); ?></button><p id="ds-import-status" role="status" aria-live="polite"></p>
		<p class="ds-help"><?php esc_html_e( 'Imports load into the form first. Review them before saving. Full imports can also change the uninstall deletion option.', 'daily-scripture' ); ?></p></div></section><?php PageChrome::footer(); ?></div>
		<?php
	}
}
