<?php
/**
 * Enumerates_Values trait file.
 *
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
 * phpcs:disable Squiz.Commenting.FunctionComment.MissingParamComment
 * phpcs:disable Squiz.Commenting.FunctionComment.ParamNameNoMatch
 * phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag
 * phpcs:disable WordPress.PHP.StrictInArray.MissingTrueStrict
 *
 * @package Mantle
 */

namespace Mantle\Support\Traits;

use Closure;
use Exception;
use Mantle\Contracts\Support\Arrayable;
use Mantle\Contracts\Support\Jsonable;
use Mantle\Support\Arr;
use Mantle\Support\Collection;
use Mantle\Support\Enumerable;
use JsonSerializable;
use Mantle\Support\Higher_Order_Collection_Proxy;
use Symfony\Component\VarDumper\VarDumper;
use Traversable;

use function Mantle\Support\Helpers\data_get;

/**
 * Enumerate_Values trait.
 *
 * @template TKey of array-key
 * @template TValue of mixed = mixed
 *
 * @property-read Higher_Order_Collection_Proxy $average
 * @property-read Higher_Order_Collection_Proxy $avg
 * @property-read Higher_Order_Collection_Proxy $contains
 * @property-read Higher_Order_Collection_Proxy $each
 * @property-read Higher_Order_Collection_Proxy $every
 * @property-read Higher_Order_Collection_Proxy $filter
 * @property-read Higher_Order_Collection_Proxy $first
 * @property-read Higher_Order_Collection_Proxy $flat_map
 * @property-read Higher_Order_Collection_Proxy $group_by
 * @property-read Higher_Order_Collection_Proxy $key_by
 * @property-read Higher_Order_Collection_Proxy $map
 * @property-read Higher_Order_Collection_Proxy $max
 * @property-read Higher_Order_Collection_Proxy $min
 * @property-read Higher_Order_Collection_Proxy $partition
 * @property-read Higher_Order_Collection_Proxy $reject
 * @property-read Higher_Order_Collection_Proxy $some
 * @property-read Higher_Order_Collection_Proxy $sort_by
 * @property-read Higher_Order_Collection_Proxy $sort_by_desc
 * @property-read Higher_Order_Collection_Proxy $sum
 * @property-read Higher_Order_Collection_Proxy $unique
 * @property-read Higher_Order_Collection_Proxy $until
 */
trait Enumerates_Values {
	use Conditionable;

	/**
	 * The methods that can be proxied.
	 *
	 * @var array<string>
	 */
	protected static $proxies = [
		'average',
		'avg',
		'contains',
		'each',
		'every',
		'filter',
		'first',
		'flat_map',
		'group_by',
		'key_by',
		'map',
		'max',
		'min',
		'partition',
		'reject',
		'skip_until',
		'skip_while',
		'some',
		'sort_by',
		'sort_by_desc',
		'sum',
		'take_until',
		'take_while',
		'unique',
		'until',
	];

	/**
	 * Create a new collection instance if the value isn't one already.
	 *
	 * @template TMakeKey of array-key
	 * @template TMakeValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TMakeKey, TMakeValue>|iterable<TMakeKey, TMakeValue>|null $items
	 * @return static<TMakeKey, TMakeValue>
	 */
	public static function make( $items = [] ) {
		return new static( $items );
	}

	/**
	 * Wrap the given value in a collection if applicable.
	 *
	 * @template TWrapValue
	 *
	 * @param  iterable<array-key, TWrapValue>|TWrapValue $value
	 * @return static<array-key, TWrapValue>
	 */
	public static function wrap( $value ): static {
		return $value instanceof Enumerable
			? new static( $value )
			: new static( Arr::wrap( $value ) );
	}

	/**
	 * Get the underlying items from the given collection if applicable.
	 *
	 * @template TUnwrapKey of array-key
	 * @template TUnwrapValue
	 *
	 * @param  array<TUnwrapKey, TUnwrapValue>|static<TUnwrapKey, TUnwrapValue> $value
	 * @return array<TUnwrapKey, TUnwrapValue>
	 */
	public static function unwrap( $value ) {
		return $value instanceof Enumerable ? $value->all() : $value; // @phpstan-ignore-line return.type
	}

