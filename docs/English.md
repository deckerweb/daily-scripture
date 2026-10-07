# User guide · Daily Scripture 1.0.0

[Deutsch](Deutsch.md) · [Home](Home.md)

Daily Scripture adds daily readings, selected Bible passages and personal dashboard widgets to your WordPress website. Choose your sources, shape the design and display readings with Gutenberg, shortcodes, Elementor or Bricks. Website features also work in Multisite, with an additional Network Admin reading view.

From your first reading to a design that fits: use the contents to jump straight to the topic you need.

## Contents

- [Installation and first steps](#installation-and-first-steps)
- [Preview, typography and dashboard](#preview-typography-and-dashboard)
- [Shortcodes](#shortcodes)
- [Data, downloads and year changes](#data-downloads-and-year-changes)
- [Bible library and sources](#bible-library-and-sources)
- [Backups and uninstallation](#backups-and-uninstallation)
- [GitHub plugin updates](#github-plugin-updates)
- [License](#license)
- [FAQ](#faq)
- [Changelog](#changelog)

<a name="installation-and-first-steps"></a>

## Installation and first steps

Requires WordPress 6.6 or later, PHP 8.0 or later and DOM/XML. ZIP packages require the PHP ZipArchive extension. Elementor and Bricks are optional.

1. Install and activate the plugin ZIP through **Plugins → Add Plugin → Upload Plugin**.
2. Under **Daily Scripture → Data sources** (data sources), check the download source, select an annual package, review its terms and import it. Alternatively, upload an official XML/TWD file or its ZIP package.
3. Choose your design on the main settings page, review the preview and click **Einstellungen speichern** (save settings) at the top.
4. Insert the **Daily Scripture** block or builder element. For selected passages, first install an edition under **Bible library** (Bible library), then use **Bible passage · Daily Scripture**.

Updates preserve settings and installed texts. Reload the editor afterwards; clear browser and website caches if the previous appearance persists. The interface is available in English, German and formal German.

<a name="preview-typography-and-dashboard"></a>

## Preview, typography and dashboard

At 100%, the default sizes are balanced: headings 28 px, verses 22 px, references 18 px, dates 16 px and additional information 14 px. The overall scale adjusts them together. Expert mode lets you override individual sizes, for example with `1.75rem` or `var(--text-xxl, 28px)`.

CSS variables must exist on your website and resolve to a valid font size. The isolated admin preview does not load variables from Bricks or other frameworks, so it uses the fallback. Relative units alone do not create viewport breakpoints; a responsive CSS variable can provide that behavior. The preview offers several widths and a strict theme-spacing simulation. Also check the published page in your actual theme.

Each dashboard user can choose their own verse size (14, 16 or 18 px), spacing and layout under **Display options** (adjust view). These preferences apply only to that user on the current site. Source headings and date formats follow site settings; colors use the personal WordPress admin accent. The admin preview shows this color context, not the widget's personal reading preferences.

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
daily-scripture/data/herrnhuter/YYYY.json.php
daily-scripture/data/bible2/YYYY.json.php
daily-scripture/bibles/luther-1912.json.php
daily-scripture/bibles/elberfelder-1905.json.php
daily-scripture/bibles/menge-1939.json.php
daily-scripture/bibles/schlachter-1951.json.php
```

`daily-scripture/downloads/` provides temporary download staging. Annual files use the `data/` subdirectory. In Multisite, per-site activation separates data under `daily-scripture/sites/BLOG_ID/`; new sites inherit defaults lazily. Renamed content directories use `WP_CONTENT_DIR/uploads/daily-scripture/`. Custom upload locations are not used for these text files.

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

Deactivation preserves texts and settings. Uninstallation clears temporary caches and scheduled checks. Settings, personal widget preferences, annual data and Bible editions are kept by default. If the removal option is enabled, settings, preferences and known text files for the site are removed; unknown files and protection files remain. Include texts in your regular website file backups when needed.

<a name="github-plugin-updates"></a>

## GitHub plugin updates

Updates come directly from the [DECKERWEB repository on GitHub](https://github.com/deckerweb/daily-scripture/releases) and appear in the **regular WordPress plugin update system** while Daily Scripture is active. Update through Plugins or Dashboard → Updates as usual; no additional updater plugin is needed. Automatic updates remain your choice.

The [English guide](https://github.com/deckerweb/daily-scripture/wiki/English), [German guide](https://github.com/deckerweb/daily-scripture/wiki/Deutsch) and themed FAQs explain settings, sources and common problems. A [local copy](English.md) is included in the plugin. The admin footer links to the documentation and opens the changelog in a dialog. Cached update checks can delay a new offer by up to 30 minutes; a manual ZIP update is also available.

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

## Dashboard readings and color picker

Define available readings in **Daily Scripture → Dashboard readings**: Die Losungen, Bible 2.0 and up to six passages from installed local Bible editions (up to 50 verses in one chapter). Save the website settings, then open **Display options** in the widget. Select one or more readings and use **Move up / Move down** to order them. Without JavaScript, numeric positions remain available. No configured readings produces a quiet notice; missing yearly data or an unavailable edition is reported per reading. Personal choices do not change website output. Full JSON settings exports include the reading definitions; design-only and older exports leave them unchanged. Install the required editions on the destination website before saving imported passage definitions.

The Network Admin widget uses a personally selected active website in the current network and visibly names it. When only the main website is active, it is used automatically. With multiple active websites or network activation, select the source website; save once after changing it to load its configured readings, then choose your personal selection. An unavailable previous website falls back with a notice. The network widget is available when the plugin is active on the main website or network-wide; activation only on a subsite does not load it in Network Admin. The website widget switch affects that website's dashboard; the network reading view is independent. Website choices are paginated in groups of 50. No separate network Bible storage is created.

Color controls use the modern WordPress color picker in a WordPress modal, with direct hex entry and optional text-color inheritance. Changes remain in the form until you save the settings.
