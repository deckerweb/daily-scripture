=== Daily Scripture ===
Contributors: deckerweb
Tags: bible, scripture, daily, shortcode, block
Requires at least: 6.6
Requires PHP: 8.0
Stable tag: 0.16.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Daily verses and selected passages, stored locally. With previews, ten layouts, Gutenberg, Elementor, Bricks and a compact dashboard widget.

== Description ==

Die Losungen, Bible 2.0 and selected passages from four local Bible editions, styled to suit your website.

* Daily verses from official annual packages: direct downloads with availability status, manual uploads, validation, installed-year overview and deletion.
* Independent Bible library: install Luther 1912, Elberfelder 1905, Menge 1939 and Schlachter 1951 separately for your own passage selections.
* Ten layouts with previews, light and dark appearances, custom colors and proportional typography. Expert controls support px, em, rem, %, and CSS variables.
* Native Gutenberg blocks, Elementor widgets and Bricks elements for daily verses and selected passages. Editor previews and responsive builder style controls.
* Two shortcodes with copyable examples, a compact dashboard widget with personal reading preferences, and custom headings and date formats.
* JSON import and export for design or site settings. Bibleserver reference links with an independently selected target translation.

All text files are stored under wp-content/uploads/daily-scripture/. Bible texts and annual packages are not bundled. Official verse pairs remain unchanged, with source and license notices accessible. The Schlachter 1951 edition uses CC BY 4.0; each source has its own publication terms. Bibleserver is used for links only.

The plugin name remains Daily Scripture in every language. Author: David Decker. See README.md for details and storage paths. German documentation is included as readme-de.txt and README-de.md.

== Plugin updates ==

While the plugin is active, the bundled deckerweb updater checks public releases from https://github.com/deckerweb/daily-scripture through WordPress update management. Results are cached for 30 minutes and failures for 10 minutes. It does not enable automatic updates. No public release means no update offer; manual ZIP updates remain available. Publisher instructions are in README.md.

== Installation ==

1. Upload the plugin ZIP in WordPress, install and activate it. Requires WordPress 6.6 or later, PHP 8.0 or later, DOM/XML, and ZipArchive for ZIP files.
2. Under Daily Scripture → Data sources, download or upload an official annual package and import it.
3. Choose your design on the main settings page, check the preview and save your settings.
4. Add Daily Scripture as a block, builder element or shortcode. For selected passages, first install an edition in the Bible library.

== Frequently Asked Questions ==

= Are new annual packages installed automatically? =
No. The data manager lists available packages; you start the download and import. Day and year changes follow the WordPress timezone. Install the next year in advance and refresh website caches at the start of each day.

= Do I need Elementor or Bricks? =
No. Gutenberg, shortcodes and the dashboard widget work independently of either builder.

= What does a JSON backup contain? =
Your saved design or all plugin site settings. Bible texts, annual packages, personal dashboard preferences and builder styles are excluded. Imported settings are staged in the form and applied only when you save.

= Are my texts kept when I deactivate the plugin? =
Yes. They are also kept by default during uninstallation. Optional data removal deletes known annual files and Bible editions for this site. Plugin settings and personal widget preferences are removed on uninstall.

== Changelog ==

= 0.16.1 =
* Misc: Explicit GPL-2.0-or-later licensing for the plugin code, with the full license and publication package. Bible text permissions remain separate.

= 0.16.0 =
* New: Shared deckerweb GitHub Release Updater library, as used by Brand Admin Schemes, with WordPress update notices and release details.
* Improved: Bounded HTTPS metadata requests, cached results and localized update errors.
* Improved: Package identity, offered version and actual WordPress/PHP requirements checked before replacing plugin files.
* Misc: Update URI, library provenance and public GitHub release instructions documented; automatic updates remain a user choice.


= 0.15.0 =
* New: Changelog dialog beside the version number on every plugin admin page, with German or English release history.
* Improved: English default readmes and separate German editions, with language-matched documentation links.

