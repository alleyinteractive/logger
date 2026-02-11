<?php
/**
 * Stringable class file
 *
 * @package Mantle
 */

namespace Mantle\Support;

use ArrayAccess;
use Closure;
use Carbon\Carbon as Date;
use Mantle\Support\Traits\Conditionable;
use Mantle\Support\Traits\Macroable;
use JsonSerializable;
use Mantle\Support\Traits\Makeable;
use Mantle\Support\Traits\Tappable;
use Symfony\Component\VarDumper\VarDumper;

use function Mantle\Support\Helpers\collect;

/**
 * Stringable Class
 *
 * Allows for the chaining of string methods.
 *
 * @implements ArrayAccess<int, string>
 */
class Stringable implements ArrayAccess, JsonSerializable, \Stringable {
	use Conditionable;
	use Macroable;
	use Makeable;
	use Tappable;

	/**
	 * The underlying string value.
	 */
	protected string $value = '';

	/**
	 * Create a new instance of the class.
	 *
	 * @param  string $value
	 */
	public function __construct( $value = '' ) {
		$this->value = (string) $value;
	}

	/**
	 * Return the remainder of a string after the first occurrence of a given value.
	 *
	 * @param  string $search
	 */
	public function after( $search ): static {
		return new static( Str::after( $this->value, $search ) );
	}

	/**
	 * Return the remainder of a string after the last occurrence of a given value.
	 *
	 * @param  string $search
	 */
	public function after_last( $search ): static {
		return new static( Str::after_last( $this->value, $search ) );
	}

	/**
	 * Append the given values to the string.
	 *
	 * @param  array<string>|string ...$values
	 */
	public function append( ...$values ): static {
		return new static( $this->value . implode( '', $values ) ); // @phpstan-ignore-line implode expects array<string>
	}

	/**
	 * Append a new line to the string.
	 *
	 * @param  int $count
	 */
	public function newLine( $count = 1 ): static {
		return $this->append( str_repeat( PHP_EOL, $count ) );
	}

	/**
	 * Transliterate a UTF-8 value to ASCII.
	 *
	 * @param  string $language
	 */
	public function ascii( string $language = 'en' ): static {
		return new static( Str::ascii( $this->value, $language ) );
	}

	/**
	 * Get the trailing name component of the path.
	 *
	 * @param  string $suffix
	 */
	public function basename( string $suffix = '' ): static {
		return new static( basename( $this->value, $suffix ) );
	}

	/**
	 * Get the character at the specified index.
	 *
	 * @param  int $index
	 * @return string|false
	 */
	public function char_at( int $index ): string|false {
		return Str::char_at( $this->value, $index );
	}

	/**
	 * Get the basename of the class path.
	 */
	public function class_basename(): static {
		return new static( class_basename( $this->value ) );
	}

	/**
	 * Get the portion of a string before the first occurrence of a given value.
	 *
	 * @param  string $search
	 */
	public function before( string $search ): static {
		return new static( Str::before( $this->value, $search ) );
	}

	/**
	 * Get the portion of a string before the last occurrence of a given value.
	 *
	 * @param  string $search
	 */
	public function before_last( string $search ): static {
		return new static( Str::before_last( $this->value, $search ) );
	}

	/**
	 * Get the portion of a string between two given values.
	 *
	 * @param  string $from
	 * @param  string $to
	 */
	public function between( string $from, string $to ): static {
		return new static( Str::between( $this->value, $from, $to ) );
	}

	/**
	 * Get the smallest possible portion of a string between two given values.
	 *
	 * @param  string $from
	 * @param  string $to
	 */
	public function between_first( string $from, string $to ): static {
		return new static( Str::between_first( $this->value, $from, $to ) );
	}

	/**
	 * Convert a value to camel case.
	 */
	public function camel(): static {
		return new static( Str::camel( $this->value ) );
	}

