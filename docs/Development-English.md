# Publishing and checking updates

The shared **deckerweb GitHub Release Updater V1** library is bundled unchanged from Brand Admin Schemes 0.15.1. While the plugin is active, it registers WordPress update checks and release details. It targets only the public repository `https://github.com/deckerweb/daily-scripture`. The `Update URI` header prevents confusion with a same-named WordPress.org plugin.

It uses published releases, excluding drafts and releases marked as prereleases. Newer versions appear in standard WordPress update management. Automatic updates are not enabled by the plugin. Without an accessible public repository or release, manual ZIP updates remain available.

**Publishing a release:**

1. Align the plugin version, both TXT readmes and both changelogs. Maintain actual minimum requirements in the plugin header.
2. Build and test a complete installable ZIP rooted at `daily-scripture/`. Include the library, translations and all four readmes; exclude test data and credentials.
3. Publish a stable release in the public repository, tagged `vX.Y.Z` or `X.Y.Z`, and attach `daily-scripture.zip` or `daily-scripture-X.Y.Z.zip`. The ZIP version and tag must agree.
4. The updater prefers that asset. Otherwise the shared library uses GitHub's source archive. The tagged repository root must therefore contain the complete plugin, its main file and all required assets. A tested release ZIP is preferable.
5. Test upgrades in a staging installation, including alongside other deckerweb plugins. Public release publishing and end-to-end downloading are separate from local integration.

Successful metadata requests are cached for 30 minutes; failures for 10 minutes. Manual WordPress checks can reuse that library cache. Metadata requests have a six-second timeout and 512 KiB response limit, with no redirects and HTTPS verification enabled. Registration itself makes no GitHub request and adds no cron job. No GitHub credentials are bundled. GitHub receives normal connection metadata during checks, not Bible texts or plugin settings.

In addition to the library, Daily Scripture validates the extracted package before replacement: expected main file, plugin name, Update URI, a newer version matching the offered update, and the candidate's actual WordPress/PHP requirements. Installed texts under uploads are left alone. Package trust relies on GitHub/HTTPS and control of the release repository; V1 does not provide a separate cryptographic package signature.

The shared library stays byte-identical so several deckerweb plugins can load the same versioned class. Plugin-specific safeguards and translations live in `src/Core/GitHubUpdates.php`. Provenance and SHA-256 are in [UPDATER.md](../UPDATER.md). Future releases should review the library revision, WordPress/PHP compatibility, caching and package validation.
