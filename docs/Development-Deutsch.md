# Updates veröffentlichen und prüfen

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

Die gemeinsame Bibliothek bleibt bytegleich, damit mehrere deckerweb-Plugins dieselbe versionierte Klasse laden können. Pluginbezogene Schutz- und Übersetzungsregeln liegen in `src/Core/GitHubUpdates.php`. Herkunft und SHA-256 stehen in [UPDATER.md](../UPDATER.md). Bei weiteren Releases gehören Bibliotheksstand, WordPress-/PHP-Kompatibilität, Cacheverhalten und Paketvalidierung zur Prüfung.
