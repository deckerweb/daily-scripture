# Updates and shared components

[Deutsch](UPDATER-de.md)

This documentation applies to Daily Scripture on single-site WordPress installations and websites in Multisite.

Daily Scripture embeds [deckerweb Updater 2.1.0](https://github.com/deckerweb/deckerweb-updater/tree/v2.1.0) in `includes/deckerweb-github-release-updater-v2.php` (SHA-256 `5e03d5c04e5ec286ef8e99219d231e16054b3cb9215940083ca04e8385ceff9f`), GPL-2.0-or-later, by David Decker – DECKERWEB. It derives from the updater supplied with Brand Admin Schemes. The V2 namespace is guarded; a loaded older copy without host-translation support stops this update integration with an administrator notice. The original V1 copy remains for compatibility and is not loaded by Daily Scripture.

Only stable public releases from `https://github.com/deckerweb/daily-scripture` are offered. Public hosts require no credentials. The host adapter supplies translated engine messages through `daily-scripture`, local SVG/PNG icons and German/English low/high-resolution banners. Automatic updates remain the administrator’s choice.

Metadata uses validated HTTPS, no redirects, a six-second limit and a 512 KiB response limit. Successful responses are cached for 30 minutes, failures for 10 minutes. No verses or settings are transmitted. A release ZIP is preferred; the GitHub source archive is a fallback. Package validation checks the exact main file, name, Update URI, newer version equal to the offered update and candidate WordPress/PHP requirements, including native bulk-update contexts. HTTPS and repository control provide package trust; the updater does not add a cryptographic release signature.

The shared [deckerweb Library 0.8.1](https://github.com/deckerweb/deckerweb-plugin-library/tree/v0.8.1), GPL-2.0-or-later, is embedded in `includes/deckerweb-plugin-library/`. Its manifest pins runtime files. One compatible runtime is elected across installed hosts. The online catalog is optional and off by default; enabling it contacts the configured approved Raw GitHub feed. Its display cache is 24 hours; its independent update cache is 12 hours. This plugin retains its direct updater. Component translations use the host domain. Final-host uninstall cleans component caches and temporary files; settings and installed plugins remain by default. The Library’s separate deletion option affects only Library settings and records, never installed plugins or their content.

For confidential reports, use [the host security policy](SECURITY.md).
