=== Daily Scripture ===
Contributors: deckerweb
Tags: bible, scripture, daily, shortcode, block
Requires at least: 6.6
Requires PHP: 8.0
Stable tag: 0.16.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Tagesverse und eigene Bibelstellen, lokal gespeichert. Mit Vorschau, zehn Layouts, Gutenberg, Elementor, Bricks und kompaktem Dashboard.

== Description ==

Die Losungen, Bible 2.0 und eigene Bibelstellen aus vier lokalen Übersetzungen – passend gestaltet für deine Website.

* Tagesverse aus offiziellen Jahrespaketen: Direktdownload mit Verfügbarkeitsanzeige oder manueller Upload, Prüfung, Bestandsübersicht und Löschen.
* Unabhängige Bibelbibliothek: Luther 1912, Elberfelder 1905, Menge 1939 und Schlachter 1951 einzeln installieren und für eigene Bibelstellen nutzen.
* Zehn Layouts mit Vorschau, helle und dunkle Ansichten, eigene Farben und proportional abgestimmte Schriftgrößen. Expertenmodus mit px, em, rem, % und CSS-Variablen.
* Native Gutenberg-Blöcke, Elementor-Widgets und Bricks-Elemente für Tagesverse und Bibelstellen. Vorschau im Editor; responsive Stilregler in den Buildern.
* Zwei Shortcodes mit kopierbaren Beispielen, ein platzsparendes Dashboard-Widget mit persönlichen Leseeinstellungen sowie anpassbare Überschriften und Datumsformate.
* JSON-Import und -Export für Gestaltung oder Website-Einstellungen. Bibleserver-Verweise mit separat wählbarer Zielübersetzung.

Alle Textdateien werden unter wp-content/uploads/daily-scripture/ gespeichert. Bibeltexte und Jahrespakete sind nicht im Plugin enthalten. Offizielle Verspaare bleiben unverändert, Quellen- und Lizenzhinweise zugänglich. Die Schlachter-1951-Ausgabe steht unter CC BY 4.0; für jede Quelle gelten ihre jeweiligen Nutzungsbedingungen. Bibleserver wird ausschließlich verlinkt.

Daily Scripture bleibt auch in der deutschen Oberfläche der Pluginname. Autor: David Decker. Weitere Hinweise und Speicherpfade stehen in README-de.md.

== Plugin-Updates ==

Der mitgelieferte deckerweb-Updater prüft bei aktiver Plugin-Installation öffentliche Releases aus https://github.com/deckerweb/daily-scripture über die WordPress-Updateverwaltung. Ergebnisse werden 30 Minuten, Fehler 10 Minuten gespeichert. Automatische Updates werden nicht eigenständig aktiviert. Ohne öffentliches Release gibt es kein Updateangebot; manuelle ZIP-Updates bleiben möglich. Details für Herausgeber stehen in README-de.md.

== Installation ==

1. Das Plugin-ZIP in WordPress hochladen, installieren und aktivieren. Benötigt werden WordPress ab 6.6, PHP ab 8.0 und DOM/XML; für ZIP-Dateien zusätzlich ZipArchive.
2. Unter Daily Scripture → Datenquellen ein offizielles Jahrespaket herunterladen oder hochladen und importieren.
3. Auf der Hauptseite die Gestaltung wählen, Vorschau prüfen und Einstellungen speichern.
4. Daily Scripture als Block, Builder-Element oder Shortcode einfügen. Für eigene Bibelstellen zuerst eine Übersetzung in der Bibelbibliothek installieren.

== Frequently Asked Questions ==

= Werden neue Jahrespakete automatisch installiert? =
Nein. Die Datenverwaltung zeigt verfügbare Pakete an; den Download und Import löst du selbst aus. Der Tages- und Jahreswechsel verwendet die WordPress-Zeitzone. Bitte rechtzeitig das Folgejahr installieren und Website-Caches zum Tageswechsel aktualisieren.

= Brauche ich Elementor oder Bricks? =
Nein. Gutenberg, Shortcodes und das Dashboard-Widget funktionieren unabhängig von den Buildern.

= Was enthält eine JSON-Sicherung? =
Die gespeicherte Gestaltung oder alle Website-Einstellungen des Plugins. Bibeltexte, Jahrespakete, persönliche Dashboard-Einstellungen und Builder-Stile sind nicht enthalten. Ein Import wird zunächst ins Formular geladen und erst beim Speichern übernommen.

