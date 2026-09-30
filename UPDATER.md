# DECKERWEB GitHub Release Updater — API v2

Copyright © 2026 David Decker – DECKERWEB. GPL-2.0-or-later.

Canonical local distribution for future deckerweb plugins. Versioned namespace: `Deckerweb\GitHubReleaseUpdater\V2`.
Derived from the API v1 library supplied with Brand Admin Schemes 0.15.1. API v1 is not overwritten: both versions can coexist, regardless of plugin loading order. Keep the V2 constructor and behavior backward compatible; use a new namespace for incompatible future changes.

## Integration

Load the file only when the V2 class does not exist. Instantiate once per plugin after translations and the admin locale are available, then call `register()`:

```php
if ( ! class_exists( '\\Deckerweb\\GitHubReleaseUpdater\\V2\\Updater' ) ) {
    require_once __DIR__ . '/includes/deckerweb-github-release-updater-v2.php';
}
$updater = new \Deckerweb\GitHubReleaseUpdater\V2\Updater(
    $main_plugin_file,
    'https://github.com/owner/plugin-slug',
    $plugin_name,
    $description,
    array(
        'icons' => array(
            'svg' => plugins_url( 'assets/icon.svg', $main_plugin_file ),
            '1x' => plugins_url( 'assets/icon-128x128.png', $main_plugin_file ),
            '2x' => plugins_url( 'assets/icon-256x256.png', $main_plugin_file ),
        ),
        'banners' => array(
            'low' => plugins_url( 'assets/banner-772x250.png', $main_plugin_file ),
            'high' => plugins_url( 'assets/banner-1544x500.png', $main_plugin_file ),
        ),
    )
);
$updater->register();
```

Use actual repository identity and bundled files. Match artwork to the administrator's locale when localized banners exist. The fifth argument is optional; integrations without artwork retain WordPress's standard presentation. The maps accept only their documented keys and absolute HTTP(S) URLs without credentials; unsupported schemes, relative URLs and control characters are rejected. Local plugin URLs are preferred: rendering needs no additional external image provider. The library does not fetch or probe artwork. Use only trusted, reviewed SVG files.

Icons are supplied in update offers and existing cached offers. Banners are supplied by `plugins_api` for the plugin-information dialog, including its Changelog tab. No update is invented if none exists. WordPress renders the banner and overlays its own plugin title; allow room for that title near the lower left. WordPress handles normal/high-resolution selection.

Release caching remains 30 minutes for success and 10 minutes for failures. Artwork never changes cache lifetime, triggers a release refresh, enables automatic updates or adds credentials. Repository and plugin isolation remain intact. Existing plugin-specific request limits and candidate package compatibility checks must stay registered; they are not replaced by artwork configuration.

Before distributing another plugin: verify PNG dimensions, language selection, icon and details-dialog rendering, empty/error responses, V1/V2 coexistence, package validation and HTTPS behavior. Include this library and the assets in its release ZIP. Existing deployed V1 integrations do not gain artwork automatically; migrate and release them individually.

## Provenance

V1 source SHA-256: `2e46a3b5b3a0a6ef46f145b8e244b4ca1521071aa055a6ec4a6390847e1e2eff`.
V2 distribution SHA-256: `252c9cae6f7590ef7d39799e3d95ed813fc3b2e846497870b244f42e3d2cc035`.

WordPress behavior verified against local core `wp-admin/includes/plugin-install.php` (banners low/high) and `wp-admin/update-core.php` (icons svg/2x/1x/default). Functional tests run against WordPress 6.8.3 and PHP 8.2.27; no claim of testing other installed versions.

Daily Scripture configures localized bundled artwork in `src/Core/GitHubUpdates.php`. That bridge also retains bounded HTTPS requests, localized errors, package identity and candidate compatibility checks.

The original V1 file is retained unchanged as a legacy compatibility copy. Daily Scripture itself loads V2 only.
