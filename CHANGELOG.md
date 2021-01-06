# Changelog

This library adheres to [Semantic Versioning](https://semver.org/) and [Keep a
CHANGELOG](https://keepachangelog.com/en/1.0.0/).

## Unreleased

### Added

- Improve logger display in-WordPress.

### Changed

- Moved to GitHub actions for continuous integration.

## 2.0.0

### Added

- Provides a helpful `ai_logger()` and maintains the existing `ai_logger_insert` hook for inserting global post logs.
- Provides [better log display interface](https://github.com/alleyinteractive/logger/wiki/Viewing-Logs) and log meta box display for object-specific logs.

### Changed

- Moves Logger to a [Monolog-based package](https://github.com/alleyinteractive/logger/wiki/How-to-Use).
- Provides more [Log Handlers](https://github.com/alleyinteractive/logger/wiki/Log-Handlers) to use with Monolog
