# Anleitung · Daily Scripture 0.16.2

[English](English.md) · [Home](Home.md)

Von der ersten Lesung bis zur passenden Gestaltung: Nutze das Inhaltsverzeichnis, um direkt zum gewünschten Thema zu springen.

## Inhaltsverzeichnis

- [Das steckt drin](#das-steckt-drin)
- [Installation und Einstieg](#installation-und-einstieg)
- [Vorschau, Schrift und Dashboard](#vorschau-schrift-und-dashboard)
- [Shortcodes](#shortcodes)
- [Daten, Downloads und Jahreswechsel](#daten-downloads-und-jahreswechsel)
- [Bibelbibliothek und Quellen](#bibelbibliothek-und-quellen)
- [Sicherung und Deinstallation](#sicherung-und-deinstallation)
- [Plugin-Updates über GitHub](#plugin-updates-ueber-github)
- [Entwicklung und Übersetzungen](#entwicklung-und-uebersetzungen)
- [Lizenz](#lizenz)
- [Häufige Fragen](#faq)
- [Changelog](#changelog)

<a name="das-steckt-drin"></a>

## Das steckt drin

- **Tagesverse und eigene Bibelstellen:** offizielle Verspaare aus Die Losungen und Bible 2.0 sowie eine unabhängige Bibelbibliothek mit Luther 1912, Elberfelder 1905, Menge 1939 und Schlachter 1951.
- **Lokal gespeicherte Texte:** verfügbare Jahrespakete direkt beim Anbieter prüfen und herunterladen oder Originaldateien manuell hochladen. Vollständige Prüfung vor dem Speichern, Bestandsübersicht, gezieltes Ersetzen und Löschen.
- **Gestaltung mit Vorschau:** zehn Layouts mit schematischen Auswahlkarten, helle und dunkle Ansichten, eigene Farben und kompakte Darstellung für schmale Bereiche. Die Gesamtgröße skaliert Überschrift, Verse, Stellenangaben und Hinweise gemeinsam.
- **Feinabstimmung nach Bedarf:** einzelne Schriftgrößen und Farben, Einheiten px, em, rem und %, vorhandene CSS-Variablen samt Ersatzwert. Datumsformat und Quellenüberschriften sind anpassbar.
- **Direkt im Editor:** je ein Gutenberg-Block für Tagesverse und eigene Bibelstellen, Einstellungen in der Seitenleiste und Vorschau der lokalen Texte. Elementor und Bricks bieten zusätzlich native, responsive Stilregler.
- **Auch ohne Builder:** zwei Shortcodes, eine eigene Beispielseite mit Kopierbuttons und ein kompaktes Dashboard-Widget mit persönlichen Leseeinstellungen.
- **Einstellungen zum Mitnehmen:** geprüfter JSON-Import und -Export für Gestaltung oder alle Website-Einstellungen. Quellenhinweise bleiben zugänglich; Bibleserver wird ausschließlich verlinkt, mit separat wählbarer Zielübersetzung.

<a name="installation-und-einstieg"></a>

## Installation und Einstieg

Benötigt werden WordPress ab 6.6, PHP ab 8.0 und DOM/XML. Für ZIP-Pakete ist die PHP-Erweiterung ZipArchive erforderlich. Elementor und Bricks sind optional.

1. Das Plugin-ZIP unter **Plugins → Installieren → Plugin hochladen** installieren und aktivieren.
2. Unter **Daily Scripture → Datenquellen** die Downloadquelle prüfen, ein Jahrespaket wählen und nach Prüfung der Nutzungsbedingungen importieren. Alternativ eine offizielle XML-/TWD-Datei oder das zugehörige ZIP hochladen.
3. Auf der Hauptseite die Gestaltung wählen, Vorschau prüfen und oben **Einstellungen speichern** klicken.
4. Den Block oder das Builder-Element **Daily Scripture** einfügen. Für eigene Bibelstellen zuerst eine Übersetzung unter **Bibelbibliothek** installieren und anschließend **Bibelstelle · Daily Scripture** verwenden.

Ein Update behält Einstellungen und installierte Texte. Nach dem Update den Editor neu laden; bei unveränderter Darstellung gegebenenfalls Browser- und Website-Cache leeren.

<a name="vorschau-schrift-und-dashboard"></a>

## Vorschau, Schrift und Dashboard

Bei 100 % sind die Schriftgrößen aufeinander abgestimmt: Überschrift 28 px, Verse 22 px, Stellenangabe 18 px, Datum 16 px und Zusatzinformationen 14 px. Der Gesamtregler verändert diese Größen gemeinsam. Im Expertenmodus lassen sich einzelne Werte überschreiben, zum Beispiel mit `1.75rem` oder `var(--text-xxl, 28px)`.

CSS-Variablen müssen auf der Website verfügbar sein und eine gültige Schriftgröße liefern. Die isolierte Admin-Vorschau lädt keine Variablen aus Bricks oder anderen Frameworks; sie verwendet den Ersatzwert. Relative Einheiten allein erzeugen keine Anpassung an Bildschirmbreiten. Eine entsprechend definierte CSS-Variable kann das übernehmen. Die Vorschau bietet verschiedene Breiten und eine Simulation strenger Theme-Abstände; die fertige Seite bitte zusätzlich im verwendeten Theme ansehen.

Im Dashboard wählt jeder Benutzer unter **Ansicht anpassen** seine eigene Versgröße (14, 16 oder 18 px), Abstände und Darstellung. Diese Auswahl gilt nur für ihn auf der jeweiligen Website. Quellenüberschriften und Datumsformat folgen den Website-Einstellungen; die Farben greifen den persönlichen WordPress-Admin-Akzent auf. Die Admin-Vorschau zeigt diese Farbumgebung, nicht die persönlichen Leseeinstellungen des Widgets.

Die Standardüberschrift lautet **Die Losungen** beziehungsweise auf deutschsprachigen Websites **Das Wort für heute**. Auf englischsprachigen Websites verwendet Bible 2.0 **The Word for Today**. Eigene globale Überschriften gelten auch im Dashboard; Blöcke und Builder-Elemente können sie überschreiben. Ein leeres Datumsformat übernimmt WordPress.

<a name="shortcodes"></a>

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

<a name="daten-downloads-und-jahreswechsel"></a>

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

<a name="bibelbibliothek-und-quellen"></a>

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

<a name="sicherung-und-deinstallation"></a>

## Sicherung und Deinstallation

Der JSON-Export enthält die **gespeicherten** Werte. Ein Design umfasst Darstellung und Datumsformat; der vollständige Export zusätzlich Quellenwahl und sonstige Website-Optionen. Texte, Jahrespakete, persönliche Dashboard-Einstellungen und Builder-Stile sind nicht enthalten. Ein Import wird erst ins Formular übernommen und nach **Einstellungen speichern** wirksam.

Deaktivieren behält Texte und Einstellungen. Bei Deinstallation werden die Plugin-Einstellungen und die persönlichen Widget-Einstellungen dieser Website entfernt. Jahresdaten und Bibelausgaben bleiben standardmäßig erhalten. Mit aktivierter Löschoption werden die erkannten Textdateien dieser Website ebenfalls entfernt; unbekannte Dateien und Schutzdateien bleiben erhalten. Sichere deine Texte bei Bedarf mit der normalen Dateisicherung deiner Website.

<a name="plugin-updates-ueber-github"></a>

## Plugin-Updates über GitHub

Updates kommen direkt aus dem [DECKERWEB-Repository auf GitHub](https://github.com/deckerweb/daily-scripture/releases) und erscheinen bei aktivem Daily Scripture im **regulären WordPress-Updatesystem**. Aktualisiere wie gewohnt über Plugins oder Dashboard → Aktualisierungen; ein zusätzliches Updater-Plugin ist nicht nötig. Automatische Updates bleiben deine Entscheidung.

Die [deutsche Anleitung](https://github.com/deckerweb/daily-scripture/wiki/Deutsch), die [englische Anleitung](https://github.com/deckerweb/daily-scripture/wiki/English) und die thematischen FAQs erklären Einstellungen, Quellen und häufige Fragen. Eine [lokale Kopie](Deutsch.md) liegt im Plugin. Im Admin-Footer findest du die Dokumentation und den Changelog-Dialog. Zwischengespeicherte Updateprüfungen können ein neues Angebot bis zu 30 Minuten verzögern; ein manuelles ZIP-Update ist ebenfalls möglich.

<a name="entwicklung-und-uebersetzungen"></a>

## Entwicklung und Übersetzungen

Namespace: `Deckerweb\DailyScripture`. Der gemeinsame Datenweg aus Import, Validierung und lokaler Speicherung versorgt die Ausgabe in allen Integrationen. Der Hook `daily_scripture_year_readiness` meldet die jährliche Datenbereitschaft; er lädt oder löscht keine Dateien.

Der Pluginname bleibt in jeder Sprache **Daily Scripture**. Deutsche Sprachdateien und die POT-Vorlage liegen in `languages/`; Quellenbezeichnungen, Shortcode-Parameter und importierte Originaltexte werden nicht umbenannt. `phpcs.xml.dist` enthält die WordPress-Coding-Standards-Konfiguration.

Über den Changelog-Link neben der Versionsnummer im Admin-Fußbereich öffnest du die Versionshistorie im Dialog. Er folgt deiner WordPress-Adminsprache (Deutsch oder Englisch); ohne JavaScript öffnet sich die Readme-Datei.

Die Versionshistorie mit **New**, **Improved**, **Fixed** und **Misc** steht in [readme-de.txt](Changelog-Deutsch.md). Autor: [David Decker](https://github.com/deckerweb) · [Plugin-Website](https://github.com/deckerweb/daily-scripture).

<a name="lizenz"></a>

## Lizenz

Copyright © 2026 David Decker – deckerweb. Der Plugin-Code steht unter **GPL-2.0-or-later**: Du darfst ihn unter den Bedingungen der GNU General Public License, Version 2 oder einer späteren Version, weitergeben und verändern. Er wird ohne Gewährleistung bereitgestellt. Den vollständigen Lizenztext findest du in [LICENSE](../LICENSE).

Diese Softwarelizenz gilt nicht für heruntergeladene Bibeltexte oder Jahrespakete. Für diese gelten die oben aufgeführten Quellen- und Nutzungshinweise.

<a name="faq"></a>

## Häufige Fragen

**Brauche ich Elementor oder Bricks?** Nein. Gutenberg, Shortcodes und das Dashboard-Widget funktionieren unabhängig von beiden Buildern.

**Sind Bibeltexte im Download enthalten?** Nein. Jahrespakete und Bibelausgaben installierst du separat im Plugin. Beachte die Bedingungen der jeweiligen Quelle.

**Kann ich den offiziellen Losungstext durch eine andere Übersetzung ersetzen?** Nein. Das offizielle Paar bleibt zusammen und unverändert. Für eigene Auswahltexte verwendest du die separate Bibelstellen-Ausgabe.

**Wird das nächste Jahr automatisch installiert?** Nein. Prüfe unter Datenquellen die Verfügbarkeit und starte den Import vor dem Jahreswechsel selbst.

**Verändert ein JSON-Import sofort die Website?** Nein. Prüfe ihn im Formular und speichere zum Anwenden. JSON enthält Einstellungen, keine gespeicherten Bibeltexte.

**Warum erscheinen noch die Verse von gestern?** Prüfe die WordPress-Zeitzone, die installierten Jahresdaten und Seiten-/CDN-Caches. Zwischengespeicherte Seiten müssen zum Tageswechsel aktualisiert werden.

**Wie funktionieren Updates?** Der enthaltene deckerweb-Updater liefert öffentliche GitHub-Releases über die regulären WordPress-Updates. Ein zusätzliches Updater-Plugin ist nicht nötig.

[Alle Fragen nach Themen](https://github.com/deckerweb/daily-scripture/wiki/FAQ-Deutsch)

<a name="changelog"></a>

## Changelog

[Vollständiger Änderungsverlauf](Changelog-Deutsch.md)

[Updates veröffentlichen und prüfen](Development-Deutsch.md)
