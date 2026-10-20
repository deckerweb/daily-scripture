# Losungen TLS intermediates / TLS-Zwischenzertifikate

Unchanged public CA certificates from Let's Encrypt, retrieved over verified HTTPS on 20 October 2026:

- YR1: https://letsencrypt.org/certs/gen-y/int-yr1.pem
- Root YR cross-signed by ISRG Root X1: https://letsencrypt.org/certs/gen-y/root-yr-by-x1.pem
- Chain documentation: https://letsencrypt.org/certificates/

These are public certificates, not private keys. The plugin validates the intermediate chain against the current WordPress CA bundle before making one verified HTTPS retry for an approved Losungen endpoint. It does not install a global trust root or disable certificate or hostname verification. No CA downloads occur at runtime. A private temporary bundle is deleted after the request. Unrelated hosts and unrelated errors do not use this fallback. A publisher certificate renewal can require reviewed updates to these intermediates.

Unveränderte öffentliche CA-Zertifikate von Let's Encrypt, am 20. Oktober 2026 über geprüftes HTTPS bezogen. Es sind keine privaten Schlüssel enthalten. Vor genau einem erneuten HTTPS-Abruf eines freigegebenen Losungen-Endpunkts wird die Zwischenkette gegen die aktuellen WordPress-Vertrauensanker geprüft. Keine globale Installation neuer Vertrauensanker, keine Abschaltung der Zertifikats- oder Hostnamenprüfung, keine Zertifikatsdownloads zur Laufzeit. Die private temporäre Datei wird anschließend gelöscht. Andere Anbieter und andere Fehler verwenden diesen Fallback nicht. Ein Zertifikatswechsel des Anbieters kann eine geprüfte Aktualisierung erfordern.
