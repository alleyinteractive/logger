# Logger

![Testing
Suite](https://github.com/alleyinteractive/logger/workflows/Testing%20Suite/badge.svg)
![Coding Standards](https://github.com/alleyinteractive/logger/workflows/Coding%20Standards/badge.svg)

Providing a WordPress integration with Monolog, allowing site-wide and post and
term specific logging.

## Documentation

See the [wiki](https://github.com/alleyinteractive/logger/wiki).

```php
ai_logger()->info( 'Log message...' );

\AI_Logger\AI_Logger::info( 'Another format for logging.' );
```

## Installation

Logger requires PHP 7.4 and Composer to run properly.

```bash
composer install
```

You can use it as a submodule in your project by loading the `main-built`
branch.
