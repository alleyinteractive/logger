<?php
/**
 * Enumerable interface file.
 *
 * phpcs:disable Generic.Commenting.DocComment.LongNotCapital
 * phpcs:disable Squiz.Commenting.FunctionComment
 * phpcs:disable Squiz.Commenting.ClassComment.Missing
 *
 * @package Mantle
 */

namespace Mantle\Support;

use CachingIterator;
use Countable;
use Mantle\Contracts\Support\Arrayable;
use Mantle\Contracts\Support\Jsonable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Enumerable interface.
 *
 * @template TKey of array-key = array-key
 * @template TValue = mixed
 *
 * @extends \Mantle\Contracts\Support\Arrayable<TKey, TValue>
 * @extends \IteratorAggregate<TKey, TValue>
 */
interface Enumerable extends Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable {
	/**
	 * Create a new collection instance if the value isn't one already.
	 *
	 * @template TMakeKey of array-key
	 * @template TMakeValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TMakeKey, TMakeValue>|iterable<TMakeKey, TMakeValue>|null  $items
	 * @return static<TMakeKey, TMakeValue>
	 */
	public static function make( $items = [] );

	/**
	 * Create a new instance by invoking the callback a given amount of times.
	 *
	 * @param  int           $number
	 * @param  callable|null $callback
	 * @return static
	 */
	public static function times( int $number, ?callable $callback = null );

	/**
	 * Create a collection with the given range.
	 *
	 * @param  int $from
	 * @param  int $to
	 * @param  int $step
	 * @return static
	 */
	public static function range( int $from, int $to, int $step = 1 );

	/**
	 * Wrap the given value in a collection if applicable.
	 *
	 * @template TWrapValue
	 *
	 * @param  iterable<array-key, TWrapValue>|TWrapValue  $value
	 * @return static<array-key, TWrapValue>
	 */
	public static function wrap( $value );

	/**
	 * Get the underlying items from the given collection if applicable.
	 *
	 * @template TUnwrapKey of array-key
	 * @template TUnwrapValue
	 *
	 * @param  array<TUnwrapKey, TUnwrapValue>|static<TUnwrapKey, TUnwrapValue>  $value
	 * @return array<TUnwrapKey, TUnwrapValue>
	 */
	public static function unwrap( $value );

	/**
	 * Create a new instance with no items.
	 *
	 * @return static
	 */
	public static function empty();

	/**
	 * Get all items in the enumerable.
	 */
	public function all(): array;

	/**
	 * Alias for the "avg" method.
	 *
	 * @param  (callable(TValue): float|int)|string|null  $callback
	 * @return float|int|null
	 */
	public function average( $callback = null );

	/**
	 * Get the median of a given key.
	 *
	 * @param  string|array<array-key, string>|null  $key
	 * @return float|int|null
	 */
	public function median( $key = null );

	/**
	 * Get the mode of a given key.
	 *
	 * @param  string|array<array-key, string>|null  $key
	 * @return array<int, float|int>|null
	 */
	public function mode( $key = null );

	/**
	 * Collapse the items into a single enumerable.
	 *
	 * @return static<int, mixed>
	 */
	public function collapse();

	/**
	 * Alias for the "contains" method.
	 *
	 * @param  (callable(TValue, TKey): bool)|TValue|string  $key
	 * @param  mixed $operator
	 * @param  mixed $value
	 * @return bool
	 */
	public function some( $key, $operator = null, $value = null );

	/**
	 * Determine if an item exists, using strict comparison.
	 *
	 * @param  (callable(TValue): bool)|TValue|array-key  $key
	 * @param  TValue|null $value
	 * @return bool
	 */
	public function contains_strict( $key, $value = null );

	/**
	 * Get the average value of a given key.
	 *
	 * @param  (callable(TValue): float|int)|string|null $callback
	 * @return float|int|null
	 */
	public function avg( $callback = null );

	/**
	 * Determine if an item exists in the enumerable.
	 *
	 * @param  (callable(TValue, TKey): bool)|TValue|string  $key
	 * @param  mixed $operator
	 * @param  mixed $value
	 * @return bool
	 */
	public function contains( $key, $operator = null, $value = null );

