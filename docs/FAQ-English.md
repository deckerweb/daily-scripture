# Frequently asked questions

[English](English.md) · [Deutsch](FAQ-Deutsch.md) · [Home](Home.md)

## Topics

- [Getting started](#getting-started)
- [Annual data and downloads](#annual-data-and-downloads)
- [Bible editions and rights notices](#bible-editions-and-rights-notices)
- [Layouts and typography](#layouts-and-typography)
- [Editors, links and dashboard](#editors-links-and-dashboard)
- [Backups and stored data](#backups-and-stored-data)
- [Updates and troubleshooting](#updates-and-troubleshooting)

<a name="getting-started"></a>

## Getting started

### What do I need?

WordPress 6.6 or later, PHP 8.0 or later and DOM/XML. ZIP imports require ZipArchive. The plugin can run without a page builder.

### Who can manage sources and settings?

The administration pages require the WordPress manage_options capability, normally assigned to administrators. Dashboard reading preferences are personal.

### Which output should I choose?

Use Daily Scripture for the official daily readings. Use Bibelstelle · Daily Scripture for a passage you select from an installed Bible edition.

### Are texts already installed?

No. Start with an annual package under Datenquellen or an edition under Bibelbibliothek. The plugin ZIP contains software and documentation, not Bible texts.

### Can I use multisite?

Activate separately on each site. Network activation is not supported; each site receives its own data directory.


<a name="annual-data-and-downloads"></a>

## Annual data and downloads

### Does checking availability import anything?

No. It lists available source packages. Select the package and start the download/import yourself.

### What if a download is unavailable?

Try the source again later or upload its linked original package manually. Have the host investigate TLS or connection errors rather than disabling certificate checks.

### Can I replace an installed year?

Yes, with explicit replacement selected. The incoming year is validated before it replaces existing data; a failed validation keeps the previous files.

### What happens at New Year?

The active year follows the WordPress timezone. Install the next package in advance. Missing data produces a notice; old verses are not silently reused.

### Can I install any Losungen year?

Import and display are limited to the previous, current and next year. Annual packages must include the full year and complete daily pairs.


<a name="bible-editions-and-rights-notices"></a>

## Bible editions and rights notices

### Which editions are supported?

Luther 1912, unrevised Elberfelder 1905, Menge 1939 and Schlachter 1951. Install them individually from the reviewed sources listed in the library.

### Are all four sources public domain?

No. The reviewed Luther, Elberfelder and Menge sources are designated public domain. The used Schlachter 1951 source is CC BY 4.0 and retains attribution and its license link.

### Does selecting a Bible edition translate the Losungen?

No. Official daily texts stay unchanged and paired. Your chosen local translation is used only for independent passage selections.

### Why can a verse number be missing?

Edition numbering differs. Menge combines Ezekiel 33:14–15; select both together. The Schlachter file has no text at Matthew 21:44 and differs in subsequent numbering. The source numbering is preserved.

### May I remove copyright notices?

Keep the notices supplied with each source accessible. Bible 2.0 can present the detailed information in a dialog, with an expandable fallback without JavaScript. Software licensing does not replace text permissions.


<a name="layouts-and-typography"></a>

## Layouts and typography

### Where should I start with font sizes?

Choose a layout, then adjust the overall scale. At 100%, the defaults are heading 28 px, verse 22 px, reference 18 px, date 16 px and notices 14 px.

### Which layouts are available?

Card, Minimal, Accent, Editorial, Paper, Ribbon, Outline, Divided, Journal and Quiet. The selection cards show their structure; compact density is a separate choice.

### Can I use CSS variables from Bricks or Core Framework?

Yes, if they exist on the displayed page and resolve to a font size. Use a fallback such as var(--text-xxl, 28px). The isolated admin preview uses the fallback.

### Are relative units automatically responsive?

em, rem and % are relative to their font context, not automatic device breakpoints. Use a responsive CSS variable or the builder’s responsive controls when sizes should change by viewport.

### Why does my website differ from the preview?

The preview is isolated from theme CSS and external variables. Check actual theme and builder overrides, element settings and caches. The strict spacing simulation helps, but cannot reproduce every theme.


<a name="editors-links-and-dashboard"></a>

## Editors, links and dashboard

### Where are Gutenberg controls?

Select the block and open the block sidebar. The canvas shows a server-rendered preview using installed local data; each block can override saved defaults.

### Can Elementor and Bricks use their own styling?

Yes. Native controls set typography, colors, borders and spacing, including responsive choices. Empty controls inherit plugin defaults.

### How do I change headings and the date?

Set source headings in the main settings; blocks and builder elements can override them. An empty date format follows WordPress. Bible 2.0 defaults to Das Wort für heute on German sites and The Word for Today otherwise.

### How do I make the dashboard smaller?

Use Ansicht anpassen in the widget to choose compact spacing and a 14, 16 or 18 px verse size. The choice is saved per user and site and does not change the frontend.

### Does Bibleserver supply the displayed text?

No. References link to Bibleserver. The target translation is independently configurable and does not change local text.


<a name="backups-and-stored-data"></a>

## Backups and stored data

### What is included in JSON exports?

Saved design or all plugin site settings. Texts, annual packages, personal widget preferences and builder styles are excluded. Unsaved form changes are not exported.

### When does a settings import take effect?

After you review the imported values in the form and save them. Importing the JSON alone does not change the saved design.

### Where are annual packages and Bible texts stored?

Under wp-content/uploads/daily-scripture/: herrnhuter/YYYY.json.php, bible2/YYYY.json.php and bibles/TRANSLATION.json.php. The guide lists each edition’s exact filename.

### Does deactivation delete my texts?

No. Deactivation preserves texts and settings. Uninstalling removes plugin settings and personal widget choices; known text files are deleted only if the removal option was enabled.

### Is JSON enough for a full backup?

No. Also back up the WordPress database, local text directory and any builder templates through your normal backup system. JSON helps transfer settings.


<a name="updates-and-troubleshooting"></a>

## Updates and troubleshooting

### How do I install an update?

While the plugin is active, public GitHub releases appear in WordPress Plugins or Dashboard → Updates. No additional updater plugin is needed. Manual installation of the release ZIP also works.

### Why is the new version not offered yet?

Successful metadata checks are cached for 30 minutes and failures for 10 minutes. Check again later. Drafts and GitHub prereleases are excluded; hosting restrictions may also block GitHub.

### Are automatic updates enabled for me?

No. You decide whether WordPress should install updates automatically. The built-in updater supplies the update information and package.

### Why is a Gutenberg preview failing?

Reload the editor and clear relevant browser or builder caches after updating. Check that the selected text data is installed. If it persists, include the exact error and reproduction steps in an issue without credentials.

### What belongs in a useful bug report?

Plugin, WordPress and PHP versions, editor and theme, affected source/year or passage, expected and actual result, and repeatable steps. Screenshots help; do not include passwords, license keys or private customer data.

