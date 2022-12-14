# Changelog

This library adheres to [Semantic Versioning](https://semver.org/) and [Keep a
CHANGELOG](https://keepachangelog.com/en/1.0.0/).

## 2.3.0

- Dependency bumps.
- Drops support for PHP 7.4. Requires 8.0.
- Adding support for `psr/log` support for v1 through v3.
- Adds support for logging with default context (`ai_logger()->with_context(...)`).
- Improves log filtering in admin.

## 2.2.0

- Dependency bumps.

## 2.1.0

### Added

- Improve logger display in-WordPress.
- Adds `ai_logger_handlers` and `ai_logger_processors` filters to allow Monolog
  handlers and processors to be filtered.
- Adds `ai_logger()->to_post()` and `ai_logger()->to_term()` methods for easily
  creating a logger with a post/term handler

### Changed

- Implements `Psr\Log\LoggerInterface` on the `AI_Logger\AI_Logger` class to
  allow the logger to support DI against the logger interface.
- Moved to GitHub actions for continuous integration.
- Switches to Mantle Framework for unit testing.

## 2.0.0

### Added

- Provides a helpful `ai_logger()` and maintains the existing `ai_logger_insert` hook for inserting global post logs.
- Provides [better log display interface](https://github.com/alleyinteractive/logger/wiki/Viewing-Logs) and log meta box display for object-specific logs.

### Changed

- Moves Logger to a [Monolog-based package](https://github.com/alleyinteractive/logger/wiki/How-to-Use).
- Provides more [Log Handlers](https://github.com/alleyinteractive/logger/wiki/Log-Handlers) to use with Monolog

## 1.0.0

Initial release.
