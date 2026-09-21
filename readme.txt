=== CRS Employee Management Plugin ===
Contributors: crswebb
Tags: employees, staff, team, custom post type, directory
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Manage employees as a custom post type with title, e-mail, phone, description, categories, custom sort order, and shortcodes to display them.

== Description ==

CRS Employee Management Plugin adds an "Employees" custom post type so you can present your team on your WordPress site.

Features:

* Employee custom post type with a featured image and custom fields (title, e-mail, phone, short description).
* Employee categories taxonomy for grouping.
* Custom sort order per employee.
* Shortcodes:
    * `[crsemp_all_employees]` – list every employee.
    * `[crsemp_category_employees category="slug"]` – list employees in a category.
    * `[crsemp_single_employee id="123"]` – show one employee.
* Translation-ready (translations are provided via translate.wordpress.org).

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install it from the WordPress plugin directory.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Add employees under the new "Employees" menu and place a shortcode where you want them displayed.

== Frequently Asked Questions ==

= How do I display employees on a page? =

Add the `[crsemp_all_employees]` shortcode to any page or post. Use `[crsemp_category_employees category="your-category-slug"]` to filter by category, or `[crsemp_single_employee id="123"]` for a single employee.

= Is the plugin translatable? =

Yes. The plugin is fully internationalized (text domain `crs-employee-management-plugin`), and translations are provided through translate.wordpress.org.

== Changelog ==

= 1.0.0 =
* Initial public release.
* Employee custom post type, categories, custom fields, sort order and shortcodes.
* Swedish (sv_SE) translation.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