	/**
	 * Determine if an item is not contained in the collection.
	 *
	 * @param  mixed $key
	 * @param  mixed $operator
	 * @param  mixed $value
	 * @return bool
	 */
	public function doesnt_contain( $key, $operator = null, $value = null );

	/**
	 * Cross join with the given lists, returning all possible permutations.
	 *
	 * @template TCrossJoinKey
	 * @template TCrossJoinValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TCrossJoinKey, TCrossJoinValue>|iterable<TCrossJoinKey, TCrossJoinValue>  ...$lists
	 * @return static<int, array<int, TValue|TCrossJoinValue>>
	 */
	public function cross_join( ...$lists );

	/**
	 * Dump the collection and end the script.
	 *
	 * @param  mixed ...$args
	 */
	public function dd( ...$args ): never;

	/**
	 * Dump the collection.
	 *
	 * @param  mixed ...$args
	 */
	public function dump( ...$args );

	/**
	 * Get the items that are not present in the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
	 * @return static
	 */
	public function diff( $items );

	/**
	 * Get the items that are not present in the given items, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
	 * @param  callable(TValue, TValue): int  $callback
	 * @return static
	 */
	public function diff_using( $items, callable $callback );

	/**
	 * Get the items whose keys and values are not present in the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
	 * @return static
	 */
	public function diff_assoc( $items );

	/**
	 * Get the items whose keys and values are not present in the given items, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
	 * @param  callable(TKey, TKey): int  $callback
	 * @return static
	 */
	public function diff_assoc_using( $items, callable $callback );

	/**
	 * Get the items whose keys are not present in the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, mixed>|iterable<TKey, mixed>  $items
	 * @return static
	 */
	public function diff_keys( $items );

	/**
	 * Get the items whose keys are not present in the given items, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, mixed>|iterable<TKey, mixed>  $items
	 * @param  callable(TKey, TKey): int  $callback
	 * @return static
	 */
	public function diff_keys_using( $items, callable $callback );

	/**
	 * Retrieve duplicate items.
	 *
	 * @param  (callable(TValue): bool)|string|null  $callback
	 * @param  bool          $strict
	 * @return static
	 */
	public function duplicates( $callback = null, bool $strict = false );

	/**
	 * Retrieve duplicate items using strict comparison.
	 *
	 * @param  (callable(TValue): bool)|string|null  $callback
	 * @return static
	 */
	public function duplicates_strict( $callback = null );

	/**
	 * Execute a callback over each item.
	 *
	 * @param  callable(TValue, TKey): mixed  $callback
	 * @return static
	 */
	public function each( callable $callback );

	/**
	 * Execute a callback over each nested chunk of items.
	 *
	 * @param  callable $callback
	 * @return static
	 */
	public function each_spread( callable $callback );

	/**
	 * Determine if all items pass the given truth test.
	 *
	 * @param  (callable(TValue, TKey): bool)|TValue|string  $key
	 * @param  mixed           $operator
	 * @param  mixed           $value
	 * @return bool
	 */
	public function every( $key, $operator = null, $value = null );

	/**
	 * Get all items except for those with the specified keys.
	 *
	 * @param  \Mantle\Support\Enumerable<array-key, TKey>|array<array-key, TKey>  $keys
	 * @return static
	 */
	public function except( $keys );

	/**
	 * Run a filter over each of the items.
	 *
	 * @param  (callable(TValue): bool)|null  $callback
	 * @return static<TKey, TValue>
	 */
	public function filter( ?callable $callback = null );

	/**
	 * Apply the callback if the given "value" is (or resolves to) truthy.
	 *
	 * @template TWhenReturnType as null
	 *
	 * @param  bool  $value
	 * @param  (callable(static): TWhenReturnType)|null  $callback
	 * @param  (callable(static): TWhenReturnType)|null  $default
	 * @return static|TWhenReturnType
	 */
	public function when( mixed $value, ?callable $callback = null, ?callable $default = null );

	/**
	 * Apply the callback if the collection is empty.
	 *
	 * @template TWhenEmptyReturnType
	 *
	 * @param  (callable(static): TWhenEmptyReturnType)  $callback
	 * @param  (callable(static): TWhenEmptyReturnType)|null  $default
	 * @return static|TWhenEmptyReturnType
	 */
	public function when_empty( callable $callback, ?callable $default = null );

	/**
	 * Apply the callback if the collection is not empty.
	 *
	 * @template TWhenNotEmptyReturnType
	 *
	 * @param  (callable(static): TWhenNotEmptyReturnType)  $callback
	 * @param  (callable(static): TWhenNotEmptyReturnType)|null  $default
	 * @return static|TWhenNotEmptyReturnType
	 */
	public function when_not_empty( callable $callback, ?callable $default = null );

	/**
	 * Apply the callback if the given "value" is (or resolves to) falsy.
	 *
	 * @template TUnlessReturnType
	 *
	 * @param  bool  $value
	 * @param  (callable(static): TUnlessReturnType)|null  $callback
	 * @param  (callable(static): TUnlessReturnType)|null  $default
	 * @return static|TUnlessReturnType
	 */
	public function unless( mixed $value, ?callable $callback = null, ?callable $default = null );

	/**
	 * Apply the callback unless the collection is empty.
	 *
	 * @template TUnlessEmptyReturnType
	 *
	 * @param  callable(static): TUnlessEmptyReturnType  $callback
	 * @param  (callable(static): TUnlessEmptyReturnType)|null  $default
	 * @return static|TUnlessEmptyReturnType
	 */
	public function unless_empty( callable $callback, ?callable $default = null );

	/**
	 * Apply the callback unless the collection is not empty.
	 *
	 * @template TUnlessNotEmptyReturnType
	 *
	 * @param  callable(static): TUnlessNotEmptyReturnType  $callback
	 * @param  (callable(static): TUnlessNotEmptyReturnType)|null  $default
	 * @return static|TUnlessNotEmptyReturnType
	 */
	public function unless_not_empty( callable $callback, ?callable $default = null );

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param  string $key
	 * @param  mixed  $operator
	 * @param  mixed  $value
	 * @return static
	 */
	public function where( ?string $key, mixed $operator = null, mixed $value = null );

	/**
	 * Filter items where the value for the given key is null.
	 *
	 * @param  string|null $key
	 * @return static
	 */
	public function where_null( ?string $key = null );

	/**
	 * Filter items where the value for the given key is not null.
	 *
	 * @param  string|null $key
	 * @return static
	 */
	public function where_not_null( ?string $key = null );

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param  string|null $key
	 * @param  mixed  $value
	 * @return static
	 */
	public function where_strict( ?string $key, mixed $value );

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param  string $key
	 * @param  \Mantle\Contracts\Support\Arrayable|iterable  $values
	 * @param  bool   $strict
	 * @return static
	 */
	public function where_in( string $key, mixed $values, bool $strict = false );

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param  string $key
	 * @param  \Mantle\Contracts\Support\Arrayable|iterable  $values
	 * @return static
	 */
	public function where_in_strict( string $key, mixed $values );

	/**
	 * Filter items such that the value of the given key is between the given values.
	 *
	 * @param  string $key
	 * @param  \Mantle\Contracts\Support\Arrayable|iterable  $values
	 * @return static
	 */
	public function where_between( string $key, mixed $values );

	/**
	 * Filter items such that the value of the given key is not between the given values.
	 *
	 * @param  string $key
	 * @param  \Mantle\Contracts\Support\Arrayable|iterable  $values
	 * @return static
	 */
	public function where_not_between( string $key, mixed $values );

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param  string $key
	 * @param  \Mantle\Contracts\Support\Arrayable|iterable  $values
	 * @param  bool   $strict
	 * @return static
	 */
	public function where_not_in( string $key, mixed $values, bool $strict = false );

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param  string $key
	 * @param  \Mantle\Contracts\Support\Arrayable|iterable  $values
	 * @return static
	 */
	public function where_not_in_strict( string $key, mixed $values );

	/**
	 * Filter the items, removing any items that don't match the given type(s).
	 *
	 * @template TWhereInstanceOf
	 *
	 * @param  class-string<TWhereInstanceOf>|array<array-key, class-string<TWhereInstanceOf>>  $type
	 * @return static<TKey, TWhereInstanceOf>
	 */
	public function where_instance_of( string|array $type );

	/**
	 * Get the first item from the enumerable passing the given truth test.
	 *
	 * @template TFirstDefault
	 *
	 * @param  (callable(TValue,TKey): bool)|null  $callback
	 * @param  TFirstDefault|(\Closure(): TFirstDefault)  $default
	 * @return TValue|TFirstDefault
	 */
	public function first( ?callable $callback = null, $default = null );

	/**
	 * Get the first item by the given key value pair.
	 *
	 * @param  string $key
	 * @param  mixed  $operator
	 * @param  mixed  $value
	 * @return TValue|null
	 */
	public function first_where( $key, $operator = null, $value = null );

	/**
	 * Get a flattened array of the items in the collection.
	 *
	 * @param  int $depth
	 * @return static
	 */
	public function flatten( int|float $depth = INF );

	/**
	 * Flip the values with their keys.
	 *
	 * @return static<TValue, TKey>
	 */
	public function flip();

	/**
	 * Get an item from the collection by key.
	 *
	 * @template TGetDefault
	 *
	 * @param  TKey  $key
	 * @param  TGetDefault|(\Closure(): TGetDefault)  $default
	 * @return TValue|TGetDefault
	 */
	public function get( $key, $default = null );

	/**
	 * Group an associative array by a field or using a callback.
	 *
	 * @template TGroupKey of array-key
	 *
	 * @param  (callable(TValue, TKey): TGroupKey)|array|string $group_by
	 * @param  bool $preserve_keys
	 * @return static<($group_by is string ? array-key : ($group_by is array ? array-key : TGroupKey)), static<($preserve_keys is true ? TKey : int), ($group_by is array ? mixed : TValue)>>
	 */
	public function group_by( $group_by, bool $preserve_keys = false );

	/**
	 * Key an associative array by a field or using a callback.
	 *
	 * @template TNewKey of array-key
	 *
	 * @param  (callable(TValue, TKey): TNewKey)|array|string  $key_by
	 * @return static<($key_by is string ? array-key : ($key_by is array ? array-key : TNewKey)), TValue>
	 */
	public function key_by( $key_by );

	/**
	 * Determine if an item exists in the collection by key.
	 *
	 * @param  TKey|array<array-key, TKey>  $key
	 * @return bool
	 */
	public function has( $key );

	/**
	 * Determine if any of the keys exist in the collection.
	 *
	 * @param  mixed $key
	 */
	public function has_any( mixed $key ): bool;

	/**
	 * Concatenate values of a given key as a string.
	 *
	 * @param  (callable(TValue, TKey): mixed)|string $value
	 * @param  string|null $glue
	 */
	public function implode( $value, ?string $glue = null ): string;

	/**
	 * Intersect the collection with the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
	 * @return static
	 */
	public function intersect( $items );

	/**
	 * Intersect the collection with the given items, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
	 * @param  callable(TValue, TValue): int  $callback
	 * @return static
	 */
	public function intersect_using( mixed $items, callable $callback );

	/**
	 * Intersect the collection with the given items with additional index check.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
	 * @return static
	 */
	public function intersect_assoc( $items );

	/**
	 * Intersect the collection with the given items with additional index check, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
	 * @param  callable(TValue, TValue): int  $callback
	 * @return static
	 */
	public function intersect_assoc_using( $items, callable $callback );

	/**
	 * Intersect the collection with the given items by key.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, mixed>|iterable<TKey, mixed>  $items
	 * @return static
	 */
	public function intersect_by_keys( $items );

	/**
	 * Determine if the collection is empty or not.
	 */
	public function is_empty(): bool;

	/**
	 * Determine if the collection is not empty.
	 */
	public function is_not_empty(): bool;

	/**
	 * Determine if the collection contains a single item.
	 */
	public function contains_one_item(): bool;

	/**
	 * Join all items from the collection using a string. The final items can use a separate glue string.
	 *
	 * @param  string $glue
	 * @param  string $final_glue
	 */
	public function join( string $glue, string $final_glue = '' ): string;

	/**
	 * Get the keys of the collection items.
	 *
	 * @return static
	 */
	public function keys();

	/**
	 * Get the last item from the collection.
	 *
	 * @template TLastDefault
	 *
	 * @param  (callable(TValue, TKey): bool)|null  $callback
	 * @param  TLastDefault|(\Closure(): TLastDefault)  $default
	 * @return TValue|TLastDefault
	 */
	public function last( ?callable $callback = null, $default = null );

	/**
	 * Run a map over each of the items.
	 *
	 * @template TMapValue
	 *
	 * @param  callable(TValue, TKey): TMapValue  $callback
	 * @return static<TKey, TMapValue>
	 */
	public function map( callable $callback );

	/**
	 * Run a map over each nested chunk of items.
	 *
	 * @param  callable $callback
	 * @return static
	 */
	public function map_spread( callable $callback );

	/**
	 * Run a dictionary map over the items.
	 *
	 * The callback should return an associative array with a single key/value pair.
	 *
	 * @template TMapToDictionaryKey of array-key
	 * @template TMapToDictionaryValue
	 *
	 * @param  callable(TValue, TKey): array<TMapToDictionaryKey, TMapToDictionaryValue>  $callback
	 * @return static<TMapToDictionaryKey, array<int, TMapToDictionaryValue>>
	 */
	public function map_to_dictionary( callable $callback );

	/**
	 * Run a grouping map over the items.
	 *
	 * The callback should return an associative array with a single key/value pair.
	 *
	 * @template TMapToGroupsKey of array-key
	 * @template TMapToGroupsValue
	 *
	 * @param  callable(TValue, TKey): array<TMapToGroupsKey, TMapToGroupsValue>  $callback
	 * @return static<TMapToGroupsKey, static<int, TMapToGroupsValue>>
	 */
	public function map_to_groups( callable $callback );

	/**
	 * Run an associative map over each of the items.
	 *
	 * The callback should return an associative array with a single key/value pair.
	 *
	 * @template TMapWithKeysKey of array-key
	 * @template TMapWithKeysValue
	 *
	 * @param  callable(TValue, TKey): array<TMapWithKeysKey, TMapWithKeysValue>  $callback
	 * @return static<TMapWithKeysKey, TMapWithKeysValue>
	 */
	public function map_with_keys( callable $callback );

	/**
	 * Map a collection and flatten the result by a single level.
	 *
	 * @template TFlatMapKey of array-key
	 * @template TFlatMapValue
	 *
	 * @param  callable(TValue, TKey): (\Illuminate\Support\Collection<TFlatMapKey, TFlatMapValue>|array<TFlatMapKey, TFlatMapValue>)  $callback
	 * @return static<TFlatMapKey, TFlatMapValue>
	 */
	public function flat_map( callable $callback );

	/**
	 * Map the values into a new class.
	 *
	 * @template TMapIntoValue
	 *
	 * @param  class-string<TMapIntoValue>  $class
	 * @return static<TKey, TMapIntoValue>
	 */
	public function map_into( $class );

	/**
	 * Merge the collection with the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
	 * @return static
	 */
	public function merge( $items );

	/**
	 * Recursively merge the collection with the given items.
	 *
	 * @template TMergeRecursiveValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TMergeRecursiveValue>|iterable<TKey, TMergeRecursiveValue>  $items
	 * @return static<TKey, TValue|TMergeRecursiveValue>
	 */
	public function merge_recursive( $items );

	/**
	 * Create a collection by using this collection for keys and another for its values.
	 *
	 * @template TCombineValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TCombineValue>|iterable<array-key, TCombineValue>  $values
	 * @return static<TValue, TCombineValue>
	 */
	public function combine( $values );

	/**
	 * Union the collection with the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
	 * @return static
	 */
	public function union( $items );

	/**
	 * Get the min value of a given key.
	 *
	 * @param  (callable(TValue):mixed)|string|null  $callback
	 * @return mixed
	 */
	public function min( $callback = null );

	/**
	 * Get the max value of a given key.
	 *
	 * @param  (callable(TValue):mixed)|string|null  $callback
	 * @return mixed
	 */
	public function max( $callback = null );

	/**
	 * Create a new collection consisting of every n-th element.
	 *
	 * @param  int $step
	 * @param  int $offset
	 * @return static
	 */
	public function nth( int $step, int $offset = 0 );

	/**
	 * Get the items with the specified keys.
	 *
	 * @param  \Mantle\Support\Enumerable<array-key, TKey>|array<array-key, TKey>|string  $keys
	 * @return static
	 */
	public function only( $keys );

	/**
	 * "Paginate" the collection by slicing it into a smaller collection.
	 *
	 * @param  int $page
	 * @param  int $per_page
	 * @return static
	 */
	public function for_page( int $page, int $per_page );

	/**
	 * Partition the collection into two arrays using the given callback or key.
	 *
	 * @param  (callable(TValue, TKey): bool)|TValue|string  $key
	 * @param  mixed $operator
	 * @param  mixed $value
	 * @return static<int<0, 1>, static<TKey, TValue>>
	 */
	public function partition( $key, $operator = null, $value = null );

	/**
	 * Push all of the given items onto the collection.
	 *
	 * @template TConcatKey of array-key
	 * @template TConcatValue
	 *
	 * @param  iterable<TConcatKey, TConcatValue>  $source
	 * @return static<TKey|TConcatKey, TValue|TConcatValue>
	 */
	public function concat( $source );

	/**
	 * Get one or a specified number of items randomly from the collection.
	 *
	 * @param  int|null $number
	 * @return static<int, TValue>|TValue
	 *
	 * @throws \InvalidArgumentException
	 */
	public function random( $number = null );

	/**
	 * Reduce the collection to a single value.
	 *
	 * @template TReduceInitial
	 * @template TReduceReturnType
	 *
	 * @param  callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType  $callback
	 * @param  TReduceInitial  $initial
	 * @return TReduceInitial|TReduceReturnType
	 */
	public function reduce( callable $callback, $initial = null );

	/**
	 * Reduce the collection to multiple aggregate values.
	 *
	 * @param  callable $callback
	 * @param  mixed ...$initial
	 *
	 * @throws \UnexpectedValueException
	 */
	public function reduce_spread( callable $callback, ...$initial ): array;

	/**
	 * Replace the collection items with the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
	 * @return static
	 */
	public function replace( $items );

	/**
	 * Recursively replace the collection items with the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
	 * @return static
	 */
	public function replace_recursive( $items );

	/**
	 * Reverse items order.
	 *
	 * @return static
	 */
	public function reverse();

	/**
	 * Search the collection for a given value and return the corresponding key if successful.
	 *
	 * @param  TValue|callable(TValue,TKey): bool  $value
	 * @param  bool  $strict
	 * @return TKey|bool
	 */
	public function search( $value, bool $strict = false );

	/**
	 * Get the item before the given item.
	 *
	 * @param  TValue|(callable(TValue,TKey): bool)  $value
	 * @param  bool  $strict
	 * @return TValue|null
	 */
	public function before( mixed $value, bool $strict = false );

	/**
	 * Get the item after the given item.
	 *
	 * @param  TValue|(callable(TValue,TKey): bool)  $value
	 * @param  bool  $strict
	 * @return TValue|null
	 */
	public function after( mixed $value, bool $strict = false );

	/**
	 * Shuffle the items in the collection.
	 *
	 * @return static
	 */
	public function shuffle();

	/**
	 * Create chunks representing a "sliding window" view of the items in the collection.
	 *
	 * @param  int $size
	 * @param  int $step
	 * @return static<int, static>
	 */
	public function sliding( int $size = 2, int $step = 1 );

	/**
	 * Skip the first {$count} items.
	 *
	 * @param  int $count
	 * @return static
	 */
	public function skip( int $count );

	/**
	 * Skip items in the collection until the given condition is met.
	 *
	 * @param  TValue|callable(TValue,TKey): bool  $value
	 * @return static
	 */
	public function skip_until( mixed $value );

	/**
	 * Skip items in the collection while the given condition is met.
	 *
	 * @param  TValue|callable(TValue,TKey): bool  $value
	 * @return static
	 */
	public function skip_while( mixed $value );

	/**
	 * Get a slice of items from the enumerable.
	 *
	 * @param  int      $offset
	 * @param  int|null $length
	 * @return static
	 */
	public function slice( int $offset, ?int $length = null );

	/**
	 * Split a collection into a certain number of groups.
	 *
	 * @param  int $number_of_groups
	 * @return static<int, static>
	 */
	public function split( int $number_of_groups );

	/**
	 * Get the first item in the collection, but only if exactly one item exists. Otherwise, throw an exception.
	 *
	 * @param  (callable(TValue, TKey): bool)|string|null  $key
	 * @param  mixed $operator
	 * @param  mixed $value
	 * @return TValue
	 *
	 * @throws \Illuminate\Support\ItemNotFoundException
	 * @throws \Illuminate\Support\MultipleItemsFoundException
	 */
	public function sole( callable|string|null $key = null, mixed $operator = null, mixed $value = null );

	/**
	 * Get the first item in the collection but throw an exception if no matching items exist.
	 *
	 * @param  (callable(TValue, TKey): bool)|string|null  $key
	 * @param  mixed $operator
	 * @param  mixed $value
	 * @return TValue
	 *
	 * @throws \Illuminate\Support\ItemNotFoundException
	 */
	public function first_or_fail( callable|string|null $key = null, mixed $operator = null, mixed $value = null );

	/**
	 * Chunk the collection into chunks of the given size.
	 *
	 * @param  int $size
	 * @return static<int, static>
	 */
	public function chunk( int $size );

	/**
	 * Chunk the collection into chunks with a callback.
	 *
	 * @param  callable(TValue, TKey, static<int, TValue>): bool  $callback
	 * @return static<int, static<int, TValue>>
	 */
	public function chunk_while( callable $callback );

	/**
	 * Split a collection into a certain number of groups, and fill the first groups completely.
	 *
	 * @param  int $number_of_groups
	 * @return static<int, static>
	 */
	public function split_in( int $number_of_groups );

	/**
	 * Sort through each item with a callback.
	 *
	 * @param  (callable(TValue, TValue): int)|null|int  $callback
	 * @return static
	 */
	public function sort( $callback = null );

	/**
	 * Sort items in descending order.
	 *
	 * @param  int $options
	 * @return static
	 */
	public function sort_desc( int $options = SORT_REGULAR );

	/**
	 * Sort the collection using the given callback.
	 *
	 * @param  array<array-key, (callable(TValue, TValue): mixed)|(callable(TValue, TKey): mixed)|string|array{string, string}>|(callable(TValue, TKey): mixed)|string  $callback
	 * @param  int             $options
	 * @param  bool            $descending
	 * @return static
	 */
	public function sort_by( $callback, int $options = SORT_REGULAR, bool $descending = false );

	/**
	 * Sort the collection in descending order using the given callback.
	 *
	 * @param  array<array-key, (callable(TValue, TValue): mixed)|(callable(TValue, TKey): mixed)|string|array{string, string}>|(callable(TValue, TKey): mixed)|string  $callback
	 * @param  int             $options
	 * @return static
	 */
	public function sort_by_desc( $callback, int $options = SORT_REGULAR );

	/**
	 * Sort the collection keys.
	 *
	 * @param  int  $options
	 * @param  bool $descending
	 * @return static
	 */
	public function sort_keys( int $options = SORT_REGULAR, bool $descending = false );

	/**
	 * Sort the collection keys in descending order.
	 *
	 * @param  int $options
	 * @return static
	 */
	public function sort_keys_desc( int $options = SORT_REGULAR );

	/**
	 * Sort the collection keys using a callback.
	 *
	 * @param  callable(TKey, TKey): int  $callback
	 * @return static
	 */
	public function sort_keys_using( callable $callback );

	/**
	 * Get the sum of the given values.
	 *
	 * @param  (callable(TValue): mixed)|string|null  $callback
	 * @return mixed
	 */
	public function sum( $callback = null );

	/**
	 * Take the first or last {$limit} items.
	 *
	 * @param  int $limit
	 * @return static
	 */
	public function take( int $limit );

	/**
	 * Take items in the collection until the given condition is met.
	 *
	 * @param  TValue|callable(TValue,TKey): bool  $value
	 * @return static
	 */
	public function take_until( $value );

	/**
	 * Take items in the collection while the given condition is met.
	 *
	 * @param  TValue|callable(TValue,TKey): bool  $value
	 * @return static
	 */
	public function take_while( $value );

	/**
	 * Pass the collection to the given callback and then return it.
	 *
	 * @param  callable(TValue): mixed  $callback
	 * @return static
	 */
	public function tap( callable $callback );

	/**
	 * Pass the enumerable to the given callback and return the result.
	 *
	 * @template TPipeReturnType
	 *
	 * @param  callable(static): TPipeReturnType  $callback
	 * @return TPipeReturnType
	 */
	public function pipe( callable $callback );

	/**
	 * Pass the collection into a new class.
	 *
	 * @template TPipeIntoValue
	 *
	 * @param  class-string<TPipeIntoValue>  $class
	 * @return TPipeIntoValue
	 */
	public function pipe_into( $class );

	/**
	 * Pass the collection through a series of callable pipes and return the result.
	 *
	 * @param  array<callable>  $pipes
	 * @return mixed
	 */
	public function pipe_through( array $pipes );

	/**
	 * Get the values of a given key.
	 *
	 * @param  string|array<array-key, string> $value
	 * @param  string|null  $key
	 * @return static<array-key, mixed>
	 */
	public function pluck( $value, $key = null );

	/**
	 * Create a collection of all elements that do not pass a given truth test.
	 *
	 * @param  (callable(TValue, TKey): bool)|bool|TValue  $callback
	 * @return static
	 */
	public function reject( $callback = true );

	/**
	 * Convert a flatten "dot" notation array into an expanded array.
	 *
	 * @return static
	 */
	public function undot();

	/**
	 * Return only unique items from the collection array.
	 *
	 * @param  (callable(TValue, TKey): mixed)|string|null  $key
	 * @param  bool                 $strict
	 * @return static
	 */
	public function unique( $key = null, bool $strict = false );

	/**
	 * Return only unique items from the collection array using strict comparison.
	 *
	 * @param  (callable(TValue, TKey): mixed)|string|null  $key
	 * @return static
	 */
	public function unique_strict( $key = null );

	/**
	 * Reset the keys on the underlying array.
	 *
	 * @return static<int, TValue>
	 */
	public function values();

	/**
	 * Pad collection to the specified length with a value.
	 *
	 * @template TPadValue
	 *
	 * @param  int  $size
	 * @param  TPadValue  $value
	 * @return static<int, TValue|TPadValue>
	 */
	public function pad( int $size, $value );

	/**
	 * Get the values iterator.
	 *
	 * @return \Traversable<TKey, TValue>
	 */
	public function getIterator(): Traversable;

	/**
	 * Count the number of items in the collection.
	 */
	public function count(): int;

	/**
	 * Count the number of items in the collection by a field or using a callback.
	 *
	 * @param  (callable(TValue, TKey): array-key)|string|null  $count_by
	 * @return static<array-key, int>
	 */
	public function count_by( $count_by = null );

	/**
	 * Zip the collection together with one or more arrays.
	 *
	 * e.g. new Collection([1, 2, 3])->zip([4, 5, 6]);
	 *      => [[1, 4], [2, 5], [3, 6]]
	 *
	 * @template TZipValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TZipValue>|iterable<array-key, TZipValue>  ...$items
	 * @return static<int, static<int, TValue|TZipValue>>
	 */
	public function zip( $items );

	/**
	 * Collect the values into a collection.
	 *
	 * @return \Mantle\Support\Collection<TKey, TValue>
	 */
	public function collect();

	/**
	 * Get the collection of items as a plain array.
	 *
	 * @return array<TKey, mixed>
	 */
	public function to_array();

	/**
	 * Convert the object into something JSON serializable.
	 */
	public function jsonSerialize(): mixed;

	/**
	 * Get the collection of items as JSON.
	 *
	 * @param  int $options
	 * @return string
	 */
	public function to_json( $options = 0 );

	/**
	 * Get the collection of items as pretty print formatted JSON.
	 *
	 * @param  int $options
	 */
	public function to_pretty_json( int $options = 0 ): string;

	/**
	 * Get a CachingIterator instance.
	 *
	 * @param  int $flags
	 */
	public function get_caching_iterator( int $flags = CachingIterator::CALL_TOSTRING ): CachingIterator;

	/**
	 * Convert the collection to its string representation.
	 */
	public function __toString(): string;

	/**
	 * Add a method to the list of proxied methods.
	 *
	 * @param  string $method
	 */
	public static function proxy( string $method ): void;

	/**
	 * Dynamically access collection proxies.
	 *
	 * @param  string $key
	 * @return mixed
	 *
	 * @throws \Exception
	 */
	public function __get( string $key );
}