	/**
	 * Determine if a given string contains a given substring.
	 *
	 * @param  string|iterable<string> $needles
	 * @param  bool                    $ignore_case
	 */
	public function contains( string|iterable $needles, bool $ignore_case = false ): bool {
		return Str::contains( $this->value, $needles, $ignore_case );
	}

	/**
	 * Determine if a given string contains all array values.
	 *
	 * @param  iterable<string> $needles
	 * @param  bool             $ignore_case
	 */
	public function contains_all( string|iterable $needles, bool $ignore_case = false ): bool {
		return Str::contains_all( $this->value, $needles, $ignore_case );
	}

	/**
	 * Get the parent directory's path.
	 *
	 * @param  int $levels
	 */
	public function dirname( int $levels = 1 ): static {
		return new static( dirname( $this->value, max( 1, $levels ) ) );
	}

	/**
	 * Alias to ends_with().
	 *
	 * @param  string|iterable<string>|mixed $needles
	 */
	public function endsWith( mixed $needles ): bool {
		return $this->ends_with( $needles );
	}

	/**
	 * Determine if a given string ends with a given substring.
	 *
	 * @param  string|iterable<string>|mixed $needles
	 */
	public function ends_with( mixed $needles ): bool {
		return Str::ends_with( $this->value, $needles );
	}

	/**
	 * Determine if the string is an exact match with the given value.
	 *
	 * @param mixed $value
	 */
	public function exactly( mixed $value ): bool {
		if ( $value instanceof Stringable ) {
			$value = $value->toString();
		}

		return $this->value === $value;
	}

	/**
	 * Extracts an excerpt from text that matches the first instance of a phrase.
	 *
	 * @param  string       $phrase
	 * @param  array<mixed> $options
	 */
	public function excerpt( string $phrase = '', array $options = [] ): ?string {
		return Str::excerpt( $this->value, $phrase, $options );
	}

	/**
	 * Explode the string into an array.
	 *
	 * @param  non-empty-string $delimiter
	 * @param  int              $limit
	 * @return Collection<int, string>
	 */
	public function explode( string $delimiter, int $limit = PHP_INT_MAX ): Collection {
		return collect( explode( $delimiter, $this->value, $limit ) );
	}

	/**
	 * Split a string using a regular expression or by length.
	 *
	 * @param  string|int $pattern
	 * @param  int        $limit
	 * @param  int        $flags
	 * @return Collection<int, mixed>
	 */
	public function split( string|int $pattern, int $limit = 1, int $flags = 0 ) {
		if ( is_int( $pattern ) || is_numeric( $pattern ) ) {
			return collect( mb_str_split( $this->value, max( 1, (int) $pattern ) ) );
		}

		$segments = preg_split( $pattern, $this->value, $limit, $flags );

		return empty( $segments ) ? collect() : collect( $segments );
	}

	/**
	 * Cap a string with a single instance of a given value.
	 *
	 * @param  string $cap
	 */
	public function finish( string $cap ): static {
		return new static( Str::finish( $this->value, $cap ) );
	}

	/**
	 * Ensure the string has a single trailing slash.
	 */
	public function trailingSlash(): static {
		return new static( Str::trailing_slash( $this->value ) );
	}

	/**
	 * Remove a trailing slash from the string.
	 */
	public function untrailingSlash(): static {
		return new static( Str::untrailing_slash( $this->value ) );
	}

	/**
	 * Remove a trailing string from the string.
	 *
	 * @param  string $cap
	 */
	public function untrailing( string $cap ): static {
		return new static( rtrim( $this->value, $cap ) );
	}

	/**
	 * Determine if a given string matches a given pattern.
	 *
	 * @param  string|iterable<string> $pattern
	 */
	public function is( string|iterable $pattern ): bool {
		return Str::is( $pattern, $this->value );
	}

	/**
	 * Determine if a given string is 7 bit ASCII.
	 */
	public function is_ascii(): bool {
		return Str::is_ascii( $this->value );
	}

	/**
	 * Determine if a given string is valid JSON.
	 */
	public function is_json(): bool {
		return Str::is_json( $this->value );
	}

