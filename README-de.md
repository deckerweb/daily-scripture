# Daily Scripture

[English](README.md) · **Deutsch**

**Tägliche Bibelworte. Passend zu deiner Website.**

Die Losungen, Bible 2.0 und eigene Bibelstellen aus vier lokal gespeicherten Übersetzungen: Daily Scripture verbindet tägliche Lektüre mit einer Gestaltung, die zu deiner Website passt. Mit Vorschau, zehn Layouts, abgestimmten Schriftgrößen und nativen Elementen für Gutenberg, Elementor und Bricks.

## Das steckt drin

- **Tagesverse und eigene Bibelstellen:** offizielle Verspaare aus Die Losungen und Bible 2.0 sowie eine unabhängige Bibelbibliothek mit Luther 1912, Elberfelder 1905, Menge 1939 und Schlachter 1951.
- **Lokal gespeicherte Texte:** verfügbare Jahrespakete direkt beim Anbieter prüfen und herunterladen oder Originaldateien manuell hochladen. Vollständige Prüfung vor dem Speichern, Bestandsübersicht, gezieltes Ersetzen und Löschen.
- **Gestaltung mit Vorschau:** zehn Layouts mit schematischen Auswahlkarten, helle und dunkle Ansichten, eigene Farben und kompakte Darstellung für schmale Bereiche. Die Gesamtgröße skaliert Überschrift, Verse, Stellenangaben und Hinweise gemeinsam.
- **Feinabstimmung nach Bedarf:** einzelne Schriftgrößen und Farben, Einheiten px, em, rem und %, vorhandene CSS-Variablen samt Ersatzwert. Datumsformat und Quellenüberschriften sind anpassbar.
- **Direkt im Editor:** je ein Gutenberg-Block für Tagesverse und eigene Bibelstellen, Einstellungen in der Seitenleiste und Vorschau der lokalen Texte. Elementor und Bricks bieten zusätzlich native, responsive Stilregler.
- **Auch ohne Builder:** zwei Shortcodes, eine eigene Beispielseite mit Kopierbuttons und ein kompaktes Dashboard-Widget mit persönlichen Leseeinstellungen.
- **Einstellungen zum Mitnehmen:** geprüfter JSON-Import und -Export für Gestaltung oder alle Website-Einstellungen. Quellenhinweise bleiben zugänglich; Bibleserver wird ausschließlich verlinkt, mit separat wählbarer Zielübersetzung.

## Installation und Einstieg

Benötigt werden WordPress ab 6.6, PHP ab 8.0 und DOM/XML. Für ZIP-Pakete ist die PHP-Erweiterung ZipArchive erforderlich. Elementor und Bricks sind optional.

1. Das Plugin-ZIP unter **Plugins → Installieren → Plugin hochladen** installieren und aktivieren.
2. Unter **Daily Scripture → Datenquellen** die Downloadquelle prüfen, ein Jahrespaket wählen und nach Prüfung der Nutzungsbedingungen importieren. Alternativ eine offizielle XML-/TWD-Datei oder das zugehörige ZIP hochladen.
3. Auf der Hauptseite die Gestaltung wählen, Vorschau prüfen und oben **Einstellungen speichern** klicken.
4. Den Block oder das Builder-Element **Daily Scripture** einfügen. Für eigene Bibelstellen zuerst eine Übersetzung unter **Bibelbibliothek** installieren und anschließend **Bibelstelle · Daily Scripture** verwenden.

Ein Update behält Einstellungen und installierte Texte. Nach dem Update den Editor neu laden; bei unveränderter Darstellung gegebenenfalls Browser- und Website-Cache leeren.

## Vorschau, Schrift und Dashboard

Bei 100 % sind die Schriftgrößen aufeinander abgestimmt: Überschrift 28 px, Verse 22 px, Stellenangabe 18 px, Datum 16 px und Zusatzinformationen 14 px. Der Gesamtregler verändert diese Größen gemeinsam. Im Expertenmodus lassen sich einzelne Werte überschreiben, zum Beispiel mit `1.75rem` oder `var(--text-xxl, 28px)`.

CSS-Variablen müssen auf der Website verfügbar sein und eine gültige Schriftgröße liefern. Die isolierte Admin-Vorschau lädt keine Variablen aus Bricks oder anderen Frameworks; sie verwendet den Ersatzwert. Relative Einheiten allein erzeugen keine Anpassung an Bildschirmbreiten. Eine entsprechend definierte CSS-Variable kann das übernehmen. Die Vorschau bietet verschiedene Breiten und eine Simulation strenger Theme-Abstände; die fertige Seite bitte zusätzlich im verwendeten Theme ansehen.

