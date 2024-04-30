<?php
/**
 * Route_Service_Provider interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Providers;

use Mantle\Http\Request;

/**
 * Route Service Provider Contract
 */
interface Route_Service_Provider {
	/**
	 * Determine if requests should pass through to WordPress.
	 *
	 * @param Request $request Request instance.
	 */
	public function should_pass_through_requests( Request $request ): bool;
}
