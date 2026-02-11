<?php
/**
 * Cache related helper functions.
 *
 * @package Mantle
 */

declare(strict_types=1);

namespace Mantle\Support\Helpers;

/**
 * Convert the TTL to seconds.
 *
 * Pass a TTL in a variety of formats and get seconds out.
 *
 * @param int|\DateTimeInterface|\DateInterval|null $ttl
 * @phpstan-return int<0, max>
 */
function normalize_cache_ttl( int|\DateTimeInterface|\DateInterval|null $ttl ): int {
	if ( $ttl instanceof \DateTimeInterface ) {
		$ttl = $ttl->getTimestamp() - time();
	} elseif ( $ttl instanceof \DateInterval ) {
		$ttl = ( new \DateTimeImmutable() )->add( $ttl )->getTimestamp() - time();
	} elseif ( null === $ttl ) {
		$ttl = 0;
	}

	return max( 0, (int) $ttl );
}
