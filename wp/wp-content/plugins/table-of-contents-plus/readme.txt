=== Table of Contents Plus ===
Contributors: conjur3r
Donate link: 
Tags: table of contents, indexes, toc, sitemap, cms, options, list, page listing, category listing
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 1208
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A powerful yet user friendly plugin that automatically creates a table of contents. Can also output a sitemap listing all pages and categories.


== Description ==

A powerful yet user friendly plugin that automatically creates a context specific index or table of contents (TOC) for long pages (and custom post types).  More than just a table of contents plugin, this plugin can also output a sitemap listing pages and/or categories.

Built from the ground up and with Wikipedia in mind, the table of contents by default appears before the first heading on a page.  This allows the author to insert lead-in content that may summarise or introduce the rest of the page.  It also uses a unique numbering scheme that doesn't get lost through CSS differences across themes.

This plugin is a great companion for content rich sites such as content management system oriented configurations.  That said, bloggers also have the same benefits when writing long structured articles.

Includes an administration options panel where you can customise settings like display position, define the minimum number of headings before an index is displayed, appearance, etc.  Using shortcodes, you can override default behaviour such as special exclusions on a specific page or even to hide the table of contents altogether.

Custom post types are supported, however, auto insertion works only when the_content() has been used by the custom post type.  Each post type will appear in the options panel, so enable the ones you want.

