# User guide · Daily Scripture 0.16.2

[Deutsch](Deutsch.md) · [Home](Home.md)

From your first reading to a design that fits: use the contents to jump straight to the topic you need.

## Contents

- [Installation and first steps](#installation-and-first-steps)
- [Preview, typography and dashboard](#preview-typography-and-dashboard)
- [Shortcodes](#shortcodes)
- [Data, downloads and year changes](#data-downloads-and-year-changes)
- [Bible library and sources](#bible-library-and-sources)
- [Backups and uninstallation](#backups-and-uninstallation)
- [GitHub plugin updates](#github-plugin-updates)
- [Development and translations](#development-and-translations)
- [License](#license)
- [FAQ](#faq)
- [Changelog](#changelog)

<a name="installation-and-first-steps"></a>

## Installation and first steps

Requires WordPress 6.6 or later, PHP 8.0 or later and DOM/XML. ZIP packages require the PHP ZipArchive extension. Elementor and Bricks are optional.

1. Install and activate the plugin ZIP through **Plugins → Add Plugin → Upload Plugin**.
2. Under **Daily Scripture → Datenquellen** (data sources), check the download source, select an annual package, review its terms and import it. Alternatively, upload an official XML/TWD file or its ZIP package.
3. Choose your design on the main settings page, review the preview and click **Einstellungen speichern** (save settings) at the top.
4. Insert the **Daily Scripture** block or builder element. For selected passages, first install an edition under **Bibelbibliothek** (Bible library), then use **Bibelstelle · Daily Scripture**.

Updates preserve settings and installed texts. Reload the editor afterwards; clear browser and website caches if the previous appearance persists. The existing interface is primarily German; these English instructions include the actual German menu labels where helpful.

<a name="preview-typography-and-dashboard"></a>

## Preview, typography and dashboard

At 100%, the default sizes are balanced: headings 28 px, verses 22 px, references 18 px, dates 16 px and additional information 14 px. The overall scale adjusts them together. Expert mode lets you override individual sizes, for example with `1.75rem` or `var(--text-xxl, 28px)`.

CSS variables must exist on your website and resolve to a valid font size. The isolated admin preview does not load variables from Bricks or other frameworks, so it uses the fallback. Relative units alone do not create viewport breakpoints; a responsive CSS variable can provide that behavior. The preview offers several widths and a strict theme-spacing simulation. Also check the published page in your actual theme.

Each dashboard user can choose their own verse size (14, 16 or 18 px), spacing and layout under **Ansicht anpassen** (adjust view). These preferences apply only to that user on the current site. Source headings and date formats follow site settings; colors use the personal WordPress admin accent. The admin preview shows this color context, not the widget's personal reading preferences.

Default headings are **Die Losungen** and, on German-language sites, **Das Wort für heute**. English-language sites use **The Word for Today** for Bible 2.0. Custom global headings also apply to the dashboard; blocks and builder elements can override them. An empty date-format field follows WordPress.

<a name="shortcodes"></a>

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

<a name="data-downloads-and-year-changes"></a>

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

<a name="bible-library-and-sources"></a>

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

<a name="backups-and-uninstallation"></a>

## Backups and uninstallation

JSON exports contain **saved** values. A design includes appearance and date format; a full export adds source selection and other site options. Texts, annual packages, personal dashboard preferences and builder styles are excluded. Imports are staged in the form and take effect only when settings are saved.

Deactivation preserves texts and settings. Uninstallation removes plugin settings and personal widget preferences for this site. Annual data and Bible editions are kept by default. If the removal option is enabled, known text files for the site are also removed; unknown files and protection files remain. Include texts in your regular website file backups when needed.

<a name="github-plugin-updates"></a>

## GitHub plugin updates

Updates come directly from the [DECKERWEB repository on GitHub](https://github.com/deckerweb/daily-scripture/releases) and appear in the **regular WordPress plugin update system** while Daily Scripture is active. Update through Plugins or Dashboard → Updates as usual; no additional updater plugin is needed. Automatic updates remain your choice.

The [English guide](https://github.com/deckerweb/daily-scripture/wiki/English), [German guide](https://github.com/deckerweb/daily-scripture/wiki/Deutsch) and themed FAQs explain settings, sources and common problems. A [local copy](English.md) is included in the plugin. The admin footer links to the documentation and opens the changelog in a dialog. Cached update checks can delay a new offer by up to 30 minutes; a manual ZIP update is also available.

<a name="development-and-translations"></a>

## Development and translations

Namespace: `Deckerweb\DailyScripture`. The shared import, validation and local-storage pipeline supplies all rendering integrations. The `daily_scripture_year_readiness` hook reports annual data readiness; it does not download or delete files.

The plugin name remains **Daily Scripture** in every language. German translation files and the POT template are in `languages/`; source names, shortcode parameters and imported original texts are not renamed. `phpcs.xml.dist` contains the WordPress Coding Standards configuration.

The changelog link beside the version number in the admin footer opens the release history in a dialog. It follows the WordPress admin language (German or English); without JavaScript it opens the readme file.

The complete release history, ordered by **New**, **Improved**, **Fixed** and **Misc**, is in [readme.txt](Changelog-English.md). Author: [David Decker](https://github.com/deckerweb) · [Plugin website](https://github.com/deckerweb/daily-scripture).

<a name="license"></a>

## License

Copyright © 2026 David Decker – deckerweb. The plugin code is licensed under **GPL-2.0-or-later**: you may redistribute and modify it under the GNU General Public License, version 2 or any later version. It is distributed without warranty. See [LICENSE](../LICENSE) for the full license text.

This software license does not cover downloaded Bible texts or annual packages. Their source-specific permissions and attribution requirements are described above.

<a name="faq"></a>

## FAQ

**Do I need Elementor or Bricks?** No. Gutenberg, shortcodes and the dashboard widget work independently of either builder.

**Are Bible texts included in the download?** No. Install annual packages and Bible editions separately in the plugin. Observe the terms of each source.

**Can I replace the official Losungen text with another translation?** No. The official pair stays together and unchanged. Use the separate passage output for your own selections.

**Is next year installed automatically?** No. Check availability under Data sources and start the import yourself before the year changes.

**Does a JSON import immediately change the website?** No. Review it in the form and save to apply it. JSON contains settings, not stored Bible texts.

**Why are yesterday’s verses still showing?** Check the WordPress timezone, installed annual data and page/CDN caches. Cached pages need to refresh at the daily changeover.

**How do updates work?** The bundled deckerweb updater delivers public GitHub releases through regular WordPress updates. No additional updater plugin is required.

[More answers by topic](https://github.com/deckerweb/daily-scripture/wiki/FAQ-English)

<a name="changelog"></a>

## Changelog

[Complete changelog](Changelog-English.md)

[Publishing and checking updates](Development-English.md)
