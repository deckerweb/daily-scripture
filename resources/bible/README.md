# Reviewed Bible sources · 0.8.0

No complete Bible texts are bundled in this plugin. Installation is optional and local. Reviewed on 2026-09-29.

| Edition | Original dataset | Rights evidence | Payload SHA-256 | Canonical verse positions |
| --- | --- | --- | --- | --- |
| Luther 1912 | [eBible VPL ZIP](https://ebible.org/Scriptures/deu1912_vpl.zip) | [Publisher metadata: Public Domain](https://ebible.org/bible/details.php?id=deu1912&all=1) | `f6723776717b2e18e53dc81b0b7758aeb6ce969aee9cfb432d9b61006fa13dff` | 31,102 |
| Elberfelder 1905, unrevised | [eBible VPL ZIP](https://ebible.org/Scriptures/deuelo_vpl.zip) | [Publisher metadata: Public Domain](https://ebible.org/bible/details.php?id=deuelo&all=1) | `a9cd10022e49e80ced94f60e8b781e383cce4a80cff595b95531622804b3e826` | 31,102 |
| Menge 1939 | [Zefania XML, 2010-01-01 release](https://sourceforge.net/projects/zefania-sharp/files/Bibles/GER/Menge-Bibel/SF_2010-01-01_GER_MENG39_%28MENGE-BIBEL%29.zip/download) | [Zefania dataset attribution by Toledot](https://www.toledot.info/die-welt-der-bibel.php?t=info%2Freformation%2Ftextueberarbeitung), [CrossWire rights record for Menge 1939](https://www.crosswire.org/sword/copyright/ModInfoCopyright.jsp?modName=GerMenge) | `dfc6344658e501c997c990e657d5e9d023e8403322b7c14030a3938a43b59c37` | 31,168 |

Menge XML identifies MENG39 and the last 1939 edition; its own rights field is empty, so the two external provenance/rights records are disclosed separately. CrossWire is supporting rights evidence, not the imported file provider.

Fingerprints cover the original decompressed text/XML payload, not the ZIP container. Archive recompression is permitted; changed texts are rejected pending review. Manual uploads must contain the same original payload.

All editions map to 66 canonical books and 1,189 chapters. Extra/apocryphal books and editorial captions are not imported. Original verse text is retained with XML line breaks decoded. No translation or spelling modernization takes place. Fifteen repeated coordinates in the Menge source represent verse parts and are joined with a newline in source order, including the source-specific Acts 8:1 split. Ezekiel 33:14–15 is stored as one text plus its empty marker and can only be selected together; the displayed reference is 14–15. This explains why position counts should not be interpreted as a common versification across editions.

Storage is guarded JSON inside `.json.php` files, read as data rather than executed. Atomic replacement and locks protect existing editions. Download staging is bounded and removed in `finally` blocks. Apache/IIS access-denial files are installed; nginx administrators must also deny direct access to `/wp-content/uploads/daily-scripture/`, especially where PHP execution in uploads is disabled. Multisite uses a per-site subdirectory.

Downloads occur only following an administrator action. User-facing reference links to Bibleserver never retrieve texts. Official annual Losungen/Bible 2.0 content is independent of this library and is never replaced with these translations.

## Schlachter 1951 · added in 0.10.0

This fourth edition is **freely licensed under CC BY 4.0**, rather than labelled Public Domain. The copyright and license statement is present both on [eBible's edition page](https://ebible.org/bible/details.php?id=deu1951&all=1) and its [copyright page](https://ebible.org/deu1951/copyright.htm), and in `deu1951_about.htm` inside the downloaded archive. Copyright © 1951 Geneva Bible Society / Genfer Bibelgesellschaft. Translation by Franz Eugen Schlachter, revised by Genfer Bibelgesellschaft in 1951. This is not Schlachter 2000.

Reviewed original: https://ebible.org/Scriptures/deu1951_vpl.zip, member `deu1951_vpl.txt`.
Payload SHA-256: `3a68f2f8816f3da952b4a5e5da515ce8bf4081c412d77578805cfe7fdd4eb32c`.
66 books, 1,189 chapters, 31,102 verse lines. Reviewed 2026-09-29. The entire parsed verse mapping is compared to the source text in the release tests.

The source package already excludes introductions, notes and noncanonical headings. The plugin converts its verse-per-line representation into guarded local JSON and displays selected passages without changing verse wording. Every passage includes credit, source, the CC BY 4.0 link, the eBible rights record and a technical-processing notice. The admin card also displays these terms. Source revisions require a new reviewed fingerprint. The user's research document was a starting point; its broad Public Domain claim was not adopted for this particular supplied dataset.

Schlachter-specific versification: `MAT 21:44` is an empty source marker (31,102 positions, 31,101 nonempty texts). This is also visible in https://ebible.org/deu1951/MAT21.htm. Positions 45 and 46 retain the original text prefixes `(21-44)` and `(21-45)`. No automatic renumbering or substitution occurs. A requested range including the empty position displays an explicit notice instead of silently omitting it. The fingerprinted empty marker is the only newly permitted empty VPL line; other missing texts still fail validation.
