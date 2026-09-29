# Daily Scripture

**English** · [Deutsch](README-de.md)

**Daily Bible readings, styled for your website.**

Die Losungen, Bible 2.0 and selected passages from four locally stored Bible editions: Daily Scripture combines daily reading with a design that fits your site. Includes previews, ten layouts, proportional typography and native Gutenberg, Elementor and Bricks integrations.

## Features

- **Daily verses and selected passages:** official verse pairs from Die Losungen and Bible 2.0, plus an independent library of Luther 1912, Elberfelder 1905, Menge 1939 and Schlachter 1951.
- **Local text storage:** check official providers for annual packages and download them directly, or upload original files manually. Files are fully validated before storage; installed data can be reviewed, explicitly replaced or deleted.
- **Design with a preview:** ten schematic layout choices, light and dark appearances, custom colors and compact layouts for narrow spaces. A single scale adjusts headings, verses, references and notices proportionally.
- **Optional fine-tuning:** individual font sizes and colors, px, em, rem and % units, existing CSS variables with fallbacks, custom date formats and source headings.
- **Native editor controls:** separate Gutenberg blocks for daily verses and selected passages, with sidebar settings and local-text previews. Elementor and Bricks also provide native responsive style controls.
- **Independent of builders:** two shortcodes, a dedicated page of copyable examples and a compact dashboard widget with personal reading preferences.
- **Portable settings:** validated JSON import and export for design or all site settings. Source notices remain accessible. Bibleserver is linked only, with an independently selectable target translation.

## Installation and first steps

Requires WordPress 6.6 or later, PHP 8.0 or later and DOM/XML. ZIP packages require the PHP ZipArchive extension. Elementor and Bricks are optional.

1. Install and activate the plugin ZIP through **Plugins → Add Plugin → Upload Plugin**.
2. Under **Daily Scripture → Datenquellen** (data sources), check the download source, select an annual package, review its terms and import it. Alternatively, upload an official XML/TWD file or its ZIP package.
3. Choose your design on the main settings page, review the preview and click **Einstellungen speichern** (save settings) at the top.
4. Insert the **Daily Scripture** block or builder element. For selected passages, first install an edition under **Bibelbibliothek** (Bible library), then use **Bibelstelle · Daily Scripture**.

Updates preserve settings and installed texts. Reload the editor afterwards; clear browser and website caches if the previous appearance persists. The existing interface is primarily German; these English instructions include the actual German menu labels where helpful.

## Preview, typography and dashboard

At 100%, the default sizes are balanced: headings 28 px, verses 22 px, references 18 px, dates 16 px and additional information 14 px. The overall scale adjusts them together. Expert mode lets you override individual sizes, for example with `1.75rem` or `var(--text-xxl, 28px)`.

CSS variables must exist on your website and resolve to a valid font size. The isolated admin preview does not load variables from Bricks or other frameworks, so it uses the fallback. Relative units alone do not create viewport breakpoints; a responsive CSS variable can provide that behavior. The preview offers several widths and a strict theme-spacing simulation. Also check the published page in your actual theme.

Each dashboard user can choose their own verse size (14, 16 or 18 px), spacing and layout under **Ansicht anpassen** (adjust view). These preferences apply only to that user on the current site. Source headings and date formats follow site settings; colors use the personal WordPress admin accent. The admin preview shows this color context, not the widget's personal reading preferences.

Default headings are **Die Losungen** and, on German-language sites, **Das Wort für heute**. English-language sites use **The Word for Today** for Bible 2.0. Custom global headings also apply to the dashboard; blocks and builder elements can override them. An empty date-format field follows WordPress.

## Shortcodes

Daily verses with saved defaults:

```text
[daily_scripture]
```

Compact Losungen display:

```text
[daily_scripture source="herrnhuter" layout="minimal" density="compact"]
```

A selected passage from a previously installed edition:

```text
[daily_scripture_passage translation="luther-1912" book="JOH" chapter="3" from="16" to="17" title="A word for today"]
```

Daily verses support `source` (`herrnhuter`, `bible2`, `both`). Selected passages support `translation`, `book`, `chapter`, `from`, `to` and `title`. Without `to`, only the first verse is shown. Select up to 50 verses within one chapter. With no custom title, the passage reference becomes the heading.

Both shortcodes support `layout`, `density` (`standard`, `compact`) and `theme` (`light`, `dark`, `auto`, `custom`). Omit display options to inherit site settings. All layout values, book codes and additional copyable examples are listed on the plugin's **Shortcodes** page.

## Data, downloads and year changes

Downloads are explicitly started in the data manager. Source checks list available packages but do not install the next year automatically. Successful catalogue checks are cached for six hours, failed checks for five minutes. Manual uploads remain available when a provider or connection is unavailable.

