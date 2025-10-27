=== Mad Libs Manager ===
Contributors: Code by ChatGPT, prompts by Stephen Peters (sbp@tikimojo.com)
Donate link: https://tikimojo.com
Tags: mad libs, story generator, fun, interactive, game
Requires at least: 5.0
Tested up to: 6.8.3
Stable tag: 3.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create, play, and share interactive Mad Libs right inside WordPress! Users fill out prompts to generate fun stories, and submissions are automatically saved and viewable.

== Description ==

**What are Mad Libs?***

* Mad Libs are a simple game.  Read more here: https://en.wikipedia.org/wiki/Mad_Libs
* MAD LIBS Trademark of PENGUIN GROUP (USA) INC and are available for purchase here: http://madlibs.com

**Mad Libs Manager** lets you create Mad Libs style templates in the WordPress admin, embed them with a shortcode, and let visitors fill them out dynamically. Each submission is saved as its own post, so you can review, display, or delete them from the admin area.

**Key Features**

* Create unlimited Mad Libs style templates.
* Embed anywhere using `[madlibs id=123]`.
* Visitors fill in placeholders like `{{noun}}`, `{{verb:past}}`, or `{{adjective}}`.
* Smart auto-formatting (underscores/hyphens become spaces, capitalized labels).
* Inline AJAX story generation — no page reload.
* Submissions automatically saved and viewable.
* Dedicated **Mad Libs Submissions** page for previous entries.
* Search and paginate public submissions.
* Admin tools: search, filter by template, and bulk delete.
* Safe input sanitization and escaping.

**Example Usage**

1. Create a new *Mad Libs Template* under **Mad Libs → Add New**.
2. In the editor, type a story using placeholders like:

I once saw a {{adjective}} {{animal}} {{verb:past}} down the street.

3. Embed the short code anywhere using `[madlibs id=123]`.

Visitors will see input fields for each placeholder, fill them in, and instantly see the completed story — no reload required.

== Installation ==

1. Upload the ZIP via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin.
3. Go to **Mad Libs → Add New** to create your first template.
4. Insert the shortcode `[madlibs id=123]` where you want it to appear.
5. The plugin automatically creates a “Mad Libs Submissions” page.

== Frequently Asked Questions ==

= Why do my apostrophes have backslashes? =
As of version 3.8, this issue is fixed for all new submissions.

= How do I list previous submissions? =
The “Mad Libs Submissions” page is created automatically and displays all entries, filtered by template.

= Can I edit or delete submissions? =
Yes. Go to **Mad Libs → Mad Lib Submissions** in your admin dashboard.

= Can I customize labels or styles? =
Yes, form fields are styled minimally — feel free to override them in your theme’s stylesheet.

== Screenshots ==

1. The Mad Lib creation screen in the WordPress admin.
2. A front-end Mad Lib form with inline results.
3. The submissions page showing previous entries.

== Changelog ==

= 3.8 =
* Fixed escaping issue causing apostrophes to display with slashes.
* Improved text sanitization for new submissions.

= 3.7 =
* Added dedicated "Mad Libs Submissions" page.
* “View previous submissions →” now filters by template.

= 3.6 =
* Restored heading placement and layout from 3.4.
* Improved label display (underscores → spaces).

= 3.5 =
* “View previous submissions →” link added (opens in new tab).

= 3.4 =
* Added admin search, filters, and bulk delete for submissions.

= 3.3 =
* Pagination and search for `[madlibs_entries]`.

= 3.2 =
* Restored inline AJAX output.
* Improved rewrite rule handling.

= 3.1 =
* Fixed 404 permalink issue for new installations.

= 3.0 =
* Rebuilt core layout and improved UI.

== Upgrade Notice ==

= 3.8 =
Fixes escaping issue in story display. Recommended for all users.

== License ==

This plugin is licensed under the GPLv2 or later.
You are free to modify and redistribute it under the same license.

