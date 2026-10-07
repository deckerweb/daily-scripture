# Fragen nach Themen

[Deutsch](Deutsch.md) · [English](FAQ-English.md) · [Home](Home.md)

Daily Scripture ergänzt deine WordPress-Website um tägliche Lesungen, ausgewählte Bibelstellen und persönliche Dashboard-Widgets. Wähle deine Quellen, gestalte die Ausgabe und zeige Lesungen mit Gutenberg, Shortcodes, Elementor oder Bricks an. Die Website-Funktionen sind auch in Multisite nutzbar, ergänzt um eine Leseansicht im Network Admin.

## Themen

- [Einstieg](#einstieg)
- [Jahresdaten und Downloads](#jahresdaten-und-downloads)
- [Bibelausgaben und Quellenhinweise](#bibelausgaben-und-quellenhinweise)
- [Layouts und Schrift](#layouts-und-schrift)
- [Editoren, Links und Dashboard](#editoren-links-und-dashboard)
- [Sicherung und gespeicherte Daten](#sicherung-und-gespeicherte-daten)
- [Updates und Fehlersuche](#updates-und-fehlersuche)

<a name="einstieg"></a>

## Einstieg

### Was brauche ich?

WordPress ab 6.6, PHP ab 8.0 und DOM/XML. ZIP-Importe benötigen ZipArchive. Das Plugin funktioniert ohne Page Builder.

### Wer darf Quellen und Einstellungen verwalten?

Die Verwaltungsseiten setzen die WordPress-Berechtigung manage_options voraus, die normalerweise Administratoren haben. Die Leseeinstellungen im Dashboard sind persönlich.

### Welche Ausgabe soll ich wählen?

Daily Scripture zeigt die offiziellen Tagesverse. Bibelstelle · Daily Scripture gibt eine selbst gewählte Stelle aus einer installierten Bibelausgabe aus.

### Sind Texte bereits installiert?

Nein. Beginne mit einem Jahrespaket unter Datenquellen oder einer Ausgabe unter Bibelbibliothek. Das Plugin-ZIP enthält Software und Dokumentation, keine Bibeltexte.

### Kann ich Multisite verwenden?

Die Aktivierung je Website wird unterstützt. Einstellungen, Jahresdaten und Bibelausgaben bleiben je Website getrennt. Netzwerkaktivierung ist durch den mitgelieferten Library-Katalog 0.6.0 noch gesperrt und wartet auf eine separat freigegebene Komponenten-Korrektur. Die gemeinsame deckerweb Library verwendet Netzwerkeinstellungen.


<a name="jahresdaten-und-downloads"></a>

## Jahresdaten und Downloads

### Importiert die Verfügbarkeitsprüfung schon etwas?

Nein. Sie listet verfügbare Quellenpakete. Wähle das Paket und starte Download beziehungsweise Import selbst.

### Was tun, wenn ein Download nicht verfügbar ist?

Prüfe die Quelle später erneut oder lade ihr verlinktes Originalpaket manuell hoch. Lass TLS- oder Verbindungsfehler beim Hosting prüfen, statt Zertifikatsprüfungen abzuschalten.

### Kann ich ein installiertes Jahr ersetzen?

Ja, mit ausdrücklich gewähltem Ersetzen. Das neue Jahr wird geprüft, bevor es vorhandene Daten ersetzt; bei fehlgeschlagener Prüfung bleiben die bisherigen Dateien erhalten.

### Was passiert zum Jahreswechsel?

Das aktive Jahr folgt der WordPress-Zeitzone. Installiere das nächste Paket vorher. Fehlende Daten führen zu einem Hinweis; alte Verse werden nicht stillschweigend weiterverwendet.

### Kann ich jedes Losungen-Jahr installieren?

Import und Anzeige sind auf Vorjahr, laufendes Jahr und Folgejahr begrenzt. Jahrespakete müssen das volle Jahr und vollständige Tagespaare enthalten.


<a name="bibelausgaben-und-quellenhinweise"></a>

## Bibelausgaben und Quellenhinweise

### Welche Ausgaben werden unterstützt?

Luther 1912, unrevidierte Elberfelder 1905, Menge 1939 und Schlachter 1951. Installiere sie einzeln aus den in der Bibliothek aufgeführten geprüften Quellen.

### Sind alle vier Quellen gemeinfrei?

Nein. Die geprüften Luther-, Elberfelder- und Menge-Quellen sind als gemeinfrei ausgewiesen. Die verwendete Schlachter-1951-Quelle steht unter CC BY 4.0; Namensnennung und Lizenzlink bleiben erhalten.

### Übersetzt die Wahl einer Bibelausgabe die Losungen neu?

Nein. Offizielle Tagesverse bleiben unverändert und als Paar erhalten. Die lokale Übersetzung gilt nur für unabhängig ausgewählte Bibelstellen.

### Warum kann eine Versnummer fehlen?

Die Verszählung unterscheidet sich je Ausgabe. Menge fasst Hesekiel 33,14–15 zusammen; wähle beide gemeinsam. Die Schlachter-Datei hat bei Matthäus 21,44 keinen Text und danach eine abweichende Zählung. Die Quellenzählung bleibt erhalten.

### Darf ich die Copyright-Hinweise entfernen?

Lass die Hinweise der jeweiligen Quelle zugänglich. Bible 2.0 kann ausführliche Angaben im Dialog zeigen, ohne JavaScript aufklappbar. Die Softwarelizenz ersetzt nicht die Nutzungsbedingungen der Texte.


<a name="layouts-und-schrift"></a>

## Layouts und Schrift

### Wie beginne ich bei den Schriftgrößen?

Wähle ein Layout und passe dann die Gesamtskala an. Bei 100 % gelten standardmäßig 28 px für Überschriften, 22 px für Verse, 18 px für Bibelstellen, 16 px für das Datum und 14 px für Zusatzhinweise.

### Welche Layouts gibt es?

Karte, Schlicht, Akzentleiste, Lesespalten, Leseblatt, Titelband, Kontur, Geteilte Verse, Journal und Ruhepol. Die Auswahlkarten zeigen ihren Aufbau; kompakte Abstände lassen sich zusätzlich wählen.

### Kann ich CSS-Variablen von Bricks oder Core Framework nutzen?

Ja, wenn sie auf der angezeigten Seite vorhanden sind und eine Schriftgröße ergeben. Nutze einen Ersatzwert wie var(--text-xxl, 28px). Die abgeschirmte Adminvorschau verwendet diesen Ersatzwert.

### Sind relative Einheiten automatisch responsiv?

em, rem und % beziehen sich auf ihren Schriftkontext, nicht automatisch auf Geräte-Breakpoints. Nutze eine responsive CSS-Variable oder die responsiven Builder-Regler für breitenabhängige Größen.

### Warum weicht die Website von der Vorschau ab?

Die Vorschau ist von Theme-CSS und externen Variablen getrennt. Prüfe Theme- und Builder-Regeln, Element-Einstellungen und Caches. Die strenge Abstands-Simulation hilft, bildet aber nicht jedes Theme nach.


<a name="editoren-links-und-dashboard"></a>

## Editoren, Links und Dashboard

### Wo sind die Gutenberg-Einstellungen?

Wähle den Block und öffne die Block-Seitenleiste. Auf der Arbeitsfläche erscheint eine serverseitige Vorschau mit installierten lokalen Daten; jeder Block kann gespeicherte Vorgaben überschreiben.

### Können Elementor und Bricks eigene Stile verwenden?

Ja. Native Regler bestimmen Typografie, Farben, Rahmen und Abstände einschließlich responsiver Werte. Leere Regler übernehmen die Plugin-Vorgaben.

### Wie ändere ich Überschriften und Datum?

Lege Quellenüberschriften in den Haupteinstellungen fest; Blöcke und Builder-Elemente können sie überschreiben. Ein leeres Datumsformat folgt WordPress. Bible 2.0 verwendet auf deutschen Websites Das Wort für heute, sonst The Word for Today.

### Wie wird das Dashboard kompakter?

Wähle im Widget unter Ansicht anpassen kompakte Abstände und eine Versgröße von 14, 16 oder 18 px. Die Auswahl gilt pro Benutzer und Website und verändert das Frontend nicht.

### Liefert Bibleserver die angezeigten Texte?

Nein. Bibelstellen verlinken auf Bibleserver. Die Zielübersetzung ist unabhängig einstellbar und verändert den lokalen Text nicht.


<a name="sicherung-und-gespeicherte-daten"></a>

## Sicherung und gespeicherte Daten

### Was enthalten JSON-Exporte?

Die gespeicherte Gestaltung oder alle Plugin-Website-Einstellungen. Texte, Jahrespakete, persönliche Widget-Werte und Builder-Stile sind ausgeschlossen. Ungespeicherte Formularänderungen werden nicht exportiert.

### Wann gilt ein Einstellungsimport?

Nachdem du die importierten Werte im Formular geprüft und gespeichert hast. Der JSON-Import allein verändert die gespeicherte Gestaltung nicht.

### Wo liegen Jahresdaten und Bibeltexte?

Unter wp-content/uploads/daily-scripture/: data/herrnhuter/YYYY.json.php, data/bible2/YYYY.json.php und bibles/UEBERSETZUNG.json.php. Die Anleitung nennt die genauen Dateinamen jeder Ausgabe.

### Löscht Deaktivieren meine Texte?

Nein. Deaktivieren erhält Texte und Einstellungen. Deinstallieren bereinigt temporäre Caches und geplante Prüfungen. Einstellungen, persönliche Widget-Werte und Texte bleiben standardmäßig erhalten. Die Löschoption einer Website entfernt ihre Einstellungen, persönlichen Werte und bekannten Textdateien. Beim Entfernen des Plugins gilt die Entscheidung jeder Website einzeln; fremde Daten bleiben unberührt.

### Genügt JSON als vollständiges Backup?

Nein. Sichere auch die WordPress-Datenbank, den lokalen Textordner und gegebenenfalls Builder-Vorlagen mit deinem normalen Backupsystem. JSON erleichtert den Einstellungstransfer.


<a name="updates-und-fehlersuche"></a>

## Updates und Fehlersuche

### Wie installiere ich ein Update?

Bei aktivem Plugin erscheinen öffentliche GitHub-Releases unter Plugins oder Dashboard → Aktualisierungen. Ein zusätzliches Updater-Plugin ist nicht nötig. Das Release-ZIP lässt sich auch manuell installieren.

### Warum wird die neue Version noch nicht angeboten?

Erfolgreiche Metadatenprüfungen werden 30 Minuten, Fehler 10 Minuten zwischengespeichert. Prüfe später erneut. Entwürfe und GitHub-Vorabversionen werden ausgeschlossen; Hosting-Beschränkungen können GitHub blockieren.

### Werden automatische Updates für mich aktiviert?

Nein. Du entscheidest, ob WordPress Updates automatisch installieren soll. Der eingebaute Updater liefert Updateinformationen und das Paket.

### Warum funktioniert eine Gutenberg-Vorschau nicht?

Lade den Editor neu und leere nach Updates betroffene Browser- oder Builder-Caches. Prüfe, ob die gewählten Textdaten installiert sind. Bleibt der Fehler, melde die genaue Fehlermeldung und Schritte zum Nachstellen ohne Zugangsdaten.

### Was gehört in eine hilfreiche Fehlermeldung?

Plugin-, WordPress- und PHP-Version, Editor und Theme, betroffene Quelle/Jahr oder Bibelstelle, erwartetes und tatsächliches Ergebnis und nachvollziehbare Schritte. Screenshots helfen; Passwörter, Lizenzschlüssel und private Kundendaten gehören nicht hinein.


## Wie wähle ich Widget-Lesungen und ihre Website?

Lege unter **Daily Scripture → Dashboard-Lesungen** die angebotenen Lesungen fest: Die Losungen, Bible 2.0 und bis zu sechs Bibelstellen aus lokal installierten Ausgaben (bis zu 50 Verse innerhalb eines Kapitels). Speichere die Website-Einstellungen und öffne im Widget **Ansicht anpassen**. Wähle eine oder mehrere Lesungen und ändere ihre Reihenfolge mit **Nach oben / Nach unten**. Ohne JavaScript stehen Positionsfelder bereit. Ohne eingerichtete Lesungen erscheint ein zurückhaltender Hinweis; fehlende Jahresdaten oder Bibelausgaben werden je Lesung angezeigt. Persönliche Auswahl und Reihenfolge ändern die öffentliche Ausgabe nicht. Vollständige JSON-Sicherungen enthalten die Lesungsdefinitionen; reine Designs und ältere Sicherungen lassen sie unverändert. Installiere benötigte Ausgaben auf der Zielwebsite, bevor du importierte Bibelstellen speicherst.

Das Network-Admin-Widget verwendet eine persönlich gewählte aktive Website des aktuellen Netzwerks und nennt diese sichtbar. Ist nur die Hauptwebsite aktiv, wird sie automatisch verwendet. Bei mehreren aktiven Websites oder Netzwerkaktivierung kannst du die Bezugswebsite wählen: Speichere nach dem Wechsel einmal, um deren Lesungen zu laden, und triff danach deine persönliche Auswahl. Ist die bisherige Website nicht mehr verfügbar, folgt ein Hinweis und eine Ersatzwebsite. Das Widget steht im Network Admin bei Aktivierung auf der Hauptwebsite oder im Netzwerk bereit; eine ausschließliche Unterwebsite-Aktivierung lädt es dort nicht. Der Website-Widget-Schalter betrifft das Dashboard dieser Website; die Netzwerk-Leseansicht ist unabhängig. Die Website-Auswahl wird in Gruppen von 50 angeboten. Es entsteht keine zusätzliche Netzwerk-Bibeltextdatenhaltung.

Farben wählst du über den modernen WordPress-Farbwähler in einem WordPress-Modal; direkte Hex-Eingabe und geerbte Textfarben bleiben möglich. Änderungen werden erst mit dem Speichern der Einstellungen wirksam.
