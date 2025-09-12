<?php
/**
 * Kernel interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Http;

use Mantle\Http\Request;
use Mantle\Http\Response;
/**
 * Http Kernel
 */
interface Kernel {
	/**
	 * Run the HTTP Application.
	 *
	 * @param Request $request Request object.
	 */
	public function handle( Request $request ): void;

	/**
	 * Terminate the HTTP request.
	 *
	 * @param Response $response Response object.
	 */
	public function terminate( mixed $response = null ): void;
}
