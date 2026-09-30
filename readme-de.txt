=== Daily Scripture ===
Contributors: deckerweb
Tags: bible, scripture, daily, shortcode, block
Requires at least: 6.6
Requires PHP: 8.0
Stable tag: 0.16.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Tägliche Verse und eigene Bibelstellen. Lokal gespeichert, mit Vorschau, zehn Layouts, Gutenberg, Elementor, Bricks und kompaktem Dashboard.

== Description ==

Worte, die den Tag erhellen. Die Losungen, „Das Wort für heute“ von Bible 2.0 und selbst gewählte Bibelstellen: Daily Scripture bringt tägliche Lesungen auf deine WordPress-Website. Wähle ein Layout, passe die Schrift an und prüfe die Vorschau. Die Texte liegen lokal, die Gestaltung passt zu dir.

Version: 0.16.2 · Voraussetzungen: WordPress 6.6+ / PHP 8.0+ · Lizenz: GPL-2.0-or-later

Download (https://github.com/deckerweb/daily-scripture/releases/latest) · Anleitung (https://github.com/deckerweb/daily-scripture/wiki/Deutsch) · English (https://github.com/deckerweb/daily-scripture/blob/main/README.md)

== Inhaltsverzeichnis ==

* Auf einen Blick
* Installation und erste Verse
* Tagesverse und Bibelbibliothek
* Layouts und Schriftgrößen
* Editoren, Shortcodes und Dashboard
* Daten und Einstellungstransfer
* Updates und Dokumentation
* Häufige Fragen
* Changelog
* Über das Plugin

== Auf einen Blick ==

* Jeden Tag ein Wort: offizielle Verspaare der Losungen und von Bible 2.0, als Jahrespaket herunterladen oder manuell hochladen.
* Eigene Bibelstellen auswählen: eine unabhängige lokale Bibliothek mit Luther 1912, Elberfelder 1905, Menge 1939 und Schlachter 1951.
* Passend zu deiner Website: zehn Layouts, helle und dunkle Ansichten, kompakte Abstände, eigene Farben und eine Vorschau in mehreren Breiten.
* Ausgewogen lesen: eine gemeinsame Schriftgrößen-Skala, bei Bedarf ergänzt um Expertenwerte in px, em, rem, %, oder CSS-Variablen.
* Im vertrauten Editor arbeiten: Gutenberg-Blöcke, Elementor-Widgets und Bricks-Elemente für Tagesverse und eigene Bibelstellen; zwei Shortcodes funktionieren ohne Builder.
* Ein kleiner Begleiter im Alltag: Dashboard-Widget mit persönlichen Schriftgrößen und Abständen, eigenen Quellenüberschriften und der WordPress-Adminfarbe.
* Für weitere Projekte vorbereitet: geprüfter JSON-Einstellungstransfer, lokale Textspeicherung und Updates über das reguläre WordPress-Updatesystem.

== Installation und erste Verse ==

1. Lade das Plugin-ZIP aus den GitHub-Releases (https://github.com/deckerweb/daily-scripture/releases/latest).
2. Installiere es über Plugins → Plugin hinzufügen → Plugin hochladen und aktiviere es.
3. Öffne Daily Scripture → Datenquellen, prüfe die Verfügbarkeit und importiere ein offizielles Jahrespaket. Alternativ kannst du es manuell hochladen.
4. Wähle unter Gestaltung ein Layout, passe die Gesamtgröße an und prüfe die Vorschau. Speichere mit dem Button oben.
5. Füge den Block, das Builder-Element oder den Shortcode Daily Scripture ein. Für eigene Bibelstellen installierst du vorher eine Ausgabe unter Bibelbibliothek.

Vorausgesetzt werden WordPress 6.6+, PHP 8.0+ und DOM/XML. ZIP-Pakete benötigen ZipArchive. Elementor und Bricks sind optional. Der Pluginname bleibt auch auf Deutsch Daily Scripture.

== Tagesverse und Bibelbibliothek ==

Tagesverse und selbst gewählte Bibelstellen sind getrennte Ausgaben. Das offizielle Losungen-Paar bleibt zusammen und unverändert; die Wahl einer lokalen Bibelausgabe schreibt diese Texte nicht um. Bible 2.0 verwendet die Texte und Hinweise des importierten Jahrespakets.

In der unabhängigen Bibelbibliothek wählst du Übersetzung, Buch, Kapitel und bis zu 50 Verse innerhalb eines Kapitels. Die Volltexte werden separat installiert. Luther 1912, Elberfelder 1905 und Menge 1939 verwenden die geprüften gemeinfreien Quellen; die verwendete Schlachter-1951-Quelle steht unter CC BY 4.0. Quellen- und Lizenzhinweise bleiben zugänglich.

Bibleserver wird nur für Bibelstellen-Links verwendet. Die separat gewählte Zielübersetzung verändert das Linkziel, nicht den auf deiner Website angezeigten Text.

== Layouts und Schriftgrößen ==

Beginne mit einem Layout und dem Regler für die Gesamtgröße: Überschrift, Vers, Bibelstelle, Datum und Quellenhinweise bleiben im Verhältnis zueinander. Wähle normale oder kompakte Abstände und anschließend helle, dunkle, geräteabhängige oder eigene Farben. Der Expertenmodus ergänzt individuelle Schriftgrößen und Farben.

Vorhandene CSS-Variablen wie var(--text-xxl, 28px) sind möglich. Gib einen Ersatzwert an: Die abgeschirmte Adminvorschau kann die Variablen deines Themes oder Builders nicht einlesen. Prüfe breite und schmale Ansichten und danach die veröffentlichte Seite. Der Vers steht im Mittelpunkt, mit klarer Überschrift und dezenteren Bibelstellen- und Lizenzangaben.

== Editoren, Shortcodes und Dashboard ==

Gutenberg zeigt die Einstellungen in der Seitenleiste und eine serverseitig erzeugte Vorschau im Block. Blöcke können die gespeicherten Vorgaben übernehmen oder eigene Überschriften und Darstellungsoptionen verwenden. Elementor und Bricks bieten zusätzlich native Regler für Schriften, Farben, Rahmen und Abstände sowie responsive Werte.

[daily_scripture] gibt Tagesverse aus, [daily_scripture_passage] eine selbst gewählte Bibelstelle. Auf der Pluginseite Shortcodes findest du Beispiele mit Kopierbuttons und alle Buchkürzel.

Im Dashboard kann jeder Benutzer über Ansicht anpassen kompakte Abstände und eine Versgröße von 14, 16 oder 18 px wählen. Das gilt nur für diesen Benutzer auf dieser Website. Quellenüberschriften und das Standard-Datumsformat folgen den Website-Einstellungen.

== Daten und Einstellungstransfer ==

Du startest die Jahresdownloads selbst. Die Quellenprüfung zeigt verfügbare Pakete, installiert aber nicht automatisch das Folgejahr. Der Import prüft vollständige Jahre und Verspaare vor dem Ersetzen lokaler Daten. Installiere das nächste Jahr rechtzeitig und aktualisiere Seiten- oder CDN-Caches zum Tageswechsel.

Alle verwalteten Textdateien bleiben unter wp-content/uploads/daily-scripture/. JSON-Exporte enthalten die gespeicherte Gestaltung oder Website-Einstellungen, aber keine Bibeltexte, Jahrespakete, persönlichen Dashboard-Werte oder Builder-Stile. Importierte Einstellungen erscheinen zunächst zur Prüfung im Formular und gelten nach dem Speichern. Nimm die Textdateien in dein normales Website-Backup auf.

== Updates und Dokumentation ==

Updates kommen direkt aus dem DECKERWEB-Repository auf GitHub (https://github.com/deckerweb/daily-scripture/releases) und erscheinen bei aktivem Daily Scripture im regulären WordPress-Updatesystem. Aktualisiere wie gewohnt über Plugins oder Dashboard → Aktualisierungen; ein zusätzliches Updater-Plugin ist nicht nötig. Automatische Updates bleiben deine Entscheidung.

Die deutsche Anleitung (https://github.com/deckerweb/daily-scripture/wiki/Deutsch), die englische Anleitung (https://github.com/deckerweb/daily-scripture/wiki/English) und die thematischen FAQs erklären Einstellungen, Quellen und häufige Fragen. Eine lokale Kopie (https://github.com/deckerweb/daily-scripture/blob/main/docs/Deutsch.md) liegt im Plugin. Im Admin-Footer findest du die Dokumentation und den Changelog-Dialog. Zwischengespeicherte Updateprüfungen können ein neues Angebot bis zu 30 Minuten verzögern; ein manuelles ZIP-Update ist ebenfalls möglich.

== Häufige Fragen ==

Brauche ich Elementor oder Bricks? Nein. Gutenberg, Shortcodes und das Dashboard-Widget funktionieren unabhängig von beiden Buildern.

Sind Bibeltexte im Download enthalten? Nein. Jahrespakete und Bibelausgaben installierst du separat im Plugin. Beachte die Bedingungen der jeweiligen Quelle.

Kann ich den offiziellen Losungstext durch eine andere Übersetzung ersetzen? Nein. Das offizielle Paar bleibt zusammen und unverändert. Für eigene Auswahltexte verwendest du die separate Bibelstellen-Ausgabe.

Wird das nächste Jahr automatisch installiert? Nein. Prüfe unter Datenquellen die Verfügbarkeit und starte den Import vor dem Jahreswechsel selbst.

Verändert ein JSON-Import sofort die Website? Nein. Prüfe ihn im Formular und speichere zum Anwenden. JSON enthält Einstellungen, keine gespeicherten Bibeltexte.

Warum erscheinen noch die Verse von gestern? Prüfe die WordPress-Zeitzone, die installierten Jahresdaten und Seiten-/CDN-Caches. Zwischengespeicherte Seiten müssen zum Tageswechsel aktualisiert werden.

Wie funktionieren Updates? Der enthaltene deckerweb-Updater liefert öffentliche GitHub-Releases über die regulären WordPress-Updates. Ein zusätzliches Updater-Plugin ist nicht nötig.

Alle Fragen nach Themen (https://github.com/deckerweb/daily-scripture/wiki/FAQ-Deutsch)

== Changelog ==

= 0.16.2 =

* Verbessert: Gliedert alle vier Readmes mit einer klaren Funktionsübersicht, Inhaltsverzeichnis, sieben kurzen Antworten und den letzten fünf Versionen neu.
* Verbessert: Ergänzt englische und deutsche GitHub-Banner, ausführliche zweisprachige Anleitungen, thematische FAQs und den vollständigen Änderungsverlauf.
* Verbessert: Verlinkt die Dokumentation im Footer aller Plugin-Adminseiten und erklärt Updates über das reguläre WordPress-Updatesystem.
* Sonstiges: Liefert die Dokumentation lokal mit und behält die gemeinsame deckerweb-Updater-Bibliothek unverändert bei.

= 0.16.1 =
* Sonstiges: GPL-2.0-or-later für den Plugin-Code ausdrücklich dokumentiert; vollständige Lizenzdatei und Veröffentlichungspaket ergänzt. Die Nutzungsbedingungen der Bibeltexte bleiben unabhängig davon.

= 0.16.0 =
* Neu: Gemeinsame deckerweb GitHub Release Updater-Bibliothek wie in Brand Admin Schemes, mit WordPress-Updateanzeige und Release-Details.
* Verbessert: Begrenzte HTTPS-Metadatenabfragen, zwischengespeicherte Ergebnisse und lokalisierte Updatefehler.
* Verbessert: Prüfung von Paketidentität, angebotener Version und tatsächlichen WordPress-/PHP-Anforderungen vor dem Austausch der Plugin-Dateien.
* Sonstiges: Update-URI, Bibliotheksherkunft und Anleitung für öffentliche GitHub-Releases dokumentiert; automatische Updates bleiben eine Benutzerentscheidung.

= 0.15.0 =
* Neu: Changelog-Dialog neben der Versionsnummer auf allen Plugin-Adminseiten, mit deutscher oder englischer Versionshistorie.
* Verbessert: Englische Standard-Readmes und separate deutsche Fassungen; Verweise passend zur jeweiligen Sprache.

= 0.14.0 =
* Neu: Deutsche Sprachdateien für Plugin und Block-Editor.
* Verbessert: Kürzere Plugin-Beschreibung mit dem aktuellen Funktionsumfang; verständlichere Hilfen und einheitliche deutsche Begriffe.
* Verbessert: Readme als aktuelle Anleitung mit Einrichtung, Gestaltung, Shortcodes, Datenpflege und allen vier Bibelausgaben.
* Behoben: Veraltete Speicherpfade und unvollständige Hinweise zu Datenlöschung, JSON-Export und Dashboard-Vorschau berichtigt.
* Sonstiges: Vollständige Versionshistorie mit den Präfixen New, Improved, Fixed und Misc; Übersetzungsvorlage aktualisiert. Der Pluginname bleibt Daily Scripture.

Vollständiger Änderungsverlauf im Wiki (https://github.com/deckerweb/daily-scripture/wiki/Changelog-Deutsch) · Lokale Historie (https://github.com/deckerweb/daily-scripture/blob/main/docs/Changelog-Deutsch.md) · Releases (https://github.com/deckerweb/daily-scripture/releases)

== Über das Plugin ==

Entwickelt von David Decker – DECKERWEB, damit tägliche Bibelverse auf Gemeinde-, Kunden- und persönlichen Websites gut lesbar ihren Platz finden.

Eine Idee oder einen Fehler gefunden? Melde dich auf GitHub (https://github.com/deckerweb/daily-scripture/issues). Nenne Plugin-, WordPress- und PHP-Version sowie den betroffenen Editor und die Schritte zum Nachstellen.

Der Plugin-Code steht unter GPL-2.0-or-later. Für Bibeltexte und Jahrespakete gelten eigene Bedingungen. © 2026 David Decker – DECKERWEB · Lizenz (https://github.com/deckerweb/daily-scripture/blob/main/LICENSE)