= Available Languages =
* Australian English (default)
* Simplified Chinese - [icedream](http://www.tesfans.org/)

Translations are more than welcome.

If you have questions or suggestions, please place them at [http://dublue.com/plugins/toc/](http://dublue.com/plugins/toc/)


== Screenshots ==

1. An example of the table of contents, positioned at the top and right aligned
2. The main options tab in the administration area
3. The sitemap options tab


== Installation ==

The normal plugin install process applies, that is search for `table of contents plus` from your plugin screen or via the manual method:

1. Upload the `table-of-contents-plus` folder into your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

That's it!  The table of contents will appear on pages with at least four or more headings.

You can change the default settings and more under Settings > TOC+

This plugin requires PHP 5.


== Shortcodes ==

When attributes are left out for the shortcodes below, they will fallback to the settings you defined under Settings > TOC+.  The following are detailed in the help tab.

= [toc] =
Lets you generate the table of contents at the preferred position.  Useful for sites that only require a TOC on a small handful of pages.  Supports the following attributes:
* "label": text, title of the table of contents
* "no_label": true/false, shows or hides the title
* "wrapping": text, either "left" or "right"
* "heading_levels": numbers, this lets you select the heading levels you want included in the table of contents.  Separate multiple levels with a comma.  Example: include headings 3, 4 and 5 but exclude the others with `heading_levels="3,4,5"`

= [no_toc] =
Allows you to disable the table of contents for the current post, page, or custom post type.

= [sitemap] =
Produces a listing of all pages and categories for your site. You can use this on any post, page or even in a text widget.

= [sitemap_pages] =
Lets you print out a listing of only pages. The following attributes are accepted:
* "heading": number between 1 and 6, defines which html heading to use
* "label": text, title of the list
* "no_label": true/false, shows or hides the list heading
* "exclude": IDs of the pages or categories you wish to exclude

= [sitemap_categories] =
Same as `[sitemap_pages]` but for categories.


== Changelog ==

= 1208 =
* Released: 2 August 2012
* New: advanced option to prevent the output of this plugin's CSS.  This option allows the site owner to incorporate styles in one of their existing style sheets.  Thanks to [Ivan](http://dublue.com/plugins/toc/comment-page-1/#comment-226) and [Swashata](http://dublue.com/plugins/toc/comment-page-3/#comment-3312) for suggesting it.
* Added Simplified Chinese translation thanks to [icedream](http://www.tesfans.org/)
* Make more translatable by adding a translation POT file in the languages folder.  Translations welcome!
* Adjust multibyte string detection as reported by [johnnyvaughan](http://wordpress.org/support/topic/plugin-table-of-contents-plus-multibyte-string-detetction)
* Support PHP 5.4.x installations.  Thanks to [Josh](http://dublue.com/plugins/toc/comment-page-3/#comment-3477) for raising it.
* Fixed: -2 appearing in links when using the TOC+ widget.  Thanks to [Arturo](http://dublue.com/plugins/toc/comment-page-3/#comment-3337) for raising it.

= 1207 =
* Released: 23 July 2012
* New: when smooth scrolling is enabled, allow the top offset to be specified to support more than the WordPress admin bar (such as Twitter Bootstrap).  The offset is displayed in the advanced section after you have enabled smooth scrolling.  Thanks to [Nicolaus](http://dublue.com/2012/05/12/another-benefit-to-structure-your-web-pages/#comment-2611) for the suggestion.
* Allow 2 headings to be set as the minimum (used to be 3).  Thanks to [Fran](http://dublue.com/plugins/toc/comment-page-2/#comment-779) for justifying it.
* Run later in the process so other plugins don't alter the anchor links (eg Google Analytics for WordPress).
* Do not show a TOC in RSS feeds.  Thanks to [Swashata](http://dublue.com/plugins/toc/comment-page-3/#comment-2875) for raising it.
* Bump tested version to WordPress 3.5-alpha.
* Added help material about why some headings may not be appearing.
* Added banner image for WordPress repository listing.
* Updated readme.txt with GPLv2 licensing.

= 1112.1 =
* Released: 9 December 2011
* Forgot to update version number.

= 1112 =
* Released: 9 December 2011
* New: auto width option added which takes up only the needed amount of horizontal space up to 100%.
* Removed trailing - or _ characters from the anchor to make it more pretty.
* This plugin's long name has changed from "Table of Contents+" to "Table of Contents Plus".  The short name remains as "TOC+".
* Fixed: when using the TOC shortcode within your content, your post or article would display the TOC on the homepage despite having the exclude from homepage option enabled.  If you also used the "more tag", then you may have resulted with an empty TOC box.  These are now addressed.
* Fixed: all anchors ending with "-2" when no headings were repeated.  This was caused by plugins and themes that trigger `the_content` filter.  The counters are now reset everytime `the_content` is run rather than only on initialisation.

= 1111 =
* Released: 11 November 2011
* New: option to adjust the font size.  Thanks to [DJ](http://dublue.com/plugins/toc/comment-page-1/#comment-323) for the suggestion.  The default remains at 95%.
* New: advanced option to select the heading levels (1 to 6) to be included.  Thanks to those that hinted about wanting to achieve this.
* New: you can now have the TOC appear in the sidebar via the TOC+ widget.  Thanks to [Nick Daugherty](http://dublue.com/plugins/toc/comment-page-1/#comment-172) and [DJ](http://dublue.com/plugins/toc/comment-page-1/#comment-323) for the suggestion.
* The TOC shortcode now supports the *heading_levels* attribute to allow you to limit the headings you want to appear in the table of contents on a per instance basis.  Separate multiple headings with a comma.  For example: include headings 3, 4 and 5 but exclude the others with `[toc heading_levels="3,4,5"]`
* The TOC shortcode also supports the *wrapping* attribute with possible values: "left" or "right".  This lets you wrap text next to the table of contents on a per instance basis.  Thanks to [Phil](http://dublue.com/plugins/toc/comment-page-1/#comment-331) for the suggestion.
* Better internal numbering system to avoid repeated headings.  This means that for non-repeated headings, there is no trailing number in the anchor.
* Consolidated information about shortcodes and their attributes into the help tab.
* Fixed: repeated headings on the same level are no longer broken.  For users with international character sets, please report any strange garbage characters in your headings (eg a character ends up being a question mark, square symbol, or diamond).  Thanks to [Juozas](http://dublue.com/plugins/toc/comment-page-2/#comment-441) for the assistance.
* Fixed: removed PHP notices on a verbosely configured PHP setup.
* Fixed: suppress TOC frame output when heading count was less than the minimum required.
* Note: when removing the last TOC+ widget, please make sure you disable the "Show the table of contents only in the sidebar" option otherwise your table of contents won't appear where you'd expect.  I will look to address this in the future.

= 1109 =
* Released: 12 September 2011
* Adjusted hide action for a smoother transition.
* Apply custom link and hover colours (when selected) to show/hide link in the title.
* Renamed jquery.cookie.min.js to jquery.c.min.js to overcome false positive with [mod_security](https://www.modsecurity.org/tracker/browse/CORERULES-29).  Mod_security would block requests to this file which would break the ability to save a user's show/hide preference.  In some cases, it has also broken other javascript functionality.  Additionally, a better graceful non disruptive fallback is now in place to prevent possible repeat.  Thanks goes to Shonie for helping debug the issue.
* Moved 'visibility option' into 'heading text'.
* Fixed: restored smooth scroll effect for Internet Explorer since 1108.2 introduced 'pathname' checks.

= 1108.2 =
* Released: 26 August 2011
* New: visibility option to show/hide the table of contents.  This option is enabled by default so if you don't want it, turn it off in the options.  Thanks to [Wafflecone](http://dublue.com/plugins/toc/#comment-123) and [Mike](http://dublue.com/plugins/toc/comment-page-1/#comment-160) for the suggestion.
* New: transparent presentation option added.
* New: custom presentation option with colour wheel for you to select your own background, border, title and link colours.
* TOC display on homepage has been disabled by default as most configurations would not require it there.  If you want to enable it, you can do so under a new advanced admin option "Include homepage".
* Make smooth scrolling less zealous with anchors and be more compatible with other plugins that may use # to initiate custom javascript actions.
* Minor admin cross browser CSS enhancements like background gradients and indents.

= 1108.1 =
* Released: 3 August 2011
* Anchor targets (eg anything after #) are now limited to ASCII characters as some mobile user agents do not accept internationalised characters.  This is also a recommendation in the [HTML spec](http://www.w3.org/TR/html4/struct/links.html#h-12.2.1).  A new advanced admin option has been added to specify the default prefix when no characters qualify.
* Make TOC, Pages and Category labels compatible with UTF-8 characters.
* Support ' " \ characters in labels as it was being escaped by WordPress before saving.

= 1108 =
* Released: 1 August 2011
* New: option to hide the title on top of the table of contents.  Thanks to [Andrew](http://dublue.com/plugins/toc/#comment-82) for the suggestion.
* New: option to preserve existing theme specified bullet images for unordered list elements.
* New: option to set the width of the table of contents.  You can select from a number of common widths, or define your own.
* Allow 3 to be set as the minimum number of headings for auto insertion.  The default stays at 4.
* Now accepts heading 1s (h1) within the body of a post, page or custom post type.
* Now creates new span tags for the target rather than the id of the heading.
* Now uses the heading as the anchor target rather than toc_index.
* Adjusted CSS styles for lists to be a little more consistent across themes (eg list-style, margins & paddings).
* Fixed: typo 'heirarchy' should be 'hierarchy'.  Also thanks to Andrew.
* Fixed: addressed an issue while saving on networked installs using sub directories.  Thanks to [Aubrey](http://dublue.com/plugins/toc/#comment-79).
* Fixed: closing of the last list item when deeply nested.

= 1107.1 =
* Released: 10 July 2011
* New: added `[toc]` shortcode to generate the table of contents at the preferred position.  Also useful for sites that only require a TOC on a small handful of pages.
* New: smooth scroll effect added to animate to anchor rather than jump.  It's off by default.
* New: appearance options to match your theme a little bit more.

= 1107 =
* Released: 1 July 2011
* First world release (functional & feature packed)


== Frequently Asked Questions ==

Check out the FAQs / Scenarios at [http://dublue.com/plugins/toc/](http://dublue.com/plugins/toc/)


== Upgrade Notice ==

Update folder with the latest files.  Any previous options will be saved.