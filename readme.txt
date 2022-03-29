=== AI Logger ===
Contributors: alleyinteractive, jaredcobb
Requires at least: 5.4
Tested up to: 5.4
Requires PHP: 7.4
Stable tag: 2.1.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A Monolog-based logging tool for WordPress. Supports storing log message in a
custom post type or in individual posts and terms.

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

= 2.1.0 =

### Added

- Improve logger display in-WordPress.
- Adds `ai_logger_handlers` and `ai_logger_processors` filters to allow Monolog
  handlers and processors to be filtered.
- Adds `ai_logger()->to_post()` and `ai_logger()->to_term()` methods for easily
  creating a logger with a post/term handler

### Changed

- Implements `Psr\Log\LoggerInterface` on the `AI_Logger\AI_Logger` class to
  allow the logger to support DI against the logger interface.
- Moved to GitHub actions for continuous integration. - Switches to Mantle
Framework for unit testing.

= 2.0.0 =

### Added

- Provides a helpful `ai_logger()` and maintains the existing `ai_logger_insert`
hook for inserting global post logs. - Provides [better log display
interface](https://github.com/alleyinteractive/logger/wiki/Viewing-Logs) and log
meta box display for object-specific logs.

### Changed

- Moves Logger to a [Monolog-based
package](https://github.com/alleyinteractive/logger/wiki/How-to-Use). - Provides
more [Log
Handlers](https://github.com/alleyinteractive/logger/wiki/Log-Handlers) to use
with Monolog = 1.0.0 = * Initial release.
