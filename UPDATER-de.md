# Updates und gemeinsame Komponenten

[English](UPDATER.md)

Diese Dokumentation gilt für Daily Scripture auf einzelnen WordPress-Websites (Single Site) und Websites in Multisite.

Daily Scripture enthält [deckerweb Updater 2.1.0](https://github.com/deckerweb/deckerweb-updater/tree/v2.1.0) unter `includes/deckerweb-github-release-updater-v2.php` (SHA-256 `5e03d5c04e5ec286ef8e99219d231e16054b3cb9215940083ca04e8385ceff9f`), GPL-2.0-or-later, von David Decker – DECKERWEB. Er geht auf den mit Brand Admin Schemes gelieferten Updater zurück. Die V2-Klasse wird geschützt geladen; eine bereits geladene ältere Kopie ohne Host-Übersetzungen beendet diese Updateintegration mit einem Adminhinweis. Die ursprüngliche V1-Kopie bleibt zur Kompatibilität erhalten und wird von Daily Scripture nicht geladen.

Angeboten werden ausschließlich stabile öffentliche Releases aus `https://github.com/deckerweb/daily-scripture`. Dafür sind keine Zugangsdaten nötig. Der Hostadapter übersetzt Engine-Meldungen über `daily-scripture` und liefert lokale SVG-/PNG-Icons sowie deutsche und englische Banner in beiden Auflösungen. Automatische Updates bleiben deine Entscheidung.

Metadaten verwenden geprüftes HTTPS ohne Weiterleitungen, mit sechs Sekunden Zeitlimit und 512 KiB Größenlimit. Erfolgreiche Antworten bleiben 30 Minuten, Fehler 10 Minuten im Cache. Verse und Einstellungen werden nicht übertragen. Ein Release-ZIP wird bevorzugt; das GitHub-Quellarchiv dient als Fallback. Die Paketprüfung kontrolliert Hauptdatei, Name, Update URI, neuere Version entsprechend dem angebotenen Update und tatsächliche WordPress-/PHP-Anforderungen, auch bei nativen Sammelupdates. Paketvertrauen beruht auf HTTPS und Repository-Kontrolle; eine kryptografische Release-Signatur wird nicht ergänzt.

Die gemeinsame [deckerweb Library 0.8.1](https://github.com/deckerweb/deckerweb-plugin-library/tree/v0.8.1), GPL-2.0-or-later, liegt unter `includes/deckerweb-plugin-library/`. Ihr Manifest fixiert die Laufzeitdateien. Mehrere Hosts wählen eine kompatible gemeinsame Laufzeit. Der Online-Katalog ist optional und standardmäßig aus; bei Aktivierung wird der konfigurierte freigegebene Raw-GitHub-Feed abgefragt. Der Anzeigecache gilt 24 Stunden, der unabhängige Updatecache 12 Stunden. Daily Scripture behält seinen direkten Updater. Komponentenmeldungen nutzen die Host-Textdomain. Beim letzten Host werden Komponenten-Caches und temporäre Dateien bereinigt; Einstellungen und installierte Plugins bleiben standardmäßig erhalten. Die separate Library-Löschoption betrifft nur Library-Einstellungen und Verwaltungsdaten, niemals installierte Plugins oder deren Inhalte.

Vertrauliche Meldungen folgen dem [Sicherheitsmeldeweg](SECURITY-de.md). Vor Releases Zusammenspiel der Komponenten, Einzel-/Sammel-/automatische Updates, abgewiesene Pakete, Artwork und unterstützte Umgebungen prüfen. Laufzeit und nötige Assets mitliefern; Werkzeuge und interne Unterlagen bleiben außerhalb des Installationspakets.
