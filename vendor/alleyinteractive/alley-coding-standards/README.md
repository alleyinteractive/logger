# Alley Coding Standards

This is a PHPCS ruleset for [Alley Interactive](https://alley.co). Currently only a proof of concept.

## Installation

To use this standard in a project, declare it as a dependency.

```
composer require alleyinteractive/alley-coding-standards
```

This will install the latest compatible version of PHPCS, WPCS, and VIPCS to your vendor directory in order to run sniffs locally.

You can also manually add this to your project's composer.json file as part of the `require` property:

```
"require": {
    "alleyinteractive/alley-coding-standards": "^0.3.0"
}
```

## Using PHPCS

To use this standard with `phpcs` directly from your command line, use the command:

```
vendor/bin/phpcs --standard=Alley-Interactive .
```

Alternatively, you can set this as a composer script, which will automatically reference the correct version of `phpcs` and the dependent standards.

```
"scripts": {
    "phpcs" : "phpcs --standard=Alley-Interactive ."
}
```

Then use the following command:

```
composer run phpcs
```

You can also pass arguments to the composer phpcs script, following a `--` operator like this:

```
composer run phpcs -- --report=summary
```

## Extending the ruleset
You can create a custom ruleset for your project that extends or customizes these rules by creating your own  `phpcs.xml` or `phpcs.xml.dist` file in your project, which references these rules, like this:

```
<?xml version="1.0"?>
<ruleset>
	<description>Example project ruleset</description>

    <!-- Include Alley Rules -->
    <rule ref="Alley-Interactive" />

    <!-- Project customizations go here -->

</ruleset>
```

# Change Log

This project adheres to [Keep a CHANGELOG](https://keepachangelog.com/en/1.0.0/).

## 0.3.0

- Add PHPCompatibilityWP standard as a dependency (#9)
- Exclude plugin template parts from WordPress.NamingConventions.PrefixAllGlobals sniff (#11)
- Remove 'wp_cache_set' from forbidden functions (#12)

## 0.2.0

- Sniff name changed to Alley-Interactive.
- Composer package renamed to `alleyinteractive/alley-coding-standards`.
- Allow short ternary syntax (#6)

## 0.1.0

- Initial release.