	/**
	 * Determine if a given string is a valid UUID.
	 *
	 * @return bool
	 */
	public function is_uuid() {
		return Str::is_uuid( $this->value );
	}

	/**
	 * Determine if the given string is empty.
	 */
	public function is_empty(): bool {
		return '' === $this->value;
	}

	/**
	 * Determine if the given string is not empty.
	 */
	public function is_not_empty(): bool {
		return ! $this->is_empty();
	}

	/**
	 * Convert a string to kebab case.
	 */
	public function kebab(): static {
		return new static( Str::kebab( $this->value ) );
	}

	/**
	 * Return the length of the given string.
	 *
	 * @param  string|null $encoding
	 */
	public function length( $encoding = null ): int {
		return Str::length( $this->value, $encoding );
	}

	/**
	 * Limit the number of characters in a string.
	 *
	 * @param  int    $limit
	 * @param  string $end
	 */
	public function limit( $limit = 100, string $end = '...' ): static {
		return new static( Str::limit( $this->value, $limit, $end ) );
	}

	/**
	 * Convert the given string to lower-case.
	 */
	public function lower(): static {
		return new static( Str::lower( $this->value ) );
	}

	/**
	 * Convert GitHub flavored Markdown into HTML.
	 *
	 * @param  array<mixed> $options
	 */
	public function markdown( array $options = [] ): static {
		return new static( Str::markdown( $this->value, $options ) );
	}

	/**
	 * Convert inline Markdown into HTML.
	 *
	 * @param  array<mixed> $options
	 */
	public function inline_markdown( array $options = [] ): static {
		return new static( Str::inline_markdown( $this->value, $options ) );
	}

	/**
	 * Masks a portion of a string with a repeated character.
	 *
	 * @param  string   $character
	 * @param  int      $index
	 * @param  int|null $length
	 * @param  string   $encoding
	 */
	public function mask( $character, $index, $length = null, $encoding = 'UTF-8' ): static {
		return new static( Str::mask( $this->value, $character, $index, $length, $encoding ) );
	}

	/**
	 * Get the string matching the given pattern.
	 *
	 * @param  string $pattern
	 */
	public function match( $pattern ): static {
		return new static( Str::match( $pattern, $this->value ) );
	}

	/**
	 * Determine if a given string matches a given pattern.
	 *
	 * @param  string|iterable<string> $pattern
	 */
	public function is_match( $pattern ): bool {
		return Str::is_match( $pattern, $this->value );
	}

	/**
	 * Get the string matching the given pattern.
	 *
	 * @param  string $pattern
	 */
	public function match_all( $pattern ): Collection {
		return Str::match_all( $pattern, $this->value );
	}

	/**
	 * Determine if the string matches the given pattern.
	 *
	 * @param  string $pattern
	 */
	public function test( $pattern ): bool {
		return $this->is_match( $pattern );
	}

	/**
	 * Pad both sides of the string with another.
	 *
	 * @param  int    $length
	 * @param  string $pad
	 */
	public function pad_both( $length, $pad = ' ' ): static {
		return new static( Str::pad_both( $this->value, $length, $pad ) );
	}

	/**
	 * Pad the left side of the string with another.
	 *
	 * @param  int    $length
	 * @param  string $pad
	 */
	public function pad_left( $length, $pad = ' ' ): static {
		return new static( Str::pad_left( $this->value, $length, $pad ) );
	}

	/**
	 * Pad the right side of the string with another.
	 *
	 * @param  int    $length
	 * @param  string $pad
	 */
	public function pad_right( $length, $pad = ' ' ): static {
		return new static( Str::pad_right( $this->value, $length, $pad ) );
	}

	/**
	 * Parse a Class@method style callback into class and method.
	 *
	 * @param  string|null $default
	 * @return array<int, string|null>
	 */
	public function parse_callback( ?string $default = null ) {
		return Str::parse_callback( $this->value, $default );
	}

