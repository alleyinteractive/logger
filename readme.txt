=== AI Logger ===
Contributors: alleyinteractive, jaredcobb
Requires at least: 4.6.0
Tested up to: 5.3
Requires PHP: 7.3
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A logger tool that stores errors and messages as a custom post type

== Description ==

This provides a globally accessible object named `AI_Logger` that allows you
to record handled errors as a post in a restricted post type. The inserts are
rate throttled so long as the error keys you create are unique to that specific
error.

== Installation ==

1. Download the plugin via GitHub or clone the project into your plugins folder
1. Upload the ZIP file through the "Plugins > Add New > Upload" screen in your WordPress dashboard.
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.0.0 =
* Initial release.
