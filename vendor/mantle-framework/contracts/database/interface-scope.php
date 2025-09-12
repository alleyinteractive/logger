<?php
/**
 * Scope interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Database;

use Mantle\Database\Model\Model;
use Mantle\Database\Query\Builder;

/**
 * Query Scope Contract
 */
interface Scope {
	/**
	 * Apply the scope to a given query builder.
	 *
	 * @template TModel of Model
	 *
	 * @param Builder $builder Query Builder instance.
	 * @phpstan-param Builder<TModel> $builder
	 * @param Model   $model Model object.
	 * @phpstan-param Model<object> $model
	 * @return Builder<TModel>
	 */
	public function apply( Builder $builder, Model $model ): Builder;
}
