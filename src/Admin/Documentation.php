<?php
/**
 * Accessible help and copyable shortcode documentation.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

use Deckerweb\DailyScripture\Bible\TranslationManager;
use Deckerweb\DailyScripture\Core\Presentation;

defined( 'ABSPATH' ) || exit;

/** Read-only documentation, independent of stored verse data. */
final class Documentation {
	/**
	 * Load documentation assets only on their own admin pages.
	 *
	 * @param string $hook Current admin screen.
	 * @return void
	 */
	public function assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'daily-scripture_page_daily-scripture-help', 'daily-scripture_page_daily-scripture-shortcodes' ), true ) ) {
			return; }
		wp_enqueue_style( 'daily-scripture-docs', DAILY_SCRIPTURE_URL . 'assets/css/docs.css', array(), DAILY_SCRIPTURE_VERSION );
		wp_enqueue_script( 'daily-scripture-docs', DAILY_SCRIPTURE_URL . 'assets/docs.js', array(), DAILY_SCRIPTURE_VERSION, true );
		wp_localize_script(
			'daily-scripture-docs',
			'dailyScriptureDocs',
			array(
				'copied' => __( 'Copied, ready to paste!', 'daily-scripture' ),
				'failed' => __( 'Please copy the highlighted shortcode with Ctrl+C or ⌘C.', 'daily-scripture' ),
			)
		);
	}
	/**
	 * Open a consistent documentation page.
	 *
	 * @param string $page Current page slug.
	 * @param string $title Page title.
	 * @param string $intro Introductory text.
	 * @return void
	 */
	private function start( string $page, string $title, string $intro ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'daily-scripture' ), '', array( 'response' => 403 ) ); }
		echo '<div class="wrap ds-docs ds-admin">';
		PageChrome::header( $page, $title, $intro );
	}
	/**
	 * Focused instructions and troubleshooting without shortcode listings.
	 *
	 * @return void
	 */
	public function help(): void {
		$this->start( 'daily-scripture-help', __( 'Help, tips & ideas', 'daily-scripture' ), __( 'Set it up once and enjoy daily readings. Find the steps for your daily verse display here.', 'daily-scripture' ) );
		$cards = array(
			array( __( 'Ready in three steps', 'daily-scripture' ), __( '1. Check the download source and import an annual package under Data sources, or upload an official annual file. 2. Choose a layout under Design. 3. Insert the “Daily Scripture” block or builder element. The dashboard uses the same daily data.', 'daily-scripture' ) ),
			array( __( 'Your selected Bible passage', 'daily-scripture' ), __( 'Install an edition in the Bible library first. Then insert “Bible passage · Daily Scripture” in Gutenberg, Elementor or Bricks. Choose edition, book, chapter and verse range. Up to 50 verses within one chapter are supported. A blank heading displays the Bible reference.', 'daily-scripture' ) ),
			array( __( 'Design with confidence', 'daily-scripture' ), __( 'Start with a layout and adjust the overall size to keep type balanced. Use compact spacing for sidebars and footers. Check light and dark surfaces in the preview. Expert mode lets you fine-tune individual text areas.', 'daily-scripture' ) ),
			array( __( 'Design with Elementor and Bricks', 'daily-scripture' ), __( 'Elementor and Bricks have native typography, color, border and spacing controls. Blank values inherit plugin defaults; custom values affect only this element. Use device previews for responsive settings. Set outer margins in the general Layout or Advanced section.', 'daily-scripture' ) ),
			array( __( 'The right verses each morning', 'daily-scripture' ), __( 'Dates and year changes follow the WordPress timezone. Check that next year is installed in time. Refresh page and CDN caches at the day change. Missing data displays a notice rather than old verses.', 'daily-scripture' ) ),
			array( __( 'Plugin updates through GitHub', 'daily-scripture' ), __( 'Daily Scripture uses the deckerweb Updater. New public GitHub releases appear in standard WordPress update management. Automatic updates are not enabled automatically. Successful checks are cached for up to 30 minutes, failures for 10 minutes. If no release is available, the installed version stays usable; manual ZIP updates remain possible.', 'daily-scripture' ) ),
			array( __( 'Texts with attribution', 'daily-scripture' ), __( 'Losung and Lehrtext belong together and remain unchanged. Source and license notices stay accessible. Observe the edition’s terms. The freely licensed Bible library is independent of official daily readings. Bibleserver is linked only; its target translation can be chosen separately.', 'daily-scripture' ) ),
		);
		echo '<div class="ds-docs-grid">';
		foreach ( $cards as $card ) {
			echo '<section class="ds-docs-card"><h2>' . esc_html( $card[0] ) . '</h2><p>' . esc_html( $card[1] ) . '</p></section>'; }
		echo '</div><h2>' . esc_html__( 'Troubleshooting', 'daily-scripture' ) . '</h2>';
		foreach ( array(
			__( 'The download is unavailable', 'daily-scripture' ) => __( 'Check the source later or manually upload the linked original package. For certificate errors, your hosting provider can check the secure connection. A failed import preserves existing data.', 'daily-scripture' ),
			__( 'The Bible passage does not appear', 'daily-scripture' ) => __( 'Is the selected edition installed? Are the chapter and verse range correct? Verse numbering varies. In Menge, select Ezekiel 33:14–15 together. The displayed notice helps identify the issue.', 'daily-scripture' ),
			__( 'The editor looks different after an update', 'daily-scripture' ) => __( 'Reload the editor completely and clear browser and builder caches if needed. Then check the preview on the site. Normal updates preserve saved blocks and settings.', 'daily-scripture' ),
			__( 'Reuse a design', 'daily-scripture' ) => __( 'Export your design or plugin settings as JSON in the design section. Save builder styles with the builder’s own templates. JSON settings do not include Bible texts or annual packages.', 'daily-scripture' ),
		) as $question => $answer ) {
			echo '<details class="ds-docs-card"><summary>' . esc_html( $question ) . '</summary><p>' . esc_html( $answer ) . '</p></details>'; }
		PageChrome::footer();
		echo '</div>';
	}
	/**
	 * Render an accessible copy target with a no-JavaScript selection fallback.
	 *
	 * @param string $title Example label.
	 * @param string $code Literal shortcode.
	 * @param string $description Example explanation.
	 * @return void
	 */
	private function example( string $title, string $code, string $description ): void {
		$id = wp_unique_id( 'ds-shortcode-' );
		echo '<section class="ds-docs-card"><h3><label for="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</label></h3><p>' . esc_html( $description ) . '</p><textarea rows="3" readonly spellcheck="false" id="' . esc_attr( $id ) . '">' . esc_textarea( $code ) . '</textarea><button type="button" class="button ds-copy" data-copy-target="' . esc_attr( $id ) . '" hidden><span class="dashicons dashicons-admin-page" aria-hidden="true"></span> ' . esc_html__( 'Copy', 'daily-scripture' ) . '<span class="screen-reader-text">: ' . esc_html( $title ) . '</span></button><span class="ds-copy-status" role="status" aria-live="polite"></span></section>';
	}
	/**
	 * List complete supported shortcode syntax, examples and value references.
	 *
	 * @return void
	 */
	public function shortcodes(): void {
		$this->start( 'daily-scripture-shortcodes', __( 'Shortcodes to copy', 'daily-scripture' ), __( 'Paste the code into a WordPress Shortcode block or your builder’s shortcode element. You can also select and copy it directly in the field.', 'daily-scripture' ) );
		echo '<h2>' . esc_html__( 'Daily verses', 'daily-scripture' ) . '</h2><div class="ds-docs-grid">';
		$this->example( __( 'The basic form', 'daily-scripture' ), '[daily_scripture]', __( 'Uses the source and presentation from your plugin settings.', 'daily-scripture' ) );
		$this->example( __( 'All daily verse options', 'daily-scripture' ), '[daily_scripture source="both" layout="card" density="standard" theme="light"]', __( 'Choose a source and presentation for this output.', 'daily-scripture' ) );
		$this->example( __( 'Compact Die Losungen', 'daily-scripture' ), '[daily_scripture source="herrnhuter" density="compact"]', __( 'Losung and Lehrtext together with less spacing, for example in a sidebar.', 'daily-scripture' ) );
		$this->example( __( 'The Word for Today in dark mode', 'daily-scripture' ), '[daily_scripture source="bible2" theme="dark"]', __( 'Bible 2.0 with a dark color scheme. Inherits the global heading.', 'daily-scripture' ) );
		echo '</div><h2>' . esc_html__( 'Selected Bible passages', 'daily-scripture' ) . '</h2><p>' . esc_html__( 'The selected edition must be installed in the Bible library. Up to 50 verses within one chapter.', 'daily-scripture' ) . '</p><div class="ds-docs-grid">';
		$this->example( __( 'The basic passage form', 'daily-scripture' ), '[daily_scripture_passage]', __( 'Displays John 3:16 from Luther 1912 with your global design.', 'daily-scripture' ) );
		$this->example( __( 'All passage options', 'daily-scripture' ), '[daily_scripture_passage translation="luther-1912" book="JOH" chapter="3" from="16" to="17" title="Ein Wort für dich" layout="card" density="standard" theme="light"]', __( 'Choose an edition, passage, heading and presentation.', 'daily-scripture' ) );
		$this->example( __( 'Psalm 23 from Elberfelder 1905', 'daily-scripture' ), '[daily_scripture_passage translation="elberfelder-1905" book="PSA" chapter="23" from="1" to="6"]', __( 'Without a custom title, the Bible reference is used as the heading.', 'daily-scripture' ) );
		$this->example( __( 'Schlachter 1951 with attribution', 'daily-scripture' ), '[daily_scripture_passage translation="schlachter-1951" book="JOH" chapter="3" from="16" to="17"]', __( 'Freely usable under CC BY 4.0. Author, source and license notices appear automatically in the output.', 'daily-scripture' ) );
		$this->example( __( 'Menge 1939 in the footer', 'daily-scripture' ), '[daily_scripture_passage translation="menge-1939" book="ROM" chapter="12" from="12" density="compact" theme="dark"]', __( 'Without “to”, only the verse selected with “from” is displayed.', 'daily-scripture' ) );
		echo '</div><h2>' . esc_html__( 'Parameters at a glance', 'daily-scripture' ) . '</h2><div class="ds-docs-table"><table class="widefat striped"><thead><tr><th>' . esc_html__( 'Parameter', 'daily-scripture' ) . '</th><th>' . esc_html__( 'Values and effects', 'daily-scripture' ) . '</th></tr></thead><tbody>';
		$rows = array(
			'source'                     => __( 'Daily verses only: herrnhuter = Die Losungen, bible2 = Bible 2.0, both = both sources. Without a value, the plugin setting applies.', 'daily-scripture' ),
			'translation'                => __( 'Passages only: luther-1912 (default), elberfelder-1905, menge-1939, or schlachter-1951 (CC BY 4.0).', 'daily-scripture' ),
			'book / chapter / from / to' => __( 'Passages only: book code, chapter, first and last verse. Default: JOH / 3 / 16 / 16. Without “to”, the last verse equals the first.', 'daily-scripture' ),
			'title'                      => __( 'Passages only: custom heading up to 160 characters. Blank displays the Bible reference. Daily verses inherit global source headings.', 'daily-scripture' ),
			'density'                    => __( 'standard or compact. Without a value, the site setting applies.', 'daily-scripture' ),
			'theme'                      => __( 'light, dark, auto (device setting), or custom (custom colors). Without a value, the site setting applies.', 'daily-scripture' ),
			'layout'                     => implode(
				', ',
				array_map( /**
							* Format one layout choice for copyable shortcode guidance.
							*
							* @param string $key Stable layout identifier.
							* @param string $label Localized layout label.
							* @return string
							*/
					static fn( $key, $label ) => $key . ' = ' . $label,
					array_keys( Presentation::choices()['layout'] ),
					Presentation::choices()['layout']
				)
			),
		);
		foreach ( $rows as $key => $value ) {
			echo '<tr><th scope="row"><code>' . esc_html( $key ) . '</code></th><td>' . esc_html( $value ) . '</td></tr>'; }
		echo '</tbody></table></div><details class="ds-docs-card"><summary>' . esc_html__( 'Look up all book codes', 'daily-scripture' ) . '</summary><dl class="ds-docs-books">';
		foreach ( TranslationManager::books() as $key => $label ) {
			echo '<div><dt><code>' . esc_html( $key ) . '</code></dt><dd>' . esc_html( $label ) . '</dd></div>'; }
		echo '</dl></details><p>' . esc_html__( 'Tip: omit presentation options to inherit later changes to your global design.', 'daily-scripture' ) . '</p>';
		PageChrome::footer();
		echo '</div>';
	}
}
