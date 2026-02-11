<?php
/**
 * Pipeline interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts;

use Closure;

/**
 * Pipeline Contract
 */
interface Pipeline {

	/**
	 * Set the traveler object being sent on the pipeline.
	 *
	 * @param  mixed $traveler
	 * @return static
	 */
	public function send( mixed $traveler );

	/**
	 * Set the stops of the pipeline.
	 *
	 * @param  array<callable>|callable|string|null $stops
	 */
	public function through( array|callable|string|null $stops ): static;

	/**
	 * Set the method to call on the stops.
	 *
	 * @param  string $method
	 */
	public function via( string $method ): static;

	/**
	 * Run the pipeline with a final destination callback.
	 *
	 * @param  \Closure $destination
	 * @return mixed
	 */
	public function then( Closure $destination );
}