= 0.14.0 =
* New: German translation files for the plugin and block editor.
* Improved: Concise plugin description covering current features, clearer help and consistent German terminology.
* Improved: Current readme covering setup, design, shortcodes, data management and all four Bible editions.
* Fixed: Outdated storage paths and incomplete notes on data removal, JSON exports and dashboard previews.
* Misc: Complete release history using New, Improved, Fixed and Misc prefixes; refreshed translation template. The plugin name remains Daily Scripture.

= 0.13.0 =
* New: Personal dashboard size, spacing and layout preferences, stored per user and site.
* Improved: Compact widget with logo and settings link; additional settings link in the plugins list.

= 0.12.0 =
* Improved: Shared branded header and navigation, plus version and author footer on every plugin admin page.
* Improved: Consistent cards and responsive annual data tables.
* Fixed: Removed the duplicate save button at the bottom of the settings page.

= 0.11.0 =
* New: Expert font sizes support px, em, rem, percentages and CSS variables with fallbacks.
* Improved: Sticky save bar, size presets, clearer typography guidance and plugin logo.
* Misc: Existing pixel values and JSON backups remain compatible.

= 0.10.0 =
* New: Schlachter 1951 from the reviewed eBible source as a fourth local edition.
* Improved: Author, source and CC BY 4.0 notices across all passage renderers; copyable shortcode example.
* Misc: Documented text provenance and edition-specific details.

= 0.9.0 =
* New: Native Elementor and Bricks passage elements with responsive style controls.
* Improved: Focused help and tips; separate shortcode page with accessible copy buttons.

= 0.8.1 =
* Fixed: Passage preview crash in the block editor by using the correct WordPress component.
* Fixed: Support for different exports of the WordPress server-side preview component.

= 0.8.0 =
* New: Direct downloads of official annual packages with availability status and manual-upload fallback.
* New: Local Luther 1912, Elberfelder 1905 and Menge 1939 library from reviewed text sources.
* New: Live-preview Gutenberg block and shortcode for selected passages; Bible installation, original-package upload and deletion.
* Improved: Bounded HTTPS downloads, approved sources, full validation and atomic installation; explicit replacement only.

= 0.7.0 =
* New: Native typography, color, border and spacing controls for Elementor and Bricks.
* Improved: Responsive style values inherit plugin defaults when left empty.
* Improved: Typography safeguards work with the Bricks preview; license dialogs inherit element typography and colors.
* Misc: Tested with Bricks 2.4.2 and Elementor Free 4.3.2.

= 0.6.0 =
* New: Native Elementor widget and Bricks element using shared server-side rendering.
* Improved: Source, headings, layout, density and theme can follow site settings.
* Improved: Registration independent of load order; builders remain optional.
* Fixed: Disabled Elementor static widget caching for daily verses.

= 0.5.0 =
* New: Gutenberg sidebar settings and daily verse previews on the editor canvas.
* New: Global source headings and per-block titles; Bible 2.0 default heading follows the site language.
* Improved: Shared frontend styles in the block editor, including iframe previews.
* Misc: Existing blocks and settings exports from 0.4 remain compatible.

= 0.4.0 =
* New: Grouped design studio with isolated preview, multiple widths and strict theme-rule simulation.
* New: Ten schematic layout cards, proportional typography and expert size and color controls.
* New: Validated JSON import and export for design or all site settings; imports staged in the form.
* Improved: Protection against excessive theme spacing and preview contrast feedback.

= 0.3.0 =
* New: Four layouts, four heading sizes, compact density, and light, dark and custom colors.
* Improved: Compact presentation with shared title/date header and the WordPress date format.
* Improved: Per-block and shortcode display overrides; dashboard admin accent color.

= 0.2.1 =
* New: Compact Bible 2.0 license disclosure with an accessible dialog.
* Improved: Full imported rights notices retained, with expandable disclosure without JavaScript.

= 0.2.0 =
* New: Validated annual XML, TWD and ZIP import with atomic local storage.
* New: Annual inventory with explicit replacement and confirmed deletion.
* New: Year-readiness check and selection based on the WordPress timezone.
* Improved: Unchanged source texts and rights notices, permitted Losungen year window, and shared rendering.
* Improved: Independent Bibleserver target translation and protected admin actions.
* Misc: PHPDoc, translation template and WordPress Coding Standards checks.

= 0.1.0-prototype =
* New: Initial source and WordPress integration architecture.
