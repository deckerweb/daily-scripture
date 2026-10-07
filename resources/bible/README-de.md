# Geprüfte Bibelquellen

[English](README.md)

Diese Dokumentation gilt für Daily Scripture auf einzelnen WordPress-Websites (Single Site) und Websites in Multisite.

Das Plugin enthält keine vollständigen Bibeltexte. Die Installation ist optional und erfolgt lokal. Quellenprüfung: 29. September 2026; Originalpakete und sämtliche Verspositionen wurden am 7. Oktober 2026 erneut geprüft.

| Ausgabe | Originalquelle | Rechtebeleg | SHA-256 des entpackten Textes | Verspositionen |
| --- | --- | --- | --- | --- |
| Luther 1912 | [eBible VPL ZIP](https://ebible.org/Scriptures/deu1912_vpl.zip) | [eBible: Public Domain](https://ebible.org/bible/details.php?id=deu1912&all=1) | `f6723776717b2e18e53dc81b0b7758aeb6ce969aee9cfb432d9b61006fa13dff` | 31.102 |
| Elberfelder 1905, unrevidiert | [eBible VPL ZIP](https://ebible.org/Scriptures/deuelo_vpl.zip) | [eBible: Public Domain](https://ebible.org/bible/details.php?id=deuelo&all=1) | `a9cd10022e49e80ced94f60e8b781e383cce4a80cff595b95531622804b3e826` | 31.102 |
| Menge 1939 | [Zefania XML, Ausgabe vom 1. Januar 2010](https://sourceforge.net/projects/zefania-sharp/files/Bibles/GER/Menge-Bibel/SF_2010-01-01_GER_MENG39_%28MENGE-BIBEL%29.zip/download) | [Toledot](https://www.toledot.info/die-welt-der-bibel.php?t=info%2Freformation%2Ftextueberarbeitung), [CrossWire-Rechtebeleg](https://www.crosswire.org/sword/copyright/ModInfoCopyright.jsp?modName=GerMenge) | `dfc6344658e501c997c990e657d5e9d023e8403322b7c14030a3938a43b59c37` | 31.168 |
| Schlachter 1951 | [eBible VPL ZIP](https://ebible.org/Scriptures/deu1951_vpl.zip) | [eBible: CC BY 4.0](https://ebible.org/deu1951/copyright.htm) | `3a68f2f8816f3da952b4a5e5da515ce8bf4081c412d77578805cfe7fdd4eb32c` | 31.102 |

Menge XML kennzeichnet MENG39 und die letzte Ausgabe von 1939. Das Rechtefeld des Datensatzes ist leer; die beiden ergänzenden Herkunfts- und Rechtebelege bleiben deshalb getrennt ausgewiesen. CrossWire ist ein ergänzender Rechtebeleg und liefert nicht die importierte Datei.

Die Fingerprints beziehen sich auf den ursprünglichen entpackten TXT-/XML-Inhalt, nicht auf das ZIP. Neu gepackte Archive sind zulässig, veränderte Texte werden bis zu einer erneuten Prüfung abgelehnt. Manuelle Uploads müssen denselben Originalinhalt enthalten.

Alle Ausgaben verwenden 66 kanonische Bücher und 1.189 Kapitel. Apokryphen, zusätzliche Bücher und redaktionelle Überschriften werden nicht importiert. Der Verswortlaut bleibt erhalten; XML-Zeilenumbrüche werden dekodiert. Es gibt keine Übersetzung oder Rechtschreibmodernisierung. Bei Menge werden 15 wiederholte Koordinaten als Versabschnitte in ursprünglicher Reihenfolge mit einem Zeilenumbruch verbunden, einschließlich der besonderen Aufteilung von Apostelgeschichte 8,1. Hesekiel 33,14–15 besteht aus einem gemeinsamen Text mit leerem Marker und ist nur zusammen auswählbar. Die Anzeige verwendet 14–15. Verszahlen sind deshalb keine ausgabenübergreifend einheitliche Verszählung.

Die Daten liegen als geschütztes JSON in `.json.php`-Dateien und werden gelesen, nicht ausgeführt. Atomarer Austausch und Sperren schützen vorhandene Ausgaben. Temporäre Download-Dateien sind begrenzt und werden auch bei Fehlern entfernt. Apache-/IIS-Schutzdateien werden angelegt. Bei nginx muss der direkte Zugriff auf `/wp-content/uploads/daily-scripture/` zusätzlich gesperrt werden, insbesondere wenn PHP im Uploadverzeichnis nicht ausgeführt wird. Multisite verwendet getrennte Unterordner je Website.

Downloads erfolgen nach einer Administratoraktion. Bibleserver-Verweislinks laden keine Texte. Offizielle Jahrespakete von Losungen und Bible 2.0 bleiben von dieser Bibliothek unabhängig und werden nicht mit deren Textausgaben ersetzt.

## Schlachter 1951

Diese Ausgabe ist unter **CC BY 4.0** frei lizenziert und wird nicht als Public Domain bezeichnet. Die Lizenzangaben stehen auf der [Ausgabenseite](https://ebible.org/bible/details.php?id=deu1951&all=1), der [Copyright-Seite](https://ebible.org/deu1951/copyright.htm) sowie in `deu1951_about.htm` im Originalarchiv. Copyright © 1951 Geneva Bible Society / Genfer Bibelgesellschaft. Übersetzung: Franz Eugen Schlachter; Revision von 1951 durch die Genfer Bibelgesellschaft. Dies ist nicht Schlachter 2000.

Das Originalarchiv enthält `deu1951_vpl.txt`, 66 Bücher, 1.189 Kapitel und 31.102 Verszeilen. Der vollständige gespeicherte Versbestand wird mit dem Original verglichen. Einleitungen, Anmerkungen und nichtkanonische Überschriften sind schon im Ausgangspaket ausgeschlossen. Jede Ausgabe einer Bibelstelle enthält Urheberangabe, Quelle, CC-BY-4.0-Link, eBible-Rechtebeleg und einen Hinweis zur technischen Verarbeitung. Auch die Administrationskarte nennt diese Bedingungen.

`MAT 21:44` ist ein leerer Originalmarker: 31.102 Positionen, davon 31.101 mit Text. Das ist auch in [Matthäus 21 bei eBible](https://ebible.org/deu1951/MAT21.htm) sichtbar. Die Positionen 45 und 46 behalten die ursprünglichen Textpräfixe `(21-44)` und `(21-45)`. Es gibt keine automatische Umnummerierung oder Ersetzung. Ein gewählter Bereich mit der leeren Position zeigt einen ausdrücklichen Hinweis. Nur dieser durch den Fingerprint bestätigte Marker darf leer bleiben; andere fehlende VPL-Texte werden abgelehnt.