	/**
	 * Alias for the "avg" method.
	 *
	 * @param  callable|string|null $callback
	 * @return mixed
	 */
	public function average( $callback = null ) {
		return $this->avg( $callback );
	}

	/**
	 * Alias for the "contains" method.
	 *
	 * @param  mixed $key
	 * @param  mixed $operator
	 * @param  mixed $value
	 * @return bool
	 */
	public function some( $key, $operator = null, $value = null ) {
		return $this->contains( ...func_get_args() );
	}

	/**
	 * Determine if an item exists, using strict comparison.
	 *
	 * @param  mixed $key
	 * @param  mixed $value
	 * @return bool
	 */
	public function contains_strict( $key, $value = null ) {
		if ( func_num_args() === 2 ) {
			return $this->contains(
				fn ( $item ) => data_get( $item, $key ) === $value
			);
		}

		if ( $this->use_as_callable( $key ) ) {
			return ! is_null( $this->first( $key ) );
		}

		foreach ( $this as $item ) {
			if ( $item === $key ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Dump the items and end the script.
	 *
	 * @param  mixed ...$args
	 */
	public function dd( ...$args ): never {
		$this->dump( ...$args );

		exit( 1 );
	}

	/**
	 * Dump the items.
	 *
	 * @param  mixed ...$args
	 */
	public function dump( ...$args ): static {
		dump( $this->all(), ...$args );

		return $this;
	}

	/**
	 * Execute a callback over each item.
	 *
	 * @param  callable(TValue, TKey): mixed $callback
	 * @return static
	 */
	public function each( callable $callback ) {
		foreach ( $this as $key => $item ) {
			if ( $callback( $item, $key ) === false ) { // @phpstan-ignore-line argument.type
				break;
			}
		}

		return $this;
	}

	/**
	 * Execute a callback over each nested chunk of items.
	 *
	 * @param  callable(array<TKey, TValue>): mixed $callback
	 * @return static
	 */
	public function each_spread( callable $callback ) {
		return $this->each(
			function ( $chunk, $key ) use ( $callback ) {
				$chunk[] = $key;

				return $callback( ...$chunk );
			}
		);
	}

	/**
	 * Determine if all items pass the given truth test.
	 *
	 * @param  (callable(TValue, TKey): bool)|TValue|string $key
	 * @param  mixed                                        $operator
	 * @param  mixed                                        $value
	 * @return bool
	 */
	public function every( $key, $operator = null, $value = null ) {
		if ( func_num_args() === 1 ) {
			$callback = $this->value_retriever( $key );

			foreach ( $this as $k => $v ) {
				if ( ! $callback( $v, $k ) ) {
					return false;
				}
			}

			return true;
		}

		return $this->every( $this->operator_for_where( ...func_get_args() ) );
	}

	/**
	 * Get the first item by the given key value pair.
	 *
	 * @param  string $key
	 * @param  mixed  $operator
	 * @param  mixed  $value
	 * @return TValue|null
	 */
	public function first_where( $key, $operator = null, $value = null ) {
		return $this->first( $this->operator_for_where( ...func_get_args() ) );
	}

	/**
	 * Determine if the collection is not empty.
	 */
	public function is_not_empty(): bool {
		return ! $this->is_empty();
	}

	/**
	 * Run a map over each nested chunk of items.
	 *
	 * @template TMapSpreadValue
	 *
	 * @param  callable(mixed): TMapSpreadValue $callback
	 * @return static<TKey, TMapSpreadValue>
	 */
	public function map_spread( callable $callback ) {
		return $this->map(
			function ( $chunk, $key ) use ( $callback ) {
				$chunk[] = $key;

				return $callback( ...$chunk );
			}
		);
	}

	/**
	 * Run a grouping map over the items.
	 *
	 * The callback should return an associative array with a single key/value pair.
	 *
	 * @template TMapToGroupsKey of array-key
	 * @template TMapToGroupsValue
	 *
	 * @param  callable(TValue, TKey): array<TMapToGroupsKey, TMapToGroupsValue> $callback
	 * @return static<TMapToGroupsKey, static<int, TMapToGroupsValue>>
	 */
	public function map_to_groups( callable $callback ) {
		$groups = $this->map_to_dictionary( $callback );

		return $groups->map( [ $this, 'make' ] ); // @phpstan-ignore-line return.type
	}

	/**
	 * Map a collection and flatten the result by a single level.
	 *
	 * @template TFlatMapKey of array-key
	 * @template TFlatMapValue
	 *
	 * @param  callable(TValue, TKey): (\Illuminate\Support\Collection<TFlatMapKey, TFlatMapValue>|array<TFlatMapKey, TFlatMapValue>) $callback
	 * @return static<TFlatMapKey, TFlatMapValue>
	 */
	public function flat_map( callable $callback ) {
		return $this->map( $callback )->collapse();
	}

	/**
	 * Map the values into a new class.
	 *
	 * @template TMapIntoValue
	 *
	 * @param  class-string<TMapIntoValue> $class
	 * @return static<TKey, TMapIntoValue>
	 */
	public function map_into( $class ) {
		return $this->map(
			fn ( $value, $key ) => new $class( $value, $key )
		);
	}

	/**
	 * Get the min value of a given key.
	 *
	 * @param  (callable(TValue):mixed)|string|null $callback
	 * @return mixed
	 */
	public function min( $callback = null ) {
		$callback = $this->value_retriever( $callback );

		return $this->map(
			fn ( $value ) => $callback( $value )
		)->filter(
			fn ( $value ) => ! is_null( $value )
		)->reduce(
			fn ( $result, $value ) => is_null( $result ) || $value < $result ? $value : $result
		);
	}

	/**
	 * Get the max value of a given key.
	 *
	 * @param  (callable(TValue):mixed)|string|null $callback
	 * @return mixed
	 */
	public function max( $callback = null ) {
		$callback = $this->value_retriever( $callback );

		return $this->filter(
			fn ( $value ) => ! is_null( $value )
		)->reduce(
			function ( $result, $item ) use ( $callback ) {
				$value = $callback( $item );

				return is_null( $result ) || $value > $result ? $value : $result;
			}
		);
	}

	/**
	 * "Paginate" the collection by slicing it into a smaller collection.
	 *
	 * @param  int $page
	 * @param  int $per_page
	 * @return static
	 */
	public function for_page( $page, $per_page ) {
		$offset = max( 0, ( $page - 1 ) * $per_page );

		return $this->slice( $offset, $per_page );
	}

	/**
	 * Partition the collection into two arrays using the given callback or key.
	 *
	 * @param  (callable(TValue, TKey): bool)|TValue|string $key
	 * @param  TValue|string|null                           $operator
	 * @param  TValue|null                                  $value
	 * @return static<int<0, 1>, static<TKey, TValue>>
	 */
	public function partition( $key, $operator = null, $value = null ) {
		$passed = [];
		$failed = [];

		$callback = func_num_args() === 1
			? $this->value_retriever( $key )
			: $this->operator_for_where( ...func_get_args() );

		foreach ( $this as $key => $item ) {
			if ( $callback( $item, $key ) ) {
				$passed[ $key ] = $item;
			} else {
				$failed[ $key ] = $item;
			}
		}

		/** @var static<TKey, TValue> $passed_collection */
		$passed_collection = new static( $passed );

		/** @var static<TKey, TValue> $failed_collection */
		$failed_collection = new static( $failed );

		return new static( [
			$passed_collection,
			$failed_collection,
		] );
	}

	/**
	 * Get the sum of the given values.
	 *
	 * @param  (callable(TValue): mixed)|string|null $callback
	 * @return mixed
	 */
	public function sum( $callback = null ) {
		$callback = is_null( $callback ) ? fn ( $value ) => $value : $this->value_retriever( $callback );

		return $this->reduce(
			fn ( $result, $item ) => $result + $callback( $item ),
			0
		);
	}

	/**
	 * Apply the callback if the collection is empty.
	 *
	 * @template TWhenEmptyReturnType
	 *
	 * @param  (callable( $this): TWhenEmptyReturnType)  $callback The callback to apply.
	 * @param  (callable( $this): TWhenEmptyReturnType)|null  $default The callback to apply if the collection is not empty.
	 * @return static|TWhenEmptyReturnType
	 */
	public function when_empty( callable $callback, ?callable $default = null ) {
		return $this->when( $this->is_empty(), $callback, $default );
	}

	/**
	 * Apply the callback if the collection is not empty.
	 *
	 * @template TWhenNotEmptyReturnType
	 *
	 * @param  callable(  $this): TWhenNotEmptyReturnType  $callback The callback to apply.
	 * @param  (callable( $this): TWhenNotEmptyReturnType)|null  $default The callback to apply if the collection is empty.
	 * @return static|TWhenNotEmptyReturnType
	 */
	public function when_not_empty( callable $callback, ?callable $default = null ) {
		return $this->when( $this->is_not_empty(), $callback, $default );
	}

	/**
	 * Apply the callback unless the collection is empty.
	 *
	 * @template TUnlessEmptyReturnType
	 *
	 * @param  callable(  $this): TUnlessEmptyReturnType  $callback The callback to apply.
	 * @param  (callable( $this): TUnlessEmptyReturnType)|null  $default The callback to apply if the collection is empty.
	 * @return static|TUnlessEmptyReturnType
	 */
	public function unless_empty( callable $callback, ?callable $default = null ) {
		return $this->when_not_empty( $callback, $default );
	}

	/**
	 * Apply the callback unless the collection is not empty.
	 *
	 * @template TUnlessNotEmptyReturnType
	 *
	 * @param  callable(  $this): TUnlessNotEmptyReturnType  $callback The callback to apply.
	 * @param  (callable( $this): TUnlessNotEmptyReturnType)|null  $default The callback to apply if the collection is not empty.
	 * @return static|TUnlessNotEmptyReturnType
	 */
	public function unless_not_empty( callable $callback, ?callable $default = null ) {
		return $this->when_empty( $callback, $default );
	}

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param  string|null $key
	 * @param  mixed       $operator
	 * @param  mixed       $value
	 * @return static
	 */
	public function where( ?string $key, $operator = null, $value = null ) {
		return $this->filter( $this->operator_for_where( ...func_get_args() ) );
	}

	/**
	 * Filter items where the given key is not null.
	 *
	 * @param  string|null $key
	 * @return static
	 */
	public function where_null( ?string $key = null ) {
		return $this->where_strict( $key, null );
	}

	/**
	 * Filter items where the given key is null.
	 *
	 * @param  string|null $key
	 * @return static
	 */
	public function where_not_null( ?string $key = null ) {
		return $this->where( $key, '!==', null );
	}

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param  string|null $key
	 * @param  mixed       $value
	 * @return static
	 */
	public function where_strict( ?string $key, mixed $value ) {
		return $this->where( $key, '===', $value );
	}

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param  string                                                                 $key The key to check.
	 * @param  \Mantle\Contracts\Support\Arrayable<int, string>|iterable<int, string> $values Values to search for.
	 * @param  bool                                                                   $strict Whether to use strict comparison.
	 * @return static
	 */
	public function where_in( string $key, mixed $values, bool $strict = false ) {
		$values = $this->get_arrayable_items( $values );

		return $this->filter(
			fn ( $item ) => in_array( data_get( $item, $key ), $values, $strict ) // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		);
	}

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param  string                                                                 $key The key to check.
	 * @param  \Mantle\Contracts\Support\Arrayable<int, string>|iterable<int, string> $values Values to search for.
	 * @return static
	 */
	public function where_in_strict( string $key, mixed $values ) {
		return $this->where_in( $key, $values, true );
	}

	/**
	 * Filter items such that the value of the given key is between the given values.
	 *
	 * @param  string                                                                 $key The key to check.
	 * @param  \Mantle\Contracts\Support\Arrayable<int, string>|iterable<int, string> $values Values to search for.
	 * @return static
	 */
	public function where_between( string $key, mixed $values ) {
		return $this->where( $key, '>=', reset( $values ) )->where( $key, '<=', end( $values ) );
	}

	/**
	 * Filter items such that the value of the given key is not between the given values.
	 *
	 * @param  string                                                                 $key The key to check.
	 * @param  \Mantle\Contracts\Support\Arrayable<int, string>|iterable<int, string> $values Values to search against.
	 * @return static
	 */
	public function where_not_between( string $key, mixed $values ) {
		return $this->filter(
			fn ( $item ) => data_get( $item, $key ) < reset( $values ) || data_get( $item, $key ) > end( $values )
		);
	}

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param  string                                                                 $key The key to check.
	 * @param  \Mantle\Contracts\Support\Arrayable<int, string>|iterable<int, string> $values Values to search against.
	 * @param  bool                                                                   $strict Whether to use strict comparison.
	 * @return static
	 */
	public function where_not_in( string $key, mixed $values, bool $strict = false ) {
		$values = $this->get_arrayable_items( $values );

		return $this->reject(
			fn ( $item ) => in_array( data_get( $item, $key ), $values, $strict ) // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		);
	}

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param  string                                                                 $key The key to check.
	 * @param  \Mantle\Contracts\Support\Arrayable<int, string>|iterable<int, string> $values Values to search against.
	 * @return static
	 */
	public function where_not_in_strict( string $key, mixed $values ) {
		return $this->where_not_in( $key, $values, true );
	}

	/**
	 * Filter the items, removing any items that don't match the given type.
	 *
	 * @template TWhereInstanceOf
	 *
	 * @param  class-string<TWhereInstanceOf>|array<array-key, class-string<TWhereInstanceOf>> $type
	 * @return static<TKey, TWhereInstanceOf>
	 */
	public function where_instance_of( string|array $type ) {
		// @phpstan-ignore-next-line return.type
		return $this->filter( function ( $value ) use ( $type ): bool {
			if ( is_array( $type ) ) {
				foreach ( $type as $class_type ) {
					if ( $value instanceof $class_type ) {
						return true;
					}
				}

				return false;
			}

			return $value instanceof $type;
		} );
	}

	/**
	 * Pass the collection to the given callback and return the result.
	 *
	 * @template TPipeReturnType
	 *
	 * @param  callable( $this): TPipeReturnType  $callback The callback to pass the collection to.
	 * @return TPipeReturnType
	 */
	public function pipe( callable $callback ) {
		return $callback( $this );
	}

	/**
	 * Pass the collection to the given callback and then return it.
	 *
	 * @param  callable( $this): mixed  $callback The callback to pass the collection to.
	 * @return static
	 */
	public function tap( callable $callback ) {
		$callback( clone $this );

		return $this;
	}

	/**
	 * Create a collection of all elements that do not pass a given truth test.
	 *
	 * @param  callable|mixed $callback
	 * @return static
	 */
	public function reject( $callback = true ) {
		$use_as_callable = $this->use_as_callable( $callback );

		return $this->filter(
			fn ( $value, $key ) => $use_as_callable
				? ! $callback( $value, $key )
				: $value != $callback // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual, WordPress.PHP.StrictComparisons.LooseComparison
		);
	}

	/**
	 * Return only unique items from the collection array.
	 *
	 * @param  (callable(TValue, TKey): mixed)|string|null $key
	 * @param  bool                                        $strict
	 * @return static
	 */
	public function unique( $key = null, $strict = false ) {
		$callback = $this->value_retriever( $key );

		$exists = [];

		return $this->reject(
			function ( $item, $key ) use ( $callback, $strict, &$exists ) {
				$id = $callback( $item, $key );
				if ( in_array( $id, $exists, $strict ) ) {
					return true;
				}

				$exists[] = $id;
			}
		);
	}

	/**
	 * Return only unique items from the collection array using strict comparison.
	 *
	 * @param  (callable(TValue, TKey): mixed)|string|null $key
	 * @return static
	 */
	public function unique_strict( $key = null ) {
		return $this->unique( $key, true );
	}

	/**
	 * Collect the values into a collection.
	 *
	 * @return \Mantle\Support\Collection<TKey, TValue>
	 */
	public function collect() {
		return new Collection( $this->all() );
	}

	/**
	 * Get the collection of items as a plain array.
	 *
	 * @return array<TKey, TValue>
	 */
	public function to_array() {
		return $this->map(
			fn ( $value ) => $value instanceof Arrayable ? $value->to_array() : $value,
		)->all();
	}

	/**
	 * Alias for the "to_array" method.
	 *
	 * @return array<TKey, TValue>
	 */
	public function toArray() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		return $this->to_array();
	}

	/**
	 * Convert the object into something JSON serializable.
	 *
	 * @return array<TKey, TValue>
	 */
	public function json_serialize(): mixed { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		return array_map(
			fn ( $value ): mixed => match ( true ) {
				$value instanceof JsonSerializable => $value->jsonSerialize(),
				$value instanceof Jsonable => json_decode( $value->to_json(), true ),
				$value instanceof Arrayable => $value->to_array(),
				default => $value,
			},
			$this->all()
		);
	}

	/**
	 * Alias to json_serialize().
	 *
	 * @return array<TKey, TValue>
	 */
	public function jsonSerialize(): mixed { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		return $this->json_serialize();
	}

	/**
	 * Get the collection of items as JSON.
	 *
	 * @param  int $options
	 */
	public function to_json( $options = 0 ): string {
		return (string) json_encode( $this->jsonSerialize(), $options ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}

	/**
	 * Count the number of items in the collection using a given truth test.
	 *
	 * @param  callable|null $callback
	 * @return static
	 */
	public function count_by( $callback = null ) {
		if ( is_null( $callback ) ) {
			$callback = fn ( $value ) => $value;
		}

		return new static(
			$this->group_by( $callback )->map( // @phpstan-ignore-line argument.templateType
				fn ( $value ) => $value->count()
			)
		);
	}

	/**
	 * Convert the collection to its string representation.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->to_json();
	}

	/**
	 * Add a method to the list of proxied methods.
	 *
	 * @param  string $method
	 */
	public static function proxy( $method ): void {
		static::$proxies[] = $method; // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.StaticOutsideClass
	}

	/**
	 * Dynamically access collection proxies.
	 *
	 * @param  string $key
	 * @return mixed
	 *
	 * @throws \Exception Throw on nonexistent property keys.
	 */
	public function __get( $key ) {
		if ( ! in_array( $key, static::$proxies ) ) { // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.StaticOutsideClass
			throw new Exception( "Property [{$key}] does not exist on this collection instance." );
		}

		return new Higher_Order_Collection_Proxy( $this, $key );
	}

	/**
	 * Results array of items from Collection or Arrayable.
	 *
	 * @param  mixed $items
	 * @return array<TKey, TValue>
	 */
	protected function get_arrayable_items( $items ) {
		return match ( true ) {
			is_array( $items ) => $items,
			$items instanceof Enumerable => $items->all(),
			$items instanceof Arrayable => $items->to_array(),
			$items instanceof Jsonable => json_decode( $items->to_json(), true ),
			$items instanceof JsonSerializable => (array) $items->jsonSerialize(),
			$items instanceof Traversable => iterator_to_array( $items ),
			default => (array) $items,
		};
	}

	/**
	 * Get an operator checker callback.
	 *
	 * @param  string|null $key
	 * @param  string|null $operator
	 * @param  mixed       $value
	 * @return \Closure
	 */
	protected function operator_for_where( ?string $key, ?string $operator = null, mixed $value = null ) {
		if ( func_num_args() === 1 ) {
			$value = true;

			$operator = '=';
		}

		if ( func_num_args() === 2 ) {
			$value = $operator;

			$operator = '=';
		}

		return function ( $item ) use ( $key, $operator, $value ) {
			$retrieved = data_get( $item, $key );

			$strings = array_filter(
				[ $retrieved, $value ],
				fn ( $value ) => is_string( $value ) || ( is_object( $value ) && method_exists( $value, '__toString' ) )
			);

			if ( count( $strings ) < 2 && count( array_filter( [ $retrieved, $value ], is_object( ... ) ) ) === 1 ) {
				return in_array( $operator, [ '!=', '<>', '!==' ] );
			}

			switch ( $operator ) {
				default:
				case '=':
				case '==':
					return $retrieved == $value; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				case '!=':
				case '<>':
				case '!==':
					return $retrieved !== $value;
				case '<':
					return $retrieved < $value;
				case '>':
					return $retrieved > $value;
				case '<=':
					return $retrieved <= $value;
				case '>=':
					return $retrieved >= $value;
				case '===':
					return $retrieved === $value;
			}
		};
	}

	/**
	 * Determine if the given value is callable, but not a string.
	 *
	 * @param  mixed $value
	 * @phpstan-return ($value is callable ? true : false)
	 */
	protected function use_as_callable( mixed $value ): bool {
		return ! is_string( $value ) && is_callable( $value );
	}

	/**
	 * Get a value retrieving callback.
	 *
	 * @param  callable|string|null $value
	 */
	protected function value_retriever( callable|string|null $value ): callable {
		if ( $this->use_as_callable( $value ) && is_callable( $value ) ) {
			return $value;
		}

		return fn ( $item ) => data_get( $item, $value );
	}

	/**
	 * Make a function to check an item's equality.
	 *
	 * @param  mixed $value
	 */
	protected function equality( mixed $value ): Closure {
		return fn ( $item ) => $item === $value;
	}

	/**
	 * Make a function using another function, by negating its result.
	 *
	 * @param  \Closure $callback
	 */
	protected function negate( Closure $callback ): Closure {
		return fn ( ...$params ) => ! $callback( ...$params );
	}

	/**
	 * Make a function that returns what's passed to it.
	 *
	 * @return \Closure(TValue): TValue
	 */
	protected function identity(): Closure {
		return fn ( $value ) => $value;
	}

	/**
	 * Create a new instance with no items.
	 */
	public static function empty(): static {
		return new static( [] );
	}

	/**
	 * Create a new collection by invoking the callback a given amount of times.
	 *
	 * @template TTimesValue
	 *
	 * @param  int                               $number
	 * @param  (callable(int): TTimesValue)|null $callback
	 * @return static<int, TTimesValue>
	 */
	public static function times( int $number, ?callable $callback = null ): static {
		if ( $number < 1 ) {
			return new static();
		}

		return static::range( 1, $number )
			->unless( $callback === null )
			->map( $callback );
	}

	/**
	 * Get the average value of a given key.
	 *
	 * @param  (callable(TValue): float|int)|string|null $callback
	 */
	public function avg( $callback = null ): int|float|null {
		$callback = $this->value_retriever( $callback );

		$reduced = $this->reduce(
			static function ( &$reduce, $value ) use ( $callback ) {
				$resolved = $callback( $value );

				if ( ! is_null( $resolved ) ) {
					$reduce[0] += $resolved;
					$reduce[1]++;
				}

				return $reduce;
			},
			[ 0, 0 ]
		);

		return $reduced[1] ? $reduced[0] / $reduced[1] : null;
	}

	/**
	 * Reduce the collection to a single value.
	 *
	 * @template TReduceInitial
	 * @template TReduceReturnType
	 *
	 * @param  callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType $callback
	 * @param  TReduceInitial                                                              $initial
	 * @return TReduceInitial|TReduceReturnType
	 */
	public function reduce( callable $callback, $initial = null ) {
		$result = $initial;

		foreach ( $this as $key => $value ) {
			$result = $callback( $result, $value, $key );
		}

		return $result;
	}

	/**
	 * Reduce the collection to multiple aggregate values.
	 *
	 * @param  callable $callback
	 * @param  mixed    ...$initial
	 * @return array<array-key, mixed>
	 *
	 * @throws \UnexpectedValueException Throw when the reducer does not return an array.
	 */
	public function reduce_spread( callable $callback, ...$initial ): array {
		$result = $initial;

		foreach ( $this as $key => $value ) {
			$result = call_user_func_array( $callback, array_merge( $result, [ $value, $key ] ) );

			if ( ! is_array( $result ) ) {
				throw new \UnexpectedValueException(
					sprintf(
						"%s::reduce_spread expects reducer to return an array, but got a '%s' instead.",
						class_basename( static::class ),
						gettype( $result )
					)
				);
			}
		}

		return $result;
	}

	/**
	 * Pass the collection into a new class.
	 *
	 * @template TPipeIntoValue
	 *
	 * @param  class-string<TPipeIntoValue> $class
	 * @return TPipeIntoValue
	 */
	public function pipe_into( $class ) {
		return new $class( $this );
	}

	/**
	 * Pass the collection through a series of callable pipes and return the result.
	 *
	 * @param  array<callable> $callbacks
	 * @return mixed
	 */
	public function pipe_through( $callbacks ) {
		return ( new Collection( $callbacks ) )->reduce(
			fn ( $carry, $callback ) => $callback( $carry ),
			$this
		);
	}

	/**
	 * Get the collection of items as pretty print formatted JSON.
	 *
	 * @param  int $options
	 */
	public function to_pretty_json( int $options = 0 ): string {
		return $this->to_json( JSON_PRETTY_PRINT | $options );
	}

	/**
	 * Get a CachingIterator instance.
	 *
	 * @param  int $flags
	 * @return \CachingIterator<TKey, TValue, \Iterator<TKey, TValue>>
	 */
	public function get_caching_iterator( int $flags = \CachingIterator::CALL_TOSTRING ): \CachingIterator {
		return new \CachingIterator( $this->getIterator(), $flags );
	}
}