	/**
	 * Call the given callback and return a new string.
	 *
	 * @param  callable $callback
	 */
	public function pipe( callable $callback ): static {
		return new static( $callback( $this ) );
	}

	/**
	 * Get the plural form of an English word.
	 *
	 * @param  int|array<mixed>|\Countable $count
	 */
	public function plural( int|array|\Countable $count = 2 ): static {
		return new static( Str::plural( $this->value, $count ) );
	}

	/**
	 * Pluralize the last word of an English, studly caps case string.
	 *
	 * @param  int|array<mixed>|\Countable $count
	 */
	public function plural_studly( int|array|\Countable $count = 2 ): static {
		return new static( Str::plural_studly( $this->value, $count ) );
	}

	/**
	 * Prepend the given values to the string.
	 *
	 * @param  string ...$values
	 */
	public function prepend( ...$values ): static {
		return new static( implode( '', $values ) . $this->value );
	}

	/**
	 * Remove any occurrence of the given string in the subject.
	 *
	 * @param  string|iterable<string> $search
	 * @param  bool                    $case_sensitive
	 */
	public function remove( $search, bool $case_sensitive = true ): static {
		return new static( Str::remove( $search, $this->value, $case_sensitive ) );
	}

	/**
	 * Reverse the string.
	 */
	public function reverse(): static {
		return new static( Str::reverse( $this->value ) );
	}

	/**
	 * Repeat the string.
	 *
	 * @param  int $times
	 */
	public function repeat( int $times ): static {
		return new static( str_repeat( $this->value, $times ) );
	}

	/**
	 * Replace the given value in the given string.
	 *
	 * @param  string|iterable<string> $search
	 * @param  string|iterable<string> $replace
	 * @param  bool                    $case_sensitive
	 */
	public function replace( $search, $replace, bool $case_sensitive = true ): static {
		return new static( Str::replace( $search, $replace, $this->value, $case_sensitive ) );
	}

	/**
	 * Replace a given value in the string sequentially with an array.
	 *
	 * @param  string           $search
	 * @param  iterable<string> $replace
	 */
	public function replace_array( $search, $replace ): static {
		return new static( Str::replace_array( $search, $replace, $this->value ) );
	}

	/**
	 * Replace the first occurrence of a given value in the string.
	 *
	 * @param  string $search
	 * @param  string $replace
	 */
	public function replace_first( $search, $replace ): static {
		return new static( Str::replace_first( $search, $replace, $this->value ) );
	}

	/**
	 * Replace the last occurrence of a given value in the string.
	 *
	 * @param  string $search
	 * @param  string $replace
	 */
	public function replace_last( $search, $replace ): static {
		return new static( Str::replace_last( $search, $replace, $this->value ) );
	}

	/**
	 * Replace the patterns matching the given regular expression.
	 *
	 * @param  string          $pattern
	 * @param  \Closure|string $replace
	 * @param  int             $limit
	 */
	public function replace_matches( $pattern, $replace, $limit = -1 ): static {
		if ( $replace instanceof Closure ) {
			return new static( preg_replace_callback( $pattern, $replace, $this->value, $limit ) );
		}

		return new static( preg_replace( $pattern, $replace, $this->value, $limit ) );
	}

	/**
	 * Parse input from a string to a collection, according to a format.
	 *
	 * @param  string $format
	 */
	public function scan( string $format ): Collection {
		return collect( sscanf( $this->value, $format ) ); // @phpstan-ignore-line argument.type
	}

	/**
	 * Remove all "extra" blank space from the given string.
	 */
	public function squish(): static {
		return new static( Str::squish( $this->value ) );
	}

	/**
	 * Begin a string with a single instance of a given value.
	 *
	 * @param  string $prefix
	 */
	public function start( string $prefix ): static {
		return new static( Str::start( $this->value, $prefix ) );
	}

