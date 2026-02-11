<?php
/**
 * Carbon class file.
 *
 * @package Mantle
 *
 * @phpstan-consistent-constructor
 */

namespace Mantle\Support;

use Carbon\Carbon as BaseCarbon;
use Carbon\CarbonImmutable as BaseCarbonImmutable;
use Mantle\Support\Traits\Conditionable;
use Ramsey\Uuid\Uuid;

/**
 * Carbon Extension
 *
 * Extends Carbon to provide additional functionality for Mantle,
 * including time mocking capabilities for testing.
 *
 * @mixin BaseCarbon
 */
class Carbon extends BaseCarbon {
	use Conditionable;

	/**
	 * Set a Carbon instance (real or mock) to be returned when a "now"
	 * instance is created. The provided instance will be returned
	 * specifically under the following conditions:
	 *
	 *   - A call to the static now() method, ex. Carbon::now()
	 *   - When a null (or blank string) is passed to the constructor or parse(), ex. new Carbon(null)
	 *   - When the string "now" is passed to the constructor or parse(), ex. new Carbon('now')
	 *   - When a string containing the desired time zone is passed to the constructor or parse(), ex. new Carbon('Canada/Eastern')
	 *
	 * Note the timezone parameter was left out of the examples above and
	 * has no affect as the mock value will be returned regardless of its value.
	 *
	 * Only the moment is mocked with setTestNow(), the timezone will still be the one passed
	 * as a parameter.
	 *
	 * To clear the test instance call this method using the default
	 * parameter of null.
	 *
	 * @param \Carbon\Carbon|\DateTimeInterface|string|null $test_now Carbon instance or null to clear the test instance.
	 */
	public static function set_test_now( mixed $test_now = null ): void {
		BaseCarbon::setTestNow( $test_now );
		BaseCarbonImmutable::setTestNow( $test_now );
	}

	/**
	 * Determine if there is a test instance set.
	 */
	public static function has_test_now(): bool {
		return BaseCarbon::hasTestNow();
	}

	/**
	 * Get the Carbon instance (real or mock) to be returned when a "now"
	 * instance is created.
	 */
	public static function get_test_now(): BaseCarbon|\Carbon\CarbonImmutable|null {
		return BaseCarbon::getTestNow();
	}

	/**
	 * Create a Carbon instance from a given ordered UUID.
	 *
	 * @param Uuid|string $id UUID instance or string.
	 */
	public static function create_from_id( Uuid|string $id ): static {
		if ( is_string( $id ) ) {
			$id = Uuid::fromString( $id );
		}

		return static::createFromInterface( $id->getDateTime() );
	}
}