= Bleiben meine Texte beim Deaktivieren erhalten? =
Ja. Auch beim Deinstallieren bleiben sie standardmäßig erhalten. Die optionale Datenlöschung betrifft bekannte Jahresdateien und Bibelausgaben dieser Website. Die Plugin- und persönlichen Widget-Einstellungen werden bei der Deinstallation entfernt.

== Changelog ==

= 0.16.1 =
* Misc: GPL-2.0-or-later für den Plugin-Code ausdrücklich dokumentiert; vollständige Lizenzdatei und Veröffentlichungspaket ergänzt. Die Nutzungsbedingungen der Bibeltexte bleiben unabhängig davon.

= 0.16.0 =
* New: Gemeinsame deckerweb GitHub Release Updater-Bibliothek wie in Brand Admin Schemes, mit WordPress-Updateanzeige und Release-Details.
* Improved: Begrenzte HTTPS-Metadatenabfragen, zwischengespeicherte Ergebnisse und lokalisierte Updatefehler.
* Improved: Prüfung von Paketidentität, angebotener Version und tatsächlichen WordPress-/PHP-Anforderungen vor dem Austausch der Plugin-Dateien.
* Misc: Update-URI, Bibliotheksherkunft und Anleitung für öffentliche GitHub-Releases dokumentiert; automatische Updates bleiben eine Benutzerentscheidung.


= 0.15.0 =
* New: Changelog-Dialog neben der Versionsnummer auf allen Plugin-Adminseiten, mit deutscher oder englischer Versionshistorie.
* Improved: Englische Standard-Readmes und separate deutsche Fassungen; Verweise passend zur jeweiligen Sprache.

= 0.14.0 =
* New: Deutsche Sprachdateien für Plugin und Block-Editor.
* Improved: Kürzere Plugin-Beschreibung mit dem aktuellen Funktionsumfang; verständlichere Hilfen und einheitliche deutsche Begriffe.
* Improved: Readme als aktuelle Anleitung mit Einrichtung, Gestaltung, Shortcodes, Datenpflege und allen vier Bibelausgaben.
* Fixed: Veraltete Speicherpfade und unvollständige Hinweise zu Datenlöschung, JSON-Export und Dashboard-Vorschau berichtigt.
* Misc: Vollständige Versionshistorie mit den Präfixen New, Improved, Fixed und Misc; Übersetzungsvorlage aktualisiert. Der Pluginname bleibt Daily Scripture.

= 0.13.0 =
* New: Persönliche Dashboard-Einstellungen für Schriftgröße, Abstände und Layout, getrennt je Benutzer und Website.
* Improved: Kompakte Widget-Ansicht mit Logo und Einstellungslink; zusätzlicher Einstellungslink in der Pluginliste.

= 0.12.0 =
* Improved: Einheitlicher Kopfbereich mit Logo und Navigation sowie Fußbereich mit Version und Autor auf allen Pluginseiten.
* Improved: Einheitliche Karten und responsive Tabellen für Jahresdaten.
* Fixed: Doppelten Speichern-Button am Seitenende entfernt.

= 0.11.0 =
* New: Experten-Schriftgrößen mit px, em, rem, Prozentwerten und CSS-Variablen samt Ersatzwert.
* Improved: Fixierte Speicherleiste, Größen-Schnellwahl, verständlichere Schrifthilfen und Pluginlogo.
* Misc: Bisherige Pixelwerte und JSON-Sicherungen bleiben kompatibel.

= 0.10.0 =
* New: Schlachter 1951 aus der geprüften eBible-Quelle als vierte lokale Bibelausgabe.
* Improved: Urheber-, Quellen- und CC-BY-4.0-Lizenzhinweise in allen Bibelstellen-Ausgaben; kopierbares Shortcode-Beispiel.
* Misc: Herkunft und Besonderheiten der Textfassung dokumentiert.

= 0.9.0 =
* New: Native Bibelstellen-Elemente für Elementor und Bricks mit responsiven Stilreglern.
* Improved: Hilfe auf Einstieg und Tipps konzentriert; eigene Shortcode-Seite mit zugänglichen Kopierbuttons.