Annual files must contain a complete calendar year with complete verse pairs. Existing years are replaced only after successful validation and explicit selection. Day and year changes follow the WordPress timezone. Missing data produces a notice instead of reusing old verses. The annual overview indicates missing data for the year transition. Refresh page and CDN caches at the start of each day.

All plugin-managed text files use these paths relative to `wp-content/uploads/`:

```text
daily-scripture/herrnhuter/YYYY.json.php
daily-scripture/bible2/YYYY.json.php
daily-scripture/bibles/luther-1912.json.php
daily-scripture/bibles/elberfelder-1905.json.php
daily-scripture/bibles/menge-1939.json.php
daily-scripture/bibles/schlachter-1951.json.php
```

`daily-scripture/downloads/` provides temporary download staging. There is no additional `data/` directory. Individual multisite activation separates data under `daily-scripture/sites/BLOG_ID/`; network activation is not supported. Renamed content directories use `WP_CONTENT_DIR/uploads/daily-scripture/`. Custom upload locations are not used for these text files.

Protection files, PHP exit guards, path validation and locked writes protect the local store. Browser uploads initially enter PHP's configured upload temporary directory. Plugin settings and short-lived status messages are stored in the WordPress database.

## Bible library and sources

Full Bible texts and annual packages are **not bundled in the plugin ZIP**. The library accepts the linked text editions verified by their fingerprints. Other revisions require a new source review. Each import includes 66 books, without additional Apocrypha or editorial headings.

| Edition | Text source | Designation of the edition used |
| --- | --- | --- |
| Luther 1912 | [eBible](https://ebible.org/bible/details.php?id=deu1912) | Public domain |
| Elberfelder 1905, unrevised | [eBible](https://ebible.org/bible/details.php?id=deuelo) | Public domain |
| Menge 1939 | [Zefania text package](https://sourceforge.net/projects/zefania-sharp/files/Bibles/GER/Menge-Bibel/) | Public domain; supporting references in the library |
| Schlachter 1951 | [eBible](https://ebible.org/deu1951/copyright.htm) | CC BY 4.0, with attribution and license link |

Verse numbering can vary. The Menge file combines Ezekiel 33:14–15; select both verses together. The Schlachter file has no text at Matthew 21:44 and uses different subsequent verse numbers. Daily Scripture preserves the source file's text and numbering and provides a notice for affected selections.

Official daily sources remain independent of this library. Losung and Lehrtext are shown together, unchanged. Losungen import and display are limited to the previous, current and next year. Observe the [Losungen terms](https://www.losungen.de/digital/nutzungsbedingungen/). Bible 2.0 is subject to its [project terms](https://bible2.net/en/copyright) and the notices of the selected Bible edition. Complete imported license information remains accessible through expandable content or a dialog, including without JavaScript.

Bibleserver is used solely as an external link destination. No texts are retrieved from it, and the selected target translation does not alter the displayed verse text.

## Backups and uninstallation

JSON exports contain **saved** values. A design includes appearance and date format; a full export adds source selection and other site options. Texts, annual packages, personal dashboard preferences and builder styles are excluded. Imports are staged in the form and take effect only when settings are saved.

Deactivation preserves texts and settings. Uninstallation removes plugin settings and personal widget preferences for this site. Annual data and Bible editions are kept by default. If the removal option is enabled, known text files for the site are also removed; unknown files and protection files remain. Include texts in your regular website file backups when needed.

## GitHub plugin updates

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

The shared library stays byte-identical so several deckerweb plugins can load the same versioned class. Plugin-specific safeguards and translations live in `src/Core/GitHubUpdates.php`. Provenance and SHA-256 are in [UPDATER.md](UPDATER.md). Future releases should review the library revision, WordPress/PHP compatibility, caching and package validation.

## Development and translations

Namespace: `Deckerweb\DailyScripture`. The shared import, validation and local-storage pipeline supplies all rendering integrations. The `daily_scripture_year_readiness` hook reports annual data readiness; it does not download or delete files.

The plugin name remains **Daily Scripture** in every language. German translation files and the POT template are in `languages/`; source names, shortcode parameters and imported original texts are not renamed. `phpcs.xml.dist` contains the WordPress Coding Standards configuration.

The changelog link beside the version number in the admin footer opens the release history in a dialog. It follows the WordPress admin language (German or English); without JavaScript it opens the readme file.

The complete release history, ordered by **New**, **Improved**, **Fixed** and **Misc**, is in [readme.txt](readme.txt). Author: [David Decker](https://github.com/deckerweb) · [Plugin website](https://github.com/deckerweb/daily-scripture).

## License

Copyright © 2026 David Decker – deckerweb. The plugin code is licensed under **GPL-2.0-or-later**: you may redistribute and modify it under the GNU General Public License, version 2 or any later version. It is distributed without warranty. See [LICENSE](LICENSE) for the full license text.

This software license does not cover downloaded Bible texts or annual packages. Their source-specific permissions and attribution requirements are described above.
