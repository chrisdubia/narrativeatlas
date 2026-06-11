# Say what? Pro
An easy-to-use plugin that allows you to alter strings on your site without editing WordPress core, or plugin code. Simply enter the current string, and what you want to replace it with and the plugin will automatically do the rest! The Pro version includes automated string discovery to make it easier for you to find the strings you need to override.

## Installation
* Install it as you would any other plugin
* Activate it
* Head over to Tools &raquo; Text changes and configure some string replacements - or activate "String discovery" to make it easier to find the strings you want.

## Frequently Asked Questions

### Can I use it to change any string?
You can only use the plugin to translate strings which are marked for translation.

### How do I find the string to translate?
The plugin has a "String Discovery" mode which will capture the strings available for replacement, and let you choose them via an autocomplete system when adding a replacement. Check out the "String Discovery" tab in the plugin settings.

## WP-CLI Support

"Say What?" has preliminary support for exporting, and importing replacements via [http://wp-cli.org/](WP-CLI). The following commands are currently
supported:
* export - Export all current string replacements.
* import - Import string replacements from a CSV file.
* list - Export all current string replacements. Synonym for 'export'.
* update - update string replacements from a CSV file.

Examples:
```
$ wp say-what export
+-----------+-------------+--------+--------------------+---------+
| string_id | orig_string | domain | replacement_string | context |
+-----------+-------------+--------+--------------------+---------+
| 3         | Tools       |        | Yada dada tools!   |         |
+-----------+-------------+--------+--------------------+---------+
```

```
$ wp say-what import import-file.csv
Success: 27 new items created.
```

```
$ wp say-what update update-file.csv
Success: 14 records updated, 19 new items created.
```

## Changelog

### 5.7.4
- Fix: Resolve issue where plugin updater could fault under certain circumstances

### 5.7.3
- Fix: Fix text issues on string discovery page

### 5.7.2
- Fix: Per-language replacements now respect changes to locale made via WordPress' locale switching

### 5.7.1
- Plugin deactivation message not showing correctly if free plugin active on activation

### 5.7.0
- Fix issue where wildcard swaps not always applied

### 5.6.0
- Internationalisation improvements

### 5.5.2
- Updates to Javascript dependencies

### 5.5.1
- Changes to query escaping for compatibility with future WordPress changes

### 5.5.0
- PHP 8.1 compatibility
- Now requires version 7.3 of PHP as minimum
- Minor optimisations

### 5.4.0
- New: Optimise performance when an external object cache is available
- Change: Refactoring of CLI class to avoid code duplication
- Fix: Running an update via WP-CLI would insert rows, not update them, depending on the format of the file provided
- Fix: Running an update via WP-CLI would ignore the active flag depending on the format of the file provided

### 5.3.2
- Fix: Better support for expired licenses

### 5.3.1
- Fix: Plugin updater improvements

### 5.2.1
- Fix: Resolve issue where deprecated parameter warnings could be thrown on recent PHP versions

### 5.2.0
- Change: Require PHP 7.2 or higher
- Fix: Silence warnings generated under PHP 8

### 5.1.1
- Fix: Resolve issue where language-storage for long locales could revert
- Fix: Support for long locales on wildcard replacements

### 5.1.0
- Fix: Resolve issue with language-specific replacements for long locales
- Fix: Pin psr/container to avoid issues with version conflicts

### 5.0.2
- Fix: Resolve table creation issues that occurred on some setups

### 5.0.1
- Fix: Resolve issue with database structure incorrect if plugin installed on v5.0.0

### 5.0.0
- New: Support for disabling replacements
- Fix: Broaden file types accepted for import to avoid issues with browsers sending incorrect MIME types

### 4.0.2
- Fix: Remove dependency on lodash solving some plugin conflicts

### 4.0.1
- Fix: Resolve issue where the plugin wouldn't run on PHP versions older than v7.3

### 4.0.0
- New: Supports overriding strings contained in WordPress Javascript-rendered content (requires WordPress 5.7)

### 3.0.1
- Fix: Resolve issue where listing table not rendered on narrow screen widths

### 3.0.0
- Change: Use domain-specific filters when available for improved performance

### 2.8.0
- New: Add support for searching replacements list
- Fix: String Discovery suggestions that differ only in case sometimes weren't shown
- Fix: Some strings in the plugin not marked up for translation
- Fix: Resolve issue where bulk actions didn't redirect properly on completion

### 2.7.1
- Update: Order replacement suggestions by length for ease of use.

### 2.7.0
- New: Allow language-specific translations to be set without multi-lingual plugin installed  
- New: Support for admin-area user-locales when determining multi-lingual replacements  

### 2.6.1
- Fix: Remove deprecated function warnings when sorting replacement list

### 2.6.0
- Fix: Replacements sometimes didn't pick up correct locale when using polylang

### 2.5.4
- New: When importing replacements, unrecognised IDs will be treated as INSERTs, not ignored
 
### 2.5.3
- New: Add settings link on WordPress plugins page

### 2.5.2
- Fix: PHP minimum version checks didn't always fire correctly.
- New: Online imports can now do insert or replace if ID provided
- New: Include POT file for easy translation of the plugin

### 2.5.1 
- Better autocomplete admin behaviour when no matches / suggestions found
- Internal reworking, and test improvements

### 2.5.0
- Allow searching for strings in string discovery by translated text, not just original

### 2.4.1
- User-interface cleanups to admin screens

### 2.4.0
- If using multi-lingual, sort active languages to the top of the language list.

### 2.3.0
- Add Wildcard Swaps feature
- Add delete bulk actions to list tables

### 2.2.7
- Enable multi-lingual features if WeGlot is active

### 2.2.6
- Re-package 2.2.5 properly.

### 2.2.5
- Better behaviour when activating the Pro plugin while the free version is still active

### 2.2.4
- Fix importer issues with odd Windows mime types
- Fix issue where autocomplete suggestions that differed in case would only show one version

### 2.2.3
- Fix issue where imports could error in some setups.

### 2.2.2
- Fix issue where exports would give "Headers already sent" errors.

### 2.2.1
- Fix issue where "English (United States)" isn't available as a choice when setting up multi-lingual strings.

### 2.2
- Fix performance issues identified under some configurations with v2.1

### 2.1
- Allow different replacements to be set for different languages.

### 2.0.1
- Fix issue with occasional double-encoding on admin screens.

### 2.0
- Introduce import/export feature
- Requires PHP 5.3 or higher