	/**
	 * Strip HTML and PHP tags from the given string.
	 *
	 * @param  string[]|string $allowed_tags
	 */
	public function strip_tags( array|string|null $allowed_tags = null ): static {
		return new static( strip_tags( $this->value, $allowed_tags ) ); // phpcs:ignore WordPressVIPMinimum.Functions.StripTags.StripTagsTwoParameters
	}

	/**
	 * Convert the given string to upper-case.
	 */
	public function upper(): static {
		return new static( Str::upper( $this->value ) );
	}

	/**
	 * Convert the given string to title case.
	 */
	public function title(): static {
		return new static( Str::title( $this->value ) );
	}

	/**
	 * Convert the given string to title case for each word.
	 */
	public function headline(): static {
		return new static( Str::headline( $this->value ) );
	}

	/**
	 * Get the singular form of an English word.
	 */
	public function singular(): static {
		return new static( Str::singular( $this->value ) );
	}

	/**
	 * Generate a URL friendly "slug" from a given string.
	 *
	 * @param  string                $separator
	 * @param  string|null           $language
	 * @param  array<string, string> $dictionary
	 */
	public function slug( string $separator = '-', ?string $language = 'en', array $dictionary = [ '@' => 'at' ] ): static {
		return new static( Str::slug( $this->value, $separator, $language, $dictionary ) );
	}

	/**
	 * Alias for slug().
	 *
	 * @param string                $separator Default is '-'.
	 * @param string                $language  Default is 'en'.
	 * @param array<string, string> $dictionary Default is [ '@' => 'at' ].
	 */
	public function slugify( string $separator = '-', ?string $language = 'en', array $dictionary = [ '@' => 'at' ] ): static {
		return $this->slug( $separator, $language, $dictionary );
	}

	/**
	 * Convert a string to snake case.
	 *
	 * @param  string $delimiter
	 */
	public function snake( string $delimiter = '_' ): static {
		return new static( Str::snake( $this->value, $delimiter ) );
	}

	/**
	 * Determine if a given string starts with a given substring.
	 *
	 * @param  string|iterable<string> $needles
	 */
	public function startsWith( $needles ): bool {
		return Str::starts_with( $this->value, $needles );
	}

	/**
	 * Convert a value to studly caps case.
	 */
	public function studly(): static {
		return new static( Str::studly( $this->value ) );
	}

	/**
	 * Convert a value to studly caps case using underscores.
	 */
	public function studlyUnderscore(): static {
		return new static( Str::studly_underscore( $this->value ) );
	}

	/**
	 * Returns the portion of the string specified by the start and length parameters.
	 *
	 * @param  int      $start
	 * @param  int|null $length
	 * @param  string   $encoding
	 */
	public function substr( $start, $length = null, $encoding = 'UTF-8' ): static {
		return new static( Str::substr( $this->value, $start, $length, $encoding ) );
	}

	/**
	 * Returns the number of substring occurrences.
	 *
	 * @param  string   $needle
	 * @param  int      $offset
	 * @param  int|null $length
	 */
	public function substr_count( $needle, $offset = 0, $length = null ): int {
		return Str::substr_count( $this->value, $needle, $offset, $length );
	}

	/**
	 * Replace text within a portion of a string.
	 *
	 * @param  string|string[] $replace
	 * @param  int|int[]       $offset
	 * @param  int|int[]|null  $length
	 */
	public function substr_replace( $replace, $offset = 0, $length = null ): static {
		return new static( Str::substr_replace( $this->value, $replace, $offset, $length ) );
	}

	/**
	 * Swap multiple keywords in a string with other keywords.
	 *
	 * @param  array<mixed> $map
	 */
	public function swap( array $map ): static {
		return new static( strtr( $this->value, $map ) );
	}

	/**
	 * Trim the string of the given characters.
	 *
	 * @param  string $characters
	 */
	public function trim( $characters = null ): static {
		return new static( trim( ...array_merge( [ $this->value ], func_get_args() ) ) );
	}

