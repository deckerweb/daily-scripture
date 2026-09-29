# Shared deckerweb updater provenance

- Component: DECKERWEB GitHub Release Updater, API v1.
- Namespace: `Deckerweb\GitHubReleaseUpdater\V1`.
- Source: `brand-admin-schemes-0.15.1.zip`, `brand-admin-schemes/includes/deckerweb-github-release-updater-v1.php`, supplied local deckerweb distribution.
- Bundled file: `includes/deckerweb-github-release-updater-v1.php`.
- SHA-256: `2e46a3b5b3a0a6ef46f145b8e244b4ca1521071aa055a6ec4a6390847e1e2eff`.
- Copyright © 2026 David Decker – DECKERWEB; SPDX GPL-2.0-or-later, as declared in the original file.
- The library is unmodified. Its existing formatting is excluded from plugin WPCS checks; syntax and behavior are tested. The plugin-owned integration follows the plugin coding standard.
- `src/Core/GitHubUpdates.php` adds bounded verified metadata requests, localized package errors, package identity/version checks and actual candidate compatibility checks. These apply only to Daily Scripture.
- Do not silently fork the shared V1 class: the first deckerweb plugin to load it supplies the class to other plugins. Review API compatibility and coexistence when upgrading the library.
- See README.md / README-de.md for release packaging and operational limits.

References checked for this integration:

- https://developer.wordpress.org/reference/hooks/update_plugins_hostname/
- https://docs.github.com/en/rest/releases/releases#get-the-latest-release
- Local WordPress 6.8.3 plugin upgrader source for package selection and compatibility behavior.