= 0.8.1 =
* Fixed: Absturz der Bibelstellen-Vorschau im Block-Editor durch Verwendung der richtigen WordPress-Komponente behoben.
* Fixed: Unterschiedliche Exporte der WordPress-Komponente für serverseitige Vorschauen unterstützt.

= 0.8.0 =
* New: Direkter Download offizieller Jahrespakete mit Verfügbarkeitsanzeige und manuellem Upload als Alternative.
* New: Lokale Bibelbibliothek mit Luther 1912, Elberfelder 1905 und Menge 1939 aus geprüften Textquellen.
* New: Gutenberg-Block mit Vorschau und Shortcode für eigene Bibelstellen; Installation, Originaldatei-Upload und Löschen von Bibelausgaben.
* Improved: Begrenzte HTTPS-Downloads, zugelassene Quellen, vollständige Prüfung und atomare Installation; Ersetzen nur nach ausdrücklicher Auswahl.

= 0.7.0 =
* New: Native Typografie-, Farb-, Rahmen- und Abstandsregler für Elementor und Bricks.
* Improved: Responsive Stilwerte mit Übernahme der Plugin-Vorgaben bei leeren Feldern.
* Improved: Schriftregeln mit Bricks-Vorschau kompatibel; Lizenzdialog übernimmt elementeigene Schrift und Farben.
* Misc: Mit Bricks 2.4.2 und Elementor Free 4.3.2 geprüft.

= 0.6.0 =
* New: Natives Elementor-Widget und Bricks-Element mit gemeinsamer serverseitiger Ausgabe.
* Improved: Quelle, Überschriften, Layout, Kompaktheit und Farbschema folgen wahlweise den Website-Einstellungen.
* Improved: Registrierung unabhängig von der Ladereihenfolge; Builder bleiben optional.
* Fixed: Statische Elementor-Widget-Zwischenspeicherung für Tagesverse deaktiviert.

= 0.5.0 =
* New: Gutenberg-Einstellungen in der Seitenleiste und Vorschau der Tagesverse direkt im Block.
* New: Globale Quellenüberschriften und eigene Blocktitel; Standard für Bible 2.0 je nach Website-Sprache.
* Improved: Gemeinsame Website-Stile auch im Block-Editor und dessen Vorschau-Rahmen.
* Misc: Bestehende Blöcke und Einstellungsexporte aus 0.4 bleiben kompatibel.

= 0.4.0 =
* New: Gruppierter Gestaltungsbereich mit isolierter Vorschau, verschiedenen Breiten und Simulation strenger Theme-Regeln.
* New: Zehn Layouts mit schematischen Auswahlkarten, proportionale Schriftgrößen und Expertenregler für Größen und Farben.
* New: Geprüfter JSON-Import und -Export für Gestaltung oder sämtliche Website-Einstellungen; Import zunächst ins Formular.
* Improved: Schutz vor übermäßigen Theme-Abständen und Kontrasthinweise in der Vorschau.

= 0.3.0 =
* New: Vier Layouts, vier Überschriftengrößen, kompakte Ansicht sowie helle, dunkle und eigene Farben.
* Improved: Kompaktere Darstellung mit gemeinsamer Zeile für Titel und Datum; Datumsformat aus WordPress.
* Improved: Darstellung je Block oder Shortcode überschreibbar; Admin-Akzentfarbe im Dashboard.

= 0.2.1 =
* New: Kompakte Bible-2.0-Lizenzanzeige mit zugänglichem Dialogfenster.
* Improved: Vollständige importierte Rechtehinweise bleiben erhalten; aufklappbare Anzeige auch ohne JavaScript.

= 0.2.0 =
* New: Geprüfter Jahresimport aus XML, TWD und ZIP mit atomarer lokaler Speicherung.
* New: Jahresübersicht mit ausdrücklichem Ersetzen und bestätigtem Löschen.
* New: Prüfung der Datenbereitschaft zum Jahreswechsel; Auswahl nach WordPress-Zeitzone.
* Improved: Unveränderte Quelltexte und Rechtehinweise, zulässiger Jahresbereich für Losungen sowie gemeinsamer Renderer.
* Improved: Bibleserver-Zielübersetzung unabhängig wählbar; geschützte Adminaktionen.
* Misc: PHPDoc, Übersetzungsvorlage und Prüfung der WordPress Coding Standards.

= 0.1.0-prototype =
* New: Grundstruktur für Quellen und WordPress-Integrationen.