	/**
	 * Left trim the string of the given characters.
	 *
	 * @param  string $characters
	 */
	public function ltrim( $characters = null ): static {
		return new static( ltrim( ...array_merge( [ $this->value ], func_get_args() ) ) );
	}

	/**
	 * Right trim the string of the given characters.
	 *
	 * @param  string $characters
	 */
	public function rtrim( $characters = null ): static {
		return new static( rtrim( ...array_merge( [ $this->value ], func_get_args() ) ) );
	}

	/**
	 * Make a string's first character lowercase.
	 */
	public function lcfirst(): static {
		return new static( Str::lcfirst( $this->value ) );
	}

	/**
	 * Make a string's first character uppercase.
	 */
	public function ucfirst(): static {
		return new static( Str::ucfirst( $this->value ) );
	}

	/**
	 * Split a string by uppercase characters.
	 *
	 * @return Collection<int, string>
	 */
	public function ucsplit(): Collection {
		return collect( Str::ucsplit( $this->value ) );
	}

	/**
	 * Execute the given callback if the string contains a given substring.
	 *
	 * @param  string|iterable<string> $needles
	 * @param  callable                $callback
	 * @param  callable|null           $default
	 * @return static
	 */
	public function when_contains( string|iterable $needles, ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->contains( $needles ), $callback, $default );
	}

	/**
	 * Execute the given callback if the string contains all array values.
	 *
	 * @param  array<string> $needles
	 * @param  callable      $callback
	 * @param  callable|null $default
	 * @return static
	 */
	public function when_contains_all( array $needles, ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->contains_all( $needles ), $callback, $default );
	}

	/**
	 * Execute the given callback if the string is empty.
	 *
	 * @param  callable      $callback
	 * @param  callable|null $default
	 * @return static
	 */
	public function when_empty( ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->is_empty(), $callback, $default );
	}

	/**
	 * Execute the given callback if the string is not empty.
	 *
	 * @param  callable      $callback
	 * @param  callable|null $default
	 * @return static
	 */
	public function when_not_empty( ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->is_not_empty(), $callback, $default );
	}

	/**
	 * Execute the given callback if the string ends with a given substring.
	 *
	 * @param  string|iterable<string> $needles
	 * @param  callable                $callback
	 * @param  callable|null           $default
	 * @return static
	 */
	public function when_ends_with( $needles, ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->ends_with( $needles ), $callback, $default );
	}

	/**
	 * Execute the given callback if the string is an exact match with the given value.
	 *
	 * @param  string        $value
	 * @param  callable      $callback
	 * @param  callable|null $default
	 * @return static
	 */
	public function when_exactly( $value, ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->exactly( $value ), $callback, $default );
	}

	/**
	 * Execute the given callback if the string is not an exact match with the given value.
	 *
	 * @param  string        $value
	 * @param  callable      $callback
	 * @param  callable|null $default
	 * @return static
	 */
	public function when_not_exactly( $value, ?callable $callback, ?callable $default = null ) {
		return $this->when( ! $this->exactly( $value ), $callback, $default );
	}

	/**
	 * Execute the given callback if the string matches a given pattern.
	 *
	 * @param  string|iterable<string> $pattern
	 * @param  callable                $callback
	 * @param  callable|null           $default
	 * @return static
	 */
	public function when_is( string|iterable $pattern, ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->is( $pattern ), $callback, $default );
	}

	/**
	 * Execute the given callback if the string is 7 bit ASCII.
	 *
	 * @param  callable      $callback
	 * @param  callable|null $default
	 * @return static
	 */
	public function when_is_ascii( ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->is_ascii(), $callback, $default );
	}

	/**
	 * Execute the given callback if the string is a valid UUID.
	 *
	 * @param  callable      $callback
	 * @param  callable|null $default
	 * @return static
	 */
	public function when_is_uuid( ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->is_uuid(), $callback, $default );
	}

	/**
	 * Execute the given callback if the string starts with a given substring.
	 *
	 * @param  string|iterable<string> $needles
	 * @param  callable                $callback
	 * @param  callable|null           $default
	 * @return static
	 */
	public function when_starts_with( $needles, ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->startsWith( $needles ), $callback, $default );
	}

	/**
	 * Execute the given callback if the string matches the given pattern.
	 *
	 * @param  string        $pattern
	 * @param  callable      $callback
	 * @param  callable|null $default
	 * @return static
	 */
	public function when_test( $pattern, ?callable $callback, ?callable $default = null ) {
		return $this->when( $this->test( $pattern ), $callback, $default );
	}

	/**
	 * Limit the number of words in a string.
	 *
	 * @param  int    $words
	 * @param  string $end
	 */
	public function words( $words = 100, string $end = '...' ): static {
		return new static( Str::words( $this->value, $words, $end ) );
	}

	/**
	 * Get the number of words a string contains.
	 *
	 * @param  string|null $characters
	 */
	public function word_count( $characters = null ): int {
		return Str::word_count( $this->value, $characters );
	}

	/**
	 * Wrap the string with the given strings.
	 *
	 * @param  string      $before
	 * @param  string|null $after
	 */
	public function wrap( string $before, $after = null ): static {
		return new static( Str::wrap( $this->value, $before, $after ) );
	}

	/**
	 * Dump the string.
	 */
	public function dump(): static {
		VarDumper::dump( $this->value );

		return $this;
	}

	/**
	 * Dump the string and end the script.
	 */
	public function dd(): never {
		$this->dump();

		exit( 1 );
	}

	/**
	 * Get the underlying string value.
	 */
	public function value(): string {
		return $this->toString();
	}

	/**
	 * Get the underlying string value.
	 */
	public function toString(): string {
		return $this->value;
	}

	/**
	 * Get the underlying string value as an integer.
	 */
	public function to_integer(): int {
		return intval( $this->value );
	}

	/**
	 * Get the underlying string value as a float.
	 */
	public function to_float(): float {
		return floatval( $this->value );
	}

	/**
	 * Get the underlying string value as a boolean.
	 *
	 * Returns true when value is "1", "true", "on", and "yes". Otherwise, returns false.
	 */
	public function to_boolean(): bool {
		return filter_var( $this->value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get the underlying string value as a Carbon instance.
	 *
	 * @param  string|null $format
	 * @param  string|null $tz
	 * @return \Carbon\Carbon
	 */
	public function to_date( $format = null, $tz = null ): ?\Carbon\Carbon {
		if ( is_null( $format ) ) {
			return Date::parse( $this->value, $tz ?: wp_timezone() );
		}

		return Date::createFromFormat( $format, $this->value, $tz ?: wp_timezone() );
	}

	/**
	 * Convert the object to a string when JSON encoded.
	 */
	public function jsonSerialize(): string {
		return $this->__toString();
	}

	/**
	 * Determine if the given offset exists.
	 *
	 * @param  mixed $offset
	 */
	public function offsetExists( mixed $offset ): bool {
		return isset( $this->value[ $offset ] );
	}

	/**
	 * Get the value at the given offset.
	 *
	 * @param  mixed $offset
	 */
	public function offsetGet( mixed $offset ): string {
		return $this->value[ $offset ];
	}

	/**
	 * Set the value at the given offset.
	 *
	 * @param  mixed $offset
	 * @param  mixed $value
	 */
	public function offsetSet( mixed $offset, mixed $value ): void {
		$this->value[ $offset ] = $value;
	}

	/**
	 * Unset the value at the given offset.
	 *
	 * @param  mixed $offset
	 */
	public function offsetUnset( mixed $offset ): void {
		unset( $this->value[ $offset ] );
	}

	/**
	 * Proxy dynamic properties onto methods.
	 *
	 * @param  string $key
	 */
	public function __get( string $key ): mixed {
		return $this->{$key}();
	}

	/**
	 * Get the raw string value.
	 */
	public function __toString(): string {
		return $this->value;
	}
}
