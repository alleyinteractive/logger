# Logger

![Testing
Suite](https://github.com/alleyinteractive/logger/workflows/Testing%20Suite/badge.svg)
![Coding Standards](https://github.com/alleyinteractive/logger/workflows/Coding%20Standards/badge.svg)

Providing a WordPress integration with Monolog, allowing site-wide and post and
term specific logging.

<img width="1018" alt="Screen Shot 2020-05-14 at 4 13 47 PM" src="https://user-images.githubusercontent.com/346399/81981285-197bd880-95fe-11ea-8645-1bb0fa3569a8.png">

## Documentation

See the [wiki](https://github.com/alleyinteractive/logger/wiki) for complete information and more examples.

## Installation

Logger requires PHP 7.4 and Composer to run properly.

```bash
composer install
```

You can use it as a submodule in your project by loading the `main-built`
branch.


## Usage

AI Logger is a complete interface to Monolog with some nice WordPress handlers built in.

```php
// Log site-wide to the ai_log post type.
ai_logger()->info( 'Log message...' );

\AI_Logger\AI_Logger::info( 'Another format for logging.' );
```

### Logging to a Specific Post

Logs will be appended to a post's meta for review.

```php
ai_logger_to_post( $post_id, 'meta-key' )->info( 'This will log to the <meta-key> for a specific post.' );
```

### Logging to a Specific Term

Logs will be appended to a term's meta for review.

```php
ai_logger_to_term( $term_id, 'meta-key' )->info( 'This will log to the <meta-key> for a specific term.' );
```

### Logging to Query Monitor

```php
ai_logger_to_qm()->info( 'This will show up in Query Monitor!' );
```

### Logging with Default Context

```php
ai_logger()->with_context( 'example-context' )->info( 'This will log to the example-context.' );
```

Also supports an array of default log context:

```php
ai_logger()->with_context(
	[
		'context' => 'example-context',
		'key'     => 'value',
	]
)->info( 'This will log to the example-context with key=>value.' );
```

You can also pass the context to `ai_logger()` directly:

```php
ai_logger( 'example-context' )->info( 'This will log to the example-context.' );
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed
recently.

## Maintainers

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.co/careers/).

![Alley logo](https://avatars.githubusercontent.com/u/1733454?s=200&v=4)

## License

This software is released under the terms of the GNU General Public License
version 2 or any later version.
