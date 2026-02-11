<?php
/**
 * Updatable interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Database;

/**
 * Updatable Model Interface
 */
interface Updatable {
	/**
	 * Save the model.
	 *
	 * @param array<mixed> $attributes Attributes to save.
	 */
	public function save( array $attributes = [] ): bool;

	/**
	 * Delete the model.
	 *
	 * @param bool $force Force delete the mode.
	 */
	public function delete( bool $force = false ): mixed;
}
