=== Page-list ===
Contributors: webvitaly
Plugin URI: http://web-profile.com.ua/wordpress/plugins/page-list/
Tags: page, page-list, pagelist, sitemap, subpages, siblings
Author URI: http://web-profile.com.ua/wordpress/
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 2.2

"Page-list" plugin helps you to show list of pages with [pagelist], [subpages], [siblings] and [pagelist_ext] shortcodes.

== Description ==

You can use aditional parameters: `[pagelist depth="2" child_of="4" exclude="6,7,8"]`.

Shortcodes [pagelist], [subpages], [siblings] are based on [wp_list_pages('title_li=')](http://codex.wordpress.org/Template_Tags/wp_list_pages) function and show hierarchical tree of pages;
You can use aditional parameters: **`[pagelist depth="2" child_of="4" exclude="6,7,8"]`**.
Shortcodes [pagelist], [subpages] and [siblings] accept the same parameters. The only difference is that [subpages] and [siblings] not accept  `child_of` parameter, because [subpages] shows subpages to the current page and [siblings] shows subpages to the parent page.

Shortcode [pagelist_ext] is based on [get_pages()](http://codex.wordpress.org/Function_Reference/get_pages) function and show list of pages with featured image and with excerpt;
You can use aditional parameters: **`[pagelist_ext child_of="4" exclude="6,7,8" image_width="50" image_height="50"]`**.

= Examples: =
* show hierarchical tree of pages: `[pagelist]`;
* show hierarchical tree of subpages: `[subpages]`;
* show hierarchical tree of sibling pages: `[siblings]`;
* show list of pages with featured image and with excerpt: `[pagelist_ext]`;

[Page-list plugin page](http://web-profile.com.ua/wordpress/plugins/page-list/)

[CMS WordPress](http://web-profile.com.ua/wordpress/)

== Usage ==

= Parameters for [pagelist], [subpages] and [siblings]: =
* **depth** - means how many levels in the hierarchy of pages are to be included in the list, by default depth is unlimited (depth=0), but you can specify it like this: `[pagelist depth="3"]`; If you want to show flat list of pages (not hierarchical tree) you can use this shortcode: `[pagelist depth="-1"]`;
* **child_of** - if you want to show subpages of the specific page you can use this shortcode: `[pagelist child_of="4"]` where `4` is the ID of the specific page; If you want to show subpages of the current page you can use this shortcodes: `[subpages]` or `[pagelist child_of="current"]` or `[pagelist child_of="this"]`; If you want to show sibling pages of the current page you can use this shortcodes: `[siblings]` or `[pagelist child_of="parent"]`;
* **exclude** - if you want to exclude some pages from the list you can use this shortcode: `[pagelist exclude="6,7,8"]` where `exclude` parameter accepts comma-separated list of Page IDs; You may exclude current page with this shortcode: `[pagelist exclude="current"]`;
* **exclude_tree** - if you want to exclude the tree of pages from the list you can use this shortcode: `[pagelist exclude_tree="7,10"]` where `exclude_tree` parameter accepts comma-separated list of Page IDs (all this pages and their subpages will be excluded);
* **include** - if you want to include certain pages into the list of pages you can use this shortcode: `[pagelist include="6,7,8"]` where `include` parameter accepts comma-separated list of Page IDs;
* **title_li** - if you want to specify the title of the list of pages you can use this shortcode: `[pagelist title_li="<h2>List of pages</h2>"]`; by default there is no title (title_li="");
* **number** - if you want to specify the number of pages to be included into list of pages you can use this shortcode: `[pagelist number="10"]`; by default the number is unlimited (number="");
* **offset** - if you want to pass over (or displace) some pages you can use this shortcode: `[pagelist offset="5"]`; by default there is no offset (offset="");
* **meta_key** - if you want to include the pages that have this Custom Field Key you can use this shortcode: `[pagelist meta_key="metakey" meta_value="metaval"]`;
* **show_date** - if you want to show the date of the page you can use this shortcode: `[pagelist show_date="created"]`; you can use this values for `show_date` parameter: created, modified, updated;
* **menu_order** - if you want to specify the column by what to sort you can use this shortcode: `[pagelist sort_column="menu_order"]`; by default order columns are `menu_order` and `post_title` (sort_column="menu_order, post_title"); you can use this values for `sort_column` parameter: post_title, menu_order, post_date (sort by creation time), post_modified (sort by last modified time), ID, post_author (sort by the page author's numeric ID), post_name (sort by page slug);
* **sort_order** - if you want to change the sort order of the list of pages (either ascending or descending) you can use this shortcode: `[pagelist sort_order="desc"]`; by default sort_order is `asc` (sort_order="asc"); you can use this values for `sort_order` parameter: asc, desc;
* **link_before** - if you want to specify the text or html that precedes the link text inside the link tag you can use this shortcode: `[pagelist link_before="<span>"]`; you may specify html tags only in the `HTML` tab in your Rich-text editor;
* **link_after** - if you want to specify the text or html that follows the link text inside the link tag you can use this shortcode: `[pagelist link_after="</span>"]`; you may specify html tags only in the `HTML` tab in your Rich-text editor;
* **class** - if you want to specify the CSS class for list of pages you can use this shortcode: `[pagelist class="listclass"]`; by default the class is empty (class="");

= Parameters for [pagelist_ext]: =
* **show_image** - show or hide featured image `[pagelist_ext show_image="0"]`; by default: show_image="1";
* **show_title** - show or hide title `[pagelist_ext show_title="0"]`; by default: show_title="1";
* **show_content** - show or hide content `[pagelist_ext show_content="0"]`; by default: show_content="1";
* **limit_content** - content is limited by "more-tag" if it is exist or by "limit_content" parameter `[pagelist_ext limit_content="100"]`; by default: limit_content="250";
* **image_width** - width of the image `[pagelist_ext image_width="80"]`; by default: image_width="50";
* **image_height** - height of the image `[pagelist_ext image_height="80"]`; by default: image_height="50";
* **child_of** - if you want to show subpages of the specific page you can use this shortcode: `[pagelist_ext child_of="4"]` where `4` is the ID of the specific page; by default it shows subpages to the current page;
* **parent** - if you want to show subpages of the specific page only you can use this shortcode: `[pagelist_ext parent="4"]` where `4` is the ID of the specific page and the depth will be only one level; by default parent="-1" and depth is unlimited;
* **sort_order** - if you want to change the sort order of the list of pages (either ascending or descending) you can use this shortcode: `[pagelist_ext sort_order="desc"]`; by default: sort_order="asc"; you can use this values for `sort_order` parameter: asc, desc;
* **sort_column** - if you want to specify the column by what to sort you can use this shortcode: `[pagelist_ext sort_column="menu_order"]`; by default order columns are `sort_column` and `post_title` (sort_column="menu_order, post_title"); you can use this values for `sort_column` parameter: post_title, menu_order, post_date (sort by creation time), post_modified (sort by last modified time), ID, post_author (sort by the page author's numeric ID), post_name (sort by page slug);
* **hierarchical** - display sub-pages below their parent page `[pagelist_ext hierarchical="0"]`; by default: hierarchical="1";
* **exclude** - if you want to exclude some pages from the list you can use this shortcode: `[pagelist_ext exclude="6,7,8"]` where `exclude` parameter accepts comma-separated list of Page IDs;
* **exclude_tree** - if you want to exclude the tree of pages from the list you can use this shortcode: `[pagelist_ext exclude_tree="7,10"]` where `exclude_tree` parameter accepts comma-separated list of Page IDs (all this pages and their subpages will be excluded);
* **include** - if you want to include certain pages into the list of pages you can use this shortcode: `[pagelist_ext include="6,7,8"]` where `include` parameter accepts comma-separated list of Page IDs;
* **meta_key** - if you want to include the pages that have this Custom Field Key you can use this shortcode: `[pagelist_ext meta_key="metakey" meta_value="metaval"]`;
* **authors** - only include the pages written by the given author(s) `[pagelist_ext authors="6,7,8"]`;
* **number** - if you want to specify the number of pages to be included into list of pages you can use this shortcode: `[pagelist_ext number="10"]`; by default the number is unlimited (number="");
* **offset** - if you want to pass over (or displace) some pages you can use this shortcode: `[pagelist_ext offset="5"]`; by default there is no offset (offset="");
* **post_type** - `[pagelist_ext post_type="page"]`;
* **post_status** - `[pagelist_ext post_status="publish"]`;
* **class** - if you want to specify the CSS class for list of pages you can use this shortcode: `[pagelist_ext class="listclass"]`; by default the class is empty (class="");
* **strip_tags** - if you want to output the content with tags use this shortcode: `[pagelist_ext strip_tags="0"]`; by default the strip_tags is enabled (strip_tags="1");
* **show_child_count** - if you want to show child count you can use this shortcode: `[pagelist_ext show_child_count="1"]`; by default the child_count is disabled (show_child_count="0"); If show_child_count="1", but count of subpages=0, than child count is not showing;
* **child_count_template** - if you want to specify the template of child_count you can use this shortcode: `[pagelist_ext show_child_count="1" child_count_template="Subpages: %child_count%"]`; by default child_count_template="Subpages: %child_count%";
* **show_meta_key** - if you want to show meta key you can use this shortcode: `[pagelist_ext show_meta_key="your_meta_key"]`; by default the show_meta_key is empty (show_meta_key=""); If show_meta_key is enabled, but meta_value is empty, than meta_key is not showing;
* **meta_template** - if you want to specify the template of meta you can use this shortcode: `[pagelist_ext show_meta_key="your_meta_key" meta_template="Meta: %meta%"]`; by default meta_template="%meta%";

== Screenshots ==

1. [pagelist] shortcode;
2. [pagelist_ext] shortcode;

== Changelog ==

= 2.2 =
* Fixed offset parameter;

= 2.1 =
* Fixed number parameter;

= 2.0 =
* Fixed crash bug with [pagelist_ext] if theme does not have thumbnail feature;

= 1.9 =
* Added show_child_count parameter;
* Added show_meta_key parameter;

= 1.8 =
* Added screenshots;
* Improved parameter parsing;

= 1.7 =
* Added strip_tags parameter;

= 1.6 =
* Improved [pagelist_ext] shortcode: added content to list, added toggle show and limit content parameters;

= 1.5 =
* Added [pagelist_ext] shortcode - list of pages with featured image;

= 1.4 =
* Added exclude="current" parameter;

= 1.3.0 =
* Added class to ul elements by default;
* Added "class" option (thanks to Arvind);

= 1.2.0 =
* Added [subpages] and [siblings] shortcodes;

= 1.0.0 =
* Initial release;

== Installation ==

1. Install plugin and activate it on the Plugins page;
2. Add shortcode `[pagelist]`, `[subpages]`, `[siblings]` or `[pagelist_ext]` to page content;