Im Dashboard wählt jeder Benutzer unter **Ansicht anpassen** seine eigene Versgröße (14, 16 oder 18 px), Abstände und Darstellung. Diese Auswahl gilt nur für ihn auf der jeweiligen Website. Quellenüberschriften und Datumsformat folgen den Website-Einstellungen; die Farben greifen den persönlichen WordPress-Admin-Akzent auf. Die Admin-Vorschau zeigt diese Farbumgebung, nicht die persönlichen Leseeinstellungen des Widgets.

Die Standardüberschrift lautet **Die Losungen** beziehungsweise auf deutschsprachigen Websites **Das Wort für heute**. Auf englischsprachigen Websites verwendet Bible 2.0 **The Word for Today**. Eigene globale Überschriften gelten auch im Dashboard; Blöcke und Builder-Elemente können sie überschreiben. Ein leeres Datumsformat übernimmt WordPress.

## Shortcodes

Tagesverse mit den gespeicherten Vorgaben:

```text
[daily_scripture]
```

Die Losungen in kompakter Darstellung:

```text
[daily_scripture source="herrnhuter" layout="minimal" density="compact"]
```

Eine eigene Bibelstelle aus der zuvor installierten Übersetzung:

```text
[daily_scripture_passage translation="luther-1912" book="JOH" chapter="3" from="16" to="17" title="Ein Wort für dich"]
```

Tagesverse unterstützen `source` (`herrnhuter`, `bible2`, `both`). Eigene Bibelstellen unterstützen `translation`, `book`, `chapter`, `from`, `to` und `title`; ohne `to` wird nur der erste Vers ausgegeben. Bis zu 50 Verse innerhalb eines Kapitels sind möglich. Ohne eigenen Titel erscheint die Bibelstellenangabe.

Beide Shortcodes unterstützen `layout`, `density` (`standard`, `compact`) und `theme` (`light`, `dark`, `auto`, `custom`). Ohne Darstellungsoptionen gelten die Website-Einstellungen. Alle Layoutwerte, Buchkürzel und weitere kopierbare Beispiele stehen auf der Pluginseite **Shortcodes**.

## Daten, Downloads und Jahreswechsel

Downloads werden bewusst in der Datenverwaltung ausgelöst. Die Quellenprüfung zeigt verfügbare Pakete; sie installiert das Folgejahr nicht selbstständig. Erfolgreiche Katalogprüfungen werden sechs Stunden, fehlgeschlagene fünf Minuten zwischengespeichert. Bei Anbieter- oder Verbindungsproblemen bleibt der manuelle Upload verfügbar.

Jahresdateien müssen ein vollständiges Kalenderjahr mit vollständigen Verspaaren enthalten. Ein bestehendes Jahr wird erst nach erfolgreicher Prüfung und ausdrücklicher Auswahl ersetzt. Der Tages- und Jahreswechsel folgt der WordPress-Zeitzone. Fehlende Daten führen zu einem Hinweis; es werden keine alten Verse als aktuelle ausgegeben. Die Jahresübersicht zeigt, ob Daten für den Jahreswechsel fehlen. Seiten- und CDN-Caches müssen zum Tageswechsel aktualisiert werden.

Alle vom Plugin verwalteten Textdateien liegen relativ zu `wp-content/uploads/` hier:

```text
daily-scripture/herrnhuter/YYYY.json.php
daily-scripture/bible2/YYYY.json.php
daily-scripture/bibles/luther-1912.json.php
daily-scripture/bibles/elberfelder-1905.json.php
daily-scripture/bibles/menge-1939.json.php
daily-scripture/bibles/schlachter-1951.json.php
```

`daily-scripture/downloads/` dient der vorübergehenden Download-Verarbeitung. Es gibt keinen zusätzlichen `data/`-Unterordner. Bei Einzelaktivierung in Multisite liegen die Daten getrennt unter `daily-scripture/sites/BLOG_ID/`; Netzwerkaktivierung wird nicht unterstützt. Bei umbenanntem Inhaltsverzeichnis gilt `WP_CONTENT_DIR/uploads/daily-scripture/`. Abweichende Uploadverzeichnisse werden für diese Textdaten nicht verwendet.

Schutzdateien, PHP-Abbruchschutz, Pfadprüfung und gesperrte Schreibvorgänge schützen den lokalen Bestand. Browseruploads landen zunächst im konfigurierten PHP-Upload-Tempverzeichnis. Plugin-Einstellungen und kurzlebige Statusmeldungen liegen in der WordPress-Datenbank.

## Bibelbibliothek und Quellen

Volltexte und Jahrespakete werden **nicht im Plugin-ZIP mitgeliefert**. Die Bibliothek akzeptiert die verlinkten, anhand ihres Fingerabdrucks geprüften Textfassungen. Andere Revisionen benötigen eine neue Quellenprüfung. Importiert werden jeweils 66 Bücher ohne zusätzliche Apokryphen und redaktionelle Überschriften.

| Ausgabe | Textquelle | Kennzeichnung der verwendeten Ausgabe |
| --- | --- | --- |
| Luther 1912 | [eBible](https://ebible.org/bible/details.php?id=deu1912) | Gemeinfrei |
| Elberfelder 1905, unrevidiert | [eBible](https://ebible.org/bible/details.php?id=deuelo) | Gemeinfrei |
| Menge 1939 | [Zefania-Textpaket](https://sourceforge.net/projects/zefania-sharp/files/Bibles/GER/Menge-Bibel/) | Gemeinfrei; Nachweise in der Bibelbibliothek |
| Schlachter 1951 | [eBible](https://ebible.org/deu1951/copyright.htm) | CC BY 4.0, mit Namensnennung und Lizenzverknüpfung |

Verszählungen können abweichen. Die Menge-Datei fasst Hesekiel 33,14–15 zusammen; beide Verse gemeinsam wählen. Die Schlachter-Datei enthält bei Matthäus 21,44 keinen Wortlaut und anschließend abweichende Versnummern. Daily Scripture übernimmt die Quelldatei und weist bei einer betroffenen Auswahl darauf hin.

Die offiziellen Tagesquellen bleiben unabhängig von dieser Bibliothek. Losung und Lehrtext erscheinen unverändert zusammen; Import und Ausgabe der Losungen sind auf Vorjahr, laufendes Jahr und Folgejahr begrenzt. Beachte die [Losungen-Nutzungsbedingungen](https://www.losungen.de/digital/nutzungsbedingungen/). Für Bible 2.0 gelten die [Projektbedingungen](https://bible2.net/en/copyright) sowie die Hinweise der jeweiligen Bibelausgabe. Die vollständigen importierten Lizenzinformationen bleiben über die aufklappbare Anzeige beziehungsweise das Dialogfenster zugänglich, auch ohne JavaScript.

Bibleserver dient ausschließlich als externes Linkziel. Das Plugin lädt dort keine Texte; die gewählte Zielübersetzung ändert nicht den angezeigten Verswortlaut.

## Sicherung und Deinstallation

Der JSON-Export enthält die **gespeicherten** Werte. Ein Design umfasst Darstellung und Datumsformat; der vollständige Export zusätzlich Quellenwahl und sonstige Website-Optionen. Texte, Jahrespakete, persönliche Dashboard-Einstellungen und Builder-Stile sind nicht enthalten. Ein Import wird erst ins Formular übernommen und nach **Einstellungen speichern** wirksam.

Deaktivieren behält Texte und Einstellungen. Bei Deinstallation werden die Plugin-Einstellungen und die persönlichen Widget-Einstellungen dieser Website entfernt. Jahresdaten und Bibelausgaben bleiben standardmäßig erhalten. Mit aktivierter Löschoption werden die erkannten Textdateien dieser Website ebenfalls entfernt; unbekannte Dateien und Schutzdateien bleiben erhalten. Sichere deine Texte bei Bedarf mit der normalen Dateisicherung deiner Website.

## Plugin-Updates über GitHub

Die gemeinsame **deckerweb GitHub Release Updater V1**-Bibliothek ist enthalten, unverändert übernommen aus Brand Admin Schemes 0.15.1. Bei aktivem Plugin registriert sie die WordPress-Updateprüfung und das Fenster mit Release-Details. Ziel ist ausschließlich das öffentliche Repository `https://github.com/deckerweb/daily-scripture`. `Update URI` verhindert eine Verwechslung mit einem gleichnamigen WordPress.org-Plugin.

Es werden veröffentlichte Releases verwendet, keine Entwürfe oder als Vorabversion markierten Releases. Höhere Versionen erscheinen in der üblichen WordPress-Updateverwaltung. Automatische Updates werden nicht selbstständig aktiviert. Ohne öffentlich erreichbares Repository oder Release bleibt der manuelle ZIP-Weg verfügbar.

**Für neue Releases:**

1. Version im Plugin, in beiden TXT-Readmes und in beiden Changelogs angleichen. Die tatsächlichen Mindestanforderungen im Pluginheader pflegen.
2. Ein vollständiges installierbares ZIP mit dem Hauptordner `daily-scripture/` bauen und prüfen. Bibliothek, Übersetzungen und alle vier Readmes mitliefern; keine Testdaten oder Zugangsdaten einpacken.
3. Im öffentlichen Repository ein stabiles Release mit Tag `vX.Y.Z` oder `X.Y.Z` veröffentlichen und `daily-scripture.zip` oder `daily-scripture-X.Y.Z.zip` anhängen. ZIP-Version und Tag müssen übereinstimmen.
4. Der Updater bevorzugt dieses Release-Asset. Fehlt es, verwendet die gemeinsame Bibliothek das GitHub-Quellarchiv. Dafür muss der markierte Repository-Stand selbst das vollständige Plugin mit Hauptdatei und sämtlichen benötigten Dateien im Stamm enthalten. Ein geprüftes Release-ZIP ist vorzuziehen.
5. Aktualisierung zuerst in einer Testinstallation prüfen, auch zusammen mit anderen deckerweb-Plugins. Öffentliche Veröffentlichung und End-to-End-Download sind separate Schritte vom lokalen Einbau.

Erfolgreiche Metadatenabfragen bleiben 30 Minuten, Fehler 10 Minuten im Cache; auch eine manuelle WordPress-Prüfung kann innerhalb dieser Frist den Bibliothekscache verwenden. Metadatenabfragen sind auf sechs Sekunden und 512 KiB begrenzt, ohne Weiterleitungen und mit HTTPS-Prüfung. Die Registrierung selbst ruft GitHub nicht auf und legt keinen zusätzlichen Cronjob an. Es sind keine GitHub-Zugangsdaten enthalten; beim Updatecheck erhält GitHub die üblichen Verbindungsdaten, aber keine Bibeltexte oder Plugin-Einstellungen.

Zusätzlich zur Bibliothek prüft Daily Scripture das entpackte Paket vor dem Austausch: erwartete Hauptdatei, Pluginname, Update-URI, neuere Versionsnummer, Übereinstimmung mit dem angebotenen Update sowie die tatsächlichen PHP-/WordPress-Mindestversionen. Die installierten Texte unter uploads werden nicht angefasst. Paketvertrauen beruht auf GitHub/HTTPS und der Kontrolle des Release-Repositories; eine separate kryptografische Paketsignatur implementiert V1 nicht.

Die gemeinsame Bibliothek bleibt bytegleich, damit mehrere deckerweb-Plugins dieselbe versionierte Klasse laden können. Pluginbezogene Schutz- und Übersetzungsregeln liegen in `src/Core/GitHubUpdates.php`. Herkunft und SHA-256 stehen in [UPDATER.md](UPDATER.md). Bei weiteren Releases gehören Bibliotheksstand, WordPress-/PHP-Kompatibilität, Cacheverhalten und Paketvalidierung zur Prüfung.

## Entwicklung und Übersetzungen

Namespace: `Deckerweb\DailyScripture`. Der gemeinsame Datenweg aus Import, Validierung und lokaler Speicherung versorgt die Ausgabe in allen Integrationen. Der Hook `daily_scripture_year_readiness` meldet die jährliche Datenbereitschaft; er lädt oder löscht keine Dateien.

Der Pluginname bleibt in jeder Sprache **Daily Scripture**. Deutsche Sprachdateien und die POT-Vorlage liegen in `languages/`; Quellenbezeichnungen, Shortcode-Parameter und importierte Originaltexte werden nicht umbenannt. `phpcs.xml.dist` enthält die WordPress-Coding-Standards-Konfiguration.

Über den Changelog-Link neben der Versionsnummer im Admin-Fußbereich öffnest du die Versionshistorie im Dialog. Er folgt deiner WordPress-Adminsprache (Deutsch oder Englisch); ohne JavaScript öffnet sich die Readme-Datei.

Die Versionshistorie mit **New**, **Improved**, **Fixed** und **Misc** steht in [readme-de.txt](readme-de.txt). Autor: [David Decker](https://github.com/deckerweb) · [Plugin-Website](https://github.com/deckerweb/daily-scripture).

## Lizenz

Copyright © 2026 David Decker – deckerweb. Der Plugin-Code steht unter **GPL-2.0-or-later**: Du darfst ihn unter den Bedingungen der GNU General Public License, Version 2 oder einer späteren Version, weitergeben und verändern. Er wird ohne Gewährleistung bereitgestellt. Den vollständigen Lizenztext findest du in [LICENSE](LICENSE).

Diese Softwarelizenz gilt nicht für heruntergeladene Bibeltexte oder Jahrespakete. Für diese gelten die oben aufgeführten Quellen- und Nutzungshinweise.
