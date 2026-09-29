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
				'copied' => __( 'Kopiert – bereit zum Einfügen!', 'daily-scripture' ),
				'failed' => __( 'Bitte den markierten Shortcode mit Strg+C oder ⌘C kopieren.', 'daily-scripture' ),
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
			wp_die( esc_html__( 'Keine Berechtigung für diese Aktion.', 'daily-scripture' ), '', array( 'response' => 403 ) ); }
		echo '<div class="wrap ds-docs ds-admin">';
		PageChrome::header( $page, $title, $intro );
	}
	/**
	 * Focused instructions and troubleshooting without shortcode listings.
	 *
	 * @return void
	 */
	public function help(): void {
		$this->start( 'daily-scripture-help', __( 'Hilfe, Tipps & gute Ideen', 'daily-scripture' ), __( 'Einmal einrichten, jeden Tag freuen. Hier findest du die passenden Handgriffe für deine tägliche Versausgabe.', 'daily-scripture' ) );
		$cards = array(
			array( __( 'In drei Schritten startklar', 'daily-scripture' ), __( '1. Unter Datenquellen die Downloadquelle prüfen und das gewünschte Jahrespaket importieren. Alternativ eine offizielle Jahresdatei hochladen. 2. Unter Gestaltung dein Layout wählen. 3. Den Block oder das Builder-Element „Daily Scripture“ einfügen. Das Dashboard verwendet dieselben Tagesdaten.', 'daily-scripture' ) ),
			array( __( 'Deine eigene Bibelstelle', 'daily-scripture' ), __( 'Installiere zuerst eine Übersetzung in der Bibelbibliothek. Füge anschließend „Bibelstelle · Daily Scripture“ in Gutenberg, Elementor oder Bricks ein. Wähle Übersetzung, Buch, Kapitel und Versbereich. Bis zu 50 Verse innerhalb eines Kapitels sind möglich. Eine leere Überschrift zeigt die Bibelstellenangabe.', 'daily-scripture' ) ),
			array( __( 'Gestalten mit gutem Gefühl', 'daily-scripture' ), __( 'Beginne mit einem Layout und passe die Gesamtgröße an: So bleibt die Schrift ausgewogen. Für Seitenleiste und Fußbereich eignet sich die kompakte Ansicht. Prüfe helle und dunkle Flächen in der Vorschau. Im Expertenmodus kannst du die einzelnen Textbereiche genauer abstimmen.', 'daily-scripture' ) ),
			array( __( 'Gestalten mit Elementor und Bricks', 'daily-scripture' ), __( 'Elementor und Bricks bieten eigene Regler für Schriften, Farben, Rahmen und Abstände. Leere Werte folgen den Plugin-Vorgaben; eigene Werte gelten nur für dieses Element. Nutze die Geräteansichten für responsive Anpassungen. Äußere Abstände stellst du im allgemeinen Layout- oder Erweitert-Bereich ein.', 'daily-scripture' ) ),
			array( __( 'Jeden Morgen die richtigen Verse', 'daily-scripture' ), __( 'Datum und Jahreswechsel folgen der WordPress-Zeitzone. Prüfe rechtzeitig, ob das nächste Jahr installiert ist. Leere Seiten- und CDN-Caches zum Tageswechsel. Fehlende Daten werden als Hinweis angezeigt, nicht durch alte Verse ersetzt.', 'daily-scripture' ) ),
			array( __( 'Plugin-Updates über GitHub', 'daily-scripture' ), __( 'Daily Scripture verwendet den deckerweb-Updater. Neue öffentliche GitHub-Releases erscheinen in der normalen WordPress-Updateverwaltung. Automatische Updates werden nicht von selbst aktiviert. Prüfungen werden bis zu 30 Minuten zwischengespeichert, Fehler für 10 Minuten. Ohne verfügbares Release bleibt die installierte Version nutzbar; ein ZIP-Update ist weiterhin möglich.', 'daily-scripture' ) ),
			array( __( 'Texte mit Herkunft', 'daily-scripture' ), __( 'Losung und Lehrtext gehören zusammen und bleiben unverändert. Quellen- und Lizenzhinweise bleiben zugänglich. Beachte die Bedingungen der jeweiligen Ausgabe. Die freie Bibelbibliothek ist unabhängig von den offiziellen Tagesversen. Bibleserver wird nur verlinkt; seine Zielübersetzung lässt sich separat wählen.', 'daily-scripture' ) ),
		);
		echo '<div class="ds-docs-grid">';
		foreach ( $cards as $card ) {
			echo '<section class="ds-docs-card"><h2>' . esc_html( $card[0] ) . '</h2><p>' . esc_html( $card[1] ) . '</p></section>'; }
		echo '</div><h2>' . esc_html__( 'Wenn etwas hakt', 'daily-scripture' ) . '</h2>';
		foreach ( array(
			__( 'Der Download ist nicht verfügbar', 'daily-scripture' ) => __( 'Prüfe die Quelle später erneut oder verwende den manuellen Upload des verlinkten Originalpakets. Bei Zertifikatsfehlern kann dein Hosting die sichere Verbindung prüfen. Vorhandene Daten bleiben bei einem fehlgeschlagenen Import erhalten.', 'daily-scripture' ),
			__( 'Die Bibelstelle erscheint nicht', 'daily-scripture' ) => __( 'Ist die gewählte Übersetzung installiert? Stimmen Kapitel und Versbereich? Verszählungen unterscheiden sich. In der Menge-Ausgabe müssen Hesekiel 33,14–15 gemeinsam gewählt werden. Der angezeigte Hinweis hilft dir weiter.', 'daily-scripture' ),
			__( 'Nach einem Update sieht der Editor anders aus', 'daily-scripture' ) => __( 'Lade den Editor vollständig neu und leere gegebenenfalls Browser- und Builder-Caches. Prüfe anschließend die Vorschau auf der Website. Gespeicherte Blöcke und Einstellungen bleiben bei einem normalen Update erhalten.', 'daily-scripture' ),
			__( 'Ein gelungenes Design wiederverwenden', 'daily-scripture' ) => __( 'Exportiere dein Design oder die Plugin-Einstellungen im Gestaltungsbereich als JSON. Builder-Stile speicherst du mit den Vorlagen des jeweiligen Builders. Die JSON-Einstellungen enthalten keine Bibeltexte oder Jahrespakete.', 'daily-scripture' ),
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
		echo '<section class="ds-docs-card"><h3><label for="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</label></h3><p>' . esc_html( $description ) . '</p><textarea rows="3" readonly spellcheck="false" id="' . esc_attr( $id ) . '">' . esc_textarea( $code ) . '</textarea><button type="button" class="button ds-copy" data-copy-target="' . esc_attr( $id ) . '" hidden><span class="dashicons dashicons-admin-page" aria-hidden="true"></span> ' . esc_html__( 'Kopieren', 'daily-scripture' ) . '<span class="screen-reader-text">: ' . esc_html( $title ) . '</span></button><span class="ds-copy-status" role="status" aria-live="polite"></span></section>';
	}
	/**
	 * List complete supported shortcode syntax, examples and value references.
	 *
	 * @return void
	 */
	public function shortcodes(): void {
		$this->start( 'daily-scripture-shortcodes', __( 'Shortcodes zum Kopieren', 'daily-scripture' ), __( 'Füge den kopierten Code in einen WordPress-Shortcode-Block oder das Shortcode-Element deines Builders ein. Du kannst ihn auch direkt im Feld markieren und kopieren.', 'daily-scripture' ) );
		echo '<h2>' . esc_html__( 'Tägliche Verse', 'daily-scripture' ) . '</h2><div class="ds-docs-grid">';
		$this->example( __( 'Die einfache Grundform', 'daily-scripture' ), '[daily_scripture]', __( 'Verwendet die Quelle und Gestaltung aus deinen Plugin-Einstellungen.', 'daily-scripture' ) );
		$this->example( __( 'Alle Optionen für Tagesverse', 'daily-scripture' ), '[daily_scripture source="both" layout="card" density="standard" theme="light"]', __( 'Quelle und Darstellung gezielt für diese Ausgabe festlegen.', 'daily-scripture' ) );
		$this->example( __( 'Die Losungen kompakt', 'daily-scripture' ), '[daily_scripture source="herrnhuter" density="compact"]', __( 'Losung und Lehrtext zusammen, mit weniger Abstand – etwa für die Seitenleiste.', 'daily-scripture' ) );
		$this->example( __( 'Das Wort für heute in Dunkel', 'daily-scripture' ), '[daily_scripture source="bible2" theme="dark"]', __( 'Bible 2.0 mit dunklem Farbschema. Die globale Überschrift wird übernommen.', 'daily-scripture' ) );
		echo '</div><h2>' . esc_html__( 'Eigene Bibelstellen', 'daily-scripture' ) . '</h2><p>' . esc_html__( 'Voraussetzung: Die gewünschte Ausgabe ist in der Bibelbibliothek installiert. Maximal 50 Verse innerhalb eines Kapitels.', 'daily-scripture' ) . '</p><div class="ds-docs-grid">';
		$this->example( __( 'Die einfache Bibelstellen-Grundform', 'daily-scripture' ), '[daily_scripture_passage]', __( 'Zeigt Johannes 3,16 aus Luther 1912 mit deiner globalen Gestaltung.', 'daily-scripture' ) );
		$this->example( __( 'Alle Optionen für eine Bibelstelle', 'daily-scripture' ), '[daily_scripture_passage translation="luther-1912" book="JOH" chapter="3" from="16" to="17" title="Ein Wort für dich" layout="card" density="standard" theme="light"]', __( 'Passe Übersetzung, Stelle, Überschrift und Darstellung nach deinen Wünschen an.', 'daily-scripture' ) );
		$this->example( __( 'Psalm 23 aus Elberfelder 1905', 'daily-scripture' ), '[daily_scripture_passage translation="elberfelder-1905" book="PSA" chapter="23" from="1" to="6"]', __( 'Ohne eigenen Titel erscheint die Bibelstelle als Überschrift.', 'daily-scripture' ) );
		$this->example( __( 'Schlachter 1951 mit Quellenhinweis', 'daily-scripture' ), '[daily_scripture_passage translation="schlachter-1951" book="JOH" chapter="3" from="16" to="17"]', __( 'Frei nutzbar unter CC BY 4.0. Urheber-, Quellen- und Lizenzhinweise erscheinen automatisch bei der Ausgabe.', 'daily-scripture' ) );
		$this->example( __( 'Menge 1939 im Fußbereich', 'daily-scripture' ), '[daily_scripture_passage translation="menge-1939" book="ROM" chapter="12" from="12" density="compact" theme="dark"]', __( 'Ohne „to“ wird nur der unter „from“ gewählte Vers angezeigt.', 'daily-scripture' ) );
		echo '</div><h2>' . esc_html__( 'Parameter auf einen Blick', 'daily-scripture' ) . '</h2><div class="ds-docs-table"><table class="widefat striped"><thead><tr><th>' . esc_html__( 'Parameter', 'daily-scripture' ) . '</th><th>' . esc_html__( 'Werte und Wirkung', 'daily-scripture' ) . '</th></tr></thead><tbody>';
		$rows = array(
			'source'                     => __( 'Nur Tagesverse: herrnhuter = Die Losungen, bible2 = Bible 2.0, both = beide Quellen. Ohne Angabe gilt die Plugin-Einstellung.', 'daily-scripture' ),
			'translation'                => __( 'Nur Bibelstellen: luther-1912 (Standard), elberfelder-1905, menge-1939 oder schlachter-1951 (CC BY 4.0).', 'daily-scripture' ),
			'book / chapter / from / to' => __( 'Nur Bibelstellen: Buchkürzel, Kapitel, erster und letzter Vers. Standard: JOH / 3 / 16 / 16. Ohne „to“ entspricht der letzte dem ersten Vers.', 'daily-scripture' ),
			'title'                      => __( 'Nur Bibelstellen: eigene Überschrift bis 160 Zeichen. Leer zeigt die Bibelstellenangabe. Tagesverse übernehmen die globalen Quellenüberschriften.', 'daily-scripture' ),
			'density'                    => __( 'standard oder compact. Ohne Angabe gilt die Website-Einstellung.', 'daily-scripture' ),
			'theme'                      => __( 'light = hell, dark = dunkel, auto = Geräteeinstellung, custom = eigene Farben. Ohne Angabe gilt die Website-Einstellung.', 'daily-scripture' ),
			'layout'                     => implode( ', ', array_map( static fn( $key, $label ) => $key . ' = ' . $label, array_keys( Presentation::choices()['layout'] ), Presentation::choices()['layout'] ) ),
		);
		foreach ( $rows as $key => $value ) {
			echo '<tr><th scope="row"><code>' . esc_html( $key ) . '</code></th><td>' . esc_html( $value ) . '</td></tr>'; }
		echo '</tbody></table></div><details class="ds-docs-card"><summary>' . esc_html__( 'Alle Buchkürzel nachschlagen', 'daily-scripture' ) . '</summary><dl class="ds-docs-books">';
		foreach ( TranslationManager::books() as $key => $label ) {
			echo '<div><dt><code>' . esc_html( $key ) . '</code></dt><dd>' . esc_html( $label ) . '</dd></div>'; }
		echo '</dl></details><p>' . esc_html__( 'Tipp: Lasse Darstellungsoptionen weg, wenn spätere Änderungen deiner globalen Gestaltung auch hier gelten sollen.', 'daily-scripture' ) . '</p>';
		PageChrome::footer();
		echo '</div>';
	}
}
