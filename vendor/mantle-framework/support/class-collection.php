<?php
/**
 * Collections class file.
 *
 * phpcs:disable Squiz.Commenting.FunctionComment.MissingParamComment, Squiz.Commenting.FunctionComment.MissingParamTag
 *
 * @todo Incorporate Lazy Collections and adjust the methods in this file to use
 * lazy evaluation where appropriate.
 *
 * @package Mantle
 */

namespace Mantle\Support;

use ArrayAccess;
use ArrayIterator;
use JsonSerializable;
use Mantle\Contracts\Support\Arrayable;
use Mantle\Contracts\Support\Jsonable;
use Mantle\Database\Model;
use Mantle\Support\Enumerable;
use Mantle\Support\Traits\Enumerates_Values;
use stdClass;
use Traversable;

use function Mantle\Support\Helpers\data_get;
use function Mantle\Support\Helpers\value;

/**
 * Collection
 *
 * @template TKey of array-key = array-key
 * @template TValue of mixed = mixed
 *
 * @implements \ArrayAccess<TKey, TValue>
 * @implements \Mantle\Support\Enumerable<TKey, TValue>
 */
class Collection implements ArrayAccess, Enumerable {
	/**
	 * The enumerated values trait.
	 *
	 * @use Enumerates_Values<TKey, TValue>
	 */
	use Enumerates_Values;

	/**
	 * The items contained in the collection.
	 *
	 * @var array<TKey, TValue>
	 */
	protected $items = [];

	/**
	 * Create a new collection.
	 *
	 * @param iterable<TKey, TValue>|Arrayable<TKey, TValue>|Jsonable|JsonSerializable $items The items to include in the collection.
	 */
	public function __construct( mixed $items = [] ) {
		$this->items = $this->get_arrayable_items( $items );
	}

	/**
	 * Create a new collection from some known WordPress object.
	 *
	 * Falls back to the normal constructor if $value is unrecognized.
	 *
	 * @template TValueFrom
	 *
	 * @param iterable<array-key, TValueFrom>|\WP_Query $value
	 * @return static<int, TValueFrom>
	 */
	public static function from( $value ) {
		global $post;
		if ( $value instanceof \WP_Query ) {
			$items = [];
			while ( $value->have_posts() ) {
				$value->the_post();

				$model = Model\Post::find( $post );

				if ( $model instanceof \Mantle\Database\Model\Post ) {
					$items[] = $model;
				}
			}

			return new static( $items ); // @phpstan-ignore-line return.type
		}

		return ( new static( $value ) )->values(); // @phpstan-ignore-line return.type
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
	public static function times( $number, ?callable $callback = null ) {
		if ( $number < 1 ) {
			return new static();
		}

		$items = range( 1, $number );

		if ( is_null( $callback ) ) {
			$callback = fn( $value ) => $value;
		}

		return ( new static( $items ) )->map( $callback );
	}

	/**
	 * Create a collection with the given range.
	 *
	 * @param  int $from
	 * @param  int $to
	 * @param  int $step
	 * @return static<int, int>
	 */
	public static function range( int $from, int $to, int $step = 1 ): static {
		return new static( range( $from, $to, $step ) );
	}

	/**
	 * Get all of the items in the collection.
	 *
	 * @return array<TKey, TValue>
	 */
	public function all(): array {
		return $this->items;
	}

	/**
	 * Get the average value of a given key.
	 *
	 * @param  (callable(): mixed)|string|null $callback
	 */
	public function avg( $callback = null ): int|float|null {
		$callback = $this->value_retriever( $callback );

		$items = $this->map(
			fn ( $value ) => $callback( $value ),
		)->filter(
			fn ( $value ) => ! is_null( $value ),
		);

		$count = $items->count();

		if ( $count ) {
			return $items->sum() / $count;
		}

		return null;
	}

	/**
	 * Get the median of a given key.
	 *
	 * @param  string|array<array-key, string>|null $key
	 * @return float|int|null
	 */
	public function median( $key = null ) {
		$values = ( isset( $key ) ? $this->pluck( $key ) : $this )
			->filter(
				fn ( $item ) => ! is_null( $item )
			)->sort()->values();


		$count = $values->count();

		if ( 0 === $count ) {
			return null;
		}

		$middle = (int) ( $count / 2 );

		if ( $count % 2 !== 0 ) {
			return $values->get( $middle ); // @phpstan-ignore-line return.type
		}

		return ( new static(
			[
				$values->get( $middle - 1 ),
				$values->get( $middle ),
			]
		) )->average();
	}

	/**
	 * Get the mode of a given key.
	 *
	 * @param  string|array<array-key, string>|null $key
	 * @return array<int, float|int>|null
	 */
	public function mode( $key = null ) {
		if ( $this->count() === 0 ) {
			return null;
		}

		$collection = isset( $key ) ? $this->pluck( $key ) : $this;

		$counts = new self();

		$collection->each(
			function ( $value ) use ( $counts ): void {
				$counts[ $value ] = isset( $counts[ $value ] ) ? $counts[ $value ] + 1 : 1;
			}
		);

		$sorted = $counts->sort();

		$highest_value = $sorted->last();

		return $sorted->filter(
			fn ( $value ) => $value == $highest_value // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison, Universal.Operators.StrictComparisons.LooseEqual
		)->sort()->keys()->all();
	}

	/**
	 * Collapse the collection of items into a single array.
	 *
	 * @return static<int, mixed>
	 */
	public function collapse() {
		return new static( Arr::collapse( $this->items ) );
	}

	/**
	 * Determine if an item exists in the collection.
	 *
	 * @param  (callable(TValue, TKey): bool)|TValue|string $key
	 * @param  mixed                                        $operator
	 * @param  mixed                                        $value
	 * @return bool
	 */
	public function contains( $key, $operator = null, $value = null ) {
		if ( func_num_args() === 1 ) {
			if ( $this->use_as_callable( $key ) ) {
				$placeholder = new stdClass();

				return $this->first( $key, $placeholder ) !== $placeholder;
			}

			return in_array( $key, $this->items ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		}

		return $this->contains( $this->operator_for_where( ...func_get_args() ) );
	}

	/**
	 * Determine if an item exists, using strict comparison.
	 *
	 * @param  (callable(TValue): bool)|TValue|array-key $key
	 * @param  TValue|null                               $value
	 * @return bool
	 */
	public function contains_strict( $key, $value = null ) {
		if ( func_num_args() === 2 ) {
			return $this->contains( fn ( $item ) => data_get( $item, $key ) === $value ); // @phpstan-ignore-line argument.type
		}

		if ( $this->use_as_callable( $key ) ) {
			return ! is_null( $this->first( $key ) );
		}

			return in_array( $key, $this->items, true );
	}

	/**
	 * Determine if an item is not contained in the collection.
	 *
	 * @param  mixed $key
	 * @param  mixed $operator
	 * @param  mixed $value
	 */
	public function doesnt_contain( $key, $operator = null, $value = null ): bool {
		return ! $this->contains( ...func_get_args() );
	}

	/**
	 * Cross join with the given lists, returning all possible permutations.
	 *
	 * @template TCrossJoinKey of array-key
	 * @template TCrossJoinValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TCrossJoinKey, TCrossJoinValue>|iterable<TCrossJoinKey, TCrossJoinValue> ...$lists
	 * @return static<int, array<int, TValue|TCrossJoinValue>>
	 */
	public function cross_join( ...$lists ) {
		return new static(
			Arr::cross_join(
				$this->items,
				...array_map( [ $this, 'get_arrayable_items' ], $lists )
			)
		);
	}

	/**
	 * Get the items in the collection that are not present in the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue> $items
	 * @return static
	 */
	public function diff( $items ) {
		return new static( array_diff( $this->items, $this->get_arrayable_items( $items ) ) );
	}

	/**
	 * Get the items in the collection that are not present in the given items, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue> $items
	 * @param  callable(TValue, TValue): int                                                      $callback
	 * @return static
	 */
	public function diff_using( $items, callable $callback ) {
		return new static( array_udiff( $this->items, $this->get_arrayable_items( $items ), $callback ) );
	}

	/**
	 * Get the items in the collection whose keys and values are not present in the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @return static
	 */
	public function diff_assoc( $items ) {
		return new static( array_diff_assoc( $this->items, $this->get_arrayable_items( $items ) ) );
	}

	/**
	 * Get the items in the collection whose keys and values are not present in the given items, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @param  callable(TKey, TKey): int                                                $callback
	 * @return static
	 */
	public function diff_assoc_using( $items, callable $callback ) {
		return new static( array_diff_uassoc( $this->items, $this->get_arrayable_items( $items ), $callback ) );
	}

	/**
	 * Get the items in the collection whose keys are not present in the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @return static
	 */
	public function diff_keys( $items ) {
		return new static( array_diff_key( $this->items, $this->get_arrayable_items( $items ) ) );
	}

	/**
	 * Get the items in the collection whose keys are not present in the given items, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @param  callable(TKey, TKey): int                                                $callback
	 * @return static
	 */
	public function diff_keys_using( $items, callable $callback ) {
		return new static( array_diff_ukey( $this->items, $this->get_arrayable_items( $items ), $callback ) );
	}

	/**
	 * Retrieve duplicate items from the collection.
	 *
	 * @param  (callable(TValue): bool)|string|null $callback
	 * @param    bool                                 $strict
	 * @return static
	 */
	public function duplicates( $callback = null, $strict = false ) {
		$items = $this->map( $this->value_retriever( $callback ) );

		$unique_items = $items->unique( null, $strict );

		$compare = $this->duplicate_comparator( $strict );

		$duplicates = new static();

		foreach ( $items as $key => $value ) {
			if ( $unique_items->is_not_empty() && $compare( $value, $unique_items->first() ) ) {
				$unique_items->shift();
			} else {
				$duplicates[ $key ] = $value;
			}
		}

		return $duplicates;
	}

	/**
	 * Retrieve duplicate items from the collection using strict comparison.
	 *
	 * @param  (callable(TValue): bool)|string|null $callback
	 * @return static
	 */
	public function duplicates_strict( $callback = null ) {
		return $this->duplicates( $callback, true );
	}

	/**
	 * Get the comparison function to detect duplicates.
	 *
	 * @param    bool $strict
	 * @return callable(TValue, TValue): bool
	 */
	protected function duplicate_comparator( $strict ) {
		if ( $strict ) {
			return fn ( $a, $b ) => $a == $b; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison, Universal.Operators.StrictComparisons.LooseEqual
		}

		return fn ( $a, $b ) => $a === $b;
	}

	/**
	 * Get all items except for those with the specified keys.
	 *
	 * @param  \Mantle\Support\Enumerable<array-key, TKey>|array<array-key, TKey>|string $keys
	 * @return static
	 */
	public function except( $keys ) {
		if ( $keys instanceof Enumerable ) {
			$keys = $keys->all();
		} elseif ( ! is_array( $keys ) ) {
			$keys = func_get_args();
		}

		return new static( Arr::except( $this->items, $keys ) );
	}

	/**
	 * Run a filter over each of the items.
	 *
	 * @param (callable(TValue, TKey): bool)|null $callback
	 * @return static<TKey, TValue>
	 */
	public function filter( ?callable $callback = null ) {
		if ( $callback ) {
			return new static( Arr::where( $this->items, $callback ) );
		}

		return new static( array_filter( $this->items ) );
	}

	/**
	 * Get the first item from the collection passing the given truth test.
	 *
	 * @template TFirstDefault
	 *
	 * @param  (callable(TValue, TKey): bool)|null       $callback
	 * @param  TFirstDefault|(\Closure(): TFirstDefault) $default
	 * @return TValue|TFirstDefault
	 */
	public function first( ?callable $callback = null, $default = null ): mixed {
		return Arr::first( $this->items, $callback, $default );
	}

	/**
	 * Get the first item in the collection, but only if exactly one item exists. Otherwise, throw an exception.
	 *
	 * @param  (callable(TValue, TKey): bool)|string|null $key
	 * @param  mixed                                      $operator
	 * @param  mixed                                      $value
	 * @return TValue
	 *
	 * @throws Item_Not_Found_Exception Thrown if no items are found.
	 * @throws Multiple_Items_Found_Exception Thrown if multiple items are found.
	 */
	public function sole( callable|string|null $key = null, mixed $operator = null, mixed $value = null ): mixed {
		$filter = func_num_args() > 1
			? $this->operator_for_where( ...func_get_args() )
			: $key;

		$items = $filter === null ? $this : $this->filter( $filter );

		$count = $items->count();

		if ( $count === 0 ) {
			throw new Item_Not_Found_Exception( 'Item not found.' );
		}

		if ( $count > 1 ) {
			throw new Multiple_Items_Found_Exception( sprintf( 'Multiple items found (%d items).', $count ) );
		}

		return $items->first();
	}

	/**
	 * Get the first item in the collection but throw an exception if no matching items exist.
	 *
	 * @param  (callable(TValue, TKey): bool)|string|null $key
	 * @param  mixed                                      $operator
	 * @param  mixed                                      $value
	 * @return TValue
	 *
	 * @throws Item_Not_Found_Exception Thrown if no items are found.
	 */
	public function first_or_fail( callable|string|null $key = null, mixed $operator = null, mixed $value = null ): mixed {
		$filter = func_num_args() > 1
			? $this->operator_for_where( ...func_get_args() )
			: $key;

		$items = $filter === null ? $this : $this->filter( $filter );

		if ( $items->is_empty() ) {
			throw new Item_Not_Found_Exception( 'Item not found.' );
		}

		return $items->first();
	}

	/**
	 * Get a flattened array of the items in the collection.
	 *
	 * @param  int|float $depth
	 * @return static<int, mixed>
	 */
	public function flatten( int|float $depth = INF ) {
		return new static( Arr::flatten( $this->items, $depth ) );
	}

	/**
	 * Flip the items in the collection.
	 *
	 * @return static<int|string, TKey>
	 */
	public function flip() {
		return new static( array_flip( $this->items ) );
	}

	/**
	 * Remove an item from the collection by key.
	 *
	 * @param  TKey|array<array-key, TKey> $keys
	 * @return static
	 */
	public function forget( $keys ) {
		foreach ( (array) $keys as $key ) {
			$this->offsetUnset( $key );
		}

		return $this;
	}

	/**
	 * Get an item from the collection by key.
	 *
	 * @template TGetDefault
	 *
	 * @param  TKey                                  $key
	 * @param  TGetDefault|(\Closure(): TGetDefault) $default
	 * @return TValue|TGetDefault
	 */
	public function get( $key, $default = null ) {
		if ( $this->offsetExists( $key ) ) {
			return $this->items[ $key ];
		}

		return value( $default );
	}

	/**
	 * Group an associative array by a field or using a callback.
	 *
	 * @template TGroupKey of array-key
	 *
	 * @param  (callable(TValue, TKey): TGroupKey)|array<mixed>|string $group_by The field or callback to group by.
	 * @param  bool                                                    $preserve_keys Whether to preserve the keys of the original array.
	 * @return static<($group_by is string ? array-key : ($group_by is array ? array-key : TGroupKey)), static<($preserve_keys is true ? TKey : int), ($group_by is array ? mixed : TValue)>>
	 */
	public function group_by( $group_by, $preserve_keys = false ) {
		if ( ! $this->use_as_callable( $group_by ) && is_array( $group_by ) ) {
			$next_groups = $group_by;

			$group_by = array_shift( $next_groups );
		}

		$group_by = $this->value_retriever( $group_by );

		$results = [];

		foreach ( $this->items as $key => $value ) {
			$group_keys = $group_by( $value, $key );

			if ( ! is_array( $group_keys ) ) {
				$group_keys = [ $group_keys ];
			}

			foreach ( $group_keys as $group_key ) {
				$group_key = is_bool( $group_key ) ? (int) $group_key : $group_key;

				if ( ! array_key_exists( $group_key, $results ) ) {
					$results[ $group_key ] = new static();
				}

				$results[ $group_key ]->offsetSet( $preserve_keys ? $key : null, $value );
			}
		}

		$result = new static( $results );

		if ( ! empty( $next_groups ) ) {
			return $result->map->group_by( $next_groups, $preserve_keys ); // @phpstan-ignore-line undefined method
		}

		return $result;
	}

	/**
	 * Key an associative array by a field or using a callback.
	 *
	 * @param  (callable(TValue, TKey): array-key)|string[]|string $key_by The field or callback to key by.
	 * @return static<array-key, TValue>
	 */
	public function key_by( $key_by ) {
		$key_by = $this->value_retriever( $key_by );

		$results = [];

		foreach ( $this->items as $key => $item ) {
			$resolved_key = $key_by( $item, $key );

			if ( is_object( $resolved_key ) ) {
				$resolved_key = (string) $resolved_key; // @phpstan-ignore-line cast.string
			}

			$results[ $resolved_key ] = $item;
		}

		return new static( $results );
	}

	/**
	 * Determine if an item exists in the collection by key.
	 *
	 * @param  TKey|array<array-key, TKey> $key
	 */
	public function has( $key ): bool {
		$keys = is_array( $key ) ? $key : func_get_args();

		foreach ( $keys as $key ) {
			if ( ! $this->offsetExists( $key ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine if any of the keys exist in the collection.
	 *
	 * @param  mixed $key
	 */
	public function has_any( mixed $key ): bool {
		$keys = is_array( $key ) ? $key : func_get_args();

		foreach ( $keys as $key ) {
			if ( $this->offsetExists( $key ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Concatenate values of a given key as a string.
	 *
	 * @param callable|string|null $value
	 * @param string|null          $glue
	 */
	public function implode( $value, $glue = null ): string {
		if ( $this->use_as_callable( $value ) ) {
			return implode( $glue ?? '', $this->map( $value )->all() );
		}

		$first = $this->first();

		if ( is_array( $first ) || ( is_object( $first ) && ! $first instanceof \Stringable ) ) {
			return implode( $glue ?? '', $this->pluck( $value )->all() );
		}

		return implode( $value ?? '', $this->items );
	}

	/**
	 * Concatenate values of a given key as a string and returns a stringable class.
	 *
	 * @param callable|string|null $value
	 * @param string|null          $glue
	 */
	public function implode_str( $value, $glue = null ): Stringable {
		return new Stringable( $this->implode( $value, $glue ) );
	}

	/**
	 * Intersect the collection with the given items.
	 *
	 * @param \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @return static
	 */
	public function intersect( $items ) {
		return new static( array_intersect( $this->items, $this->get_arrayable_items( $items ) ) );
	}

	/**
	 * Intersect the collection with the given items, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue> $items
	 * @param  callable(TValue, TValue): int                                                      $callback
	 * @return static
	 */
	public function intersect_using( mixed $items, callable $callback ) {
		return new static( array_uintersect( $this->items, $this->get_arrayable_items( $items ), $callback ) );
	}

	/**
	 * Intersect the collection with the given items with additional index check.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @return static
	 */
	public function intersect_assoc( $items ) {
		return new static( array_intersect_assoc( $this->items, $this->get_arrayable_items( $items ) ) );
	}

	/**
	 * Intersect the collection with the given items with additional index check, using the callback.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue> $items
	 * @param  callable(TValue, TValue): int                                                      $callback
	 * @return static
	 */
	public function intersect_assoc_using( $items, callable $callback ) {
		return new static( array_intersect_uassoc( $this->items, $this->get_arrayable_items( $items ), $callback ) );
	}

	/**
	 * Intersect the collection with the given items by key.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @return static
	 */
	public function intersect_by_keys( $items ) {
		return new static(
			array_intersect_key(
				$this->items,
				$this->get_arrayable_items( $items )
			)
		);
	}

	/**
	 * Determine if the collection is empty or not.
	 */
	public function is_empty(): bool {
		return empty( $this->items );
	}

	/**
	 * Determine if the collection contains a single item.
	 */
	public function contains_one_item(): bool {
		return $this->count() === 1;
	}

	/**
	 * Join all items from the collection using a string. The final items can use a separate glue string.
	 *
	 * @param    string $glue
	 * @param    string $final_glue
	 */
	public function join( string $glue, string $final_glue = '' ): string {
		if ( '' === $final_glue ) {
			return $this->implode( $glue );
		}

		$count = $this->count();

		if ( 0 === $count ) {
			return '';
		}

		if ( 1 === $count ) {
			return $this->last();
		}

		$collection = new static( $this->items );

		$final_item = $collection->pop();

		return $collection->implode( $glue ) . $final_glue . $final_item;
	}

	/**
	 * Get the keys of the collection items.
	 *
	 * @return static<int, TKey>
	 */
	public function keys() {
		return new static( array_keys( $this->items ) );
	}

	/**
	 * Get the last item from the collection.
	 *
	 * @template TLastDefault
	 *
	 * @param  (callable(TValue, TKey): bool)|null     $callback
	 * @param  TLastDefault|(\Closure(): TLastDefault) $default
	 * @return TValue|TLastDefault
	 */
	public function last( ?callable $callback = null, $default = null ): mixed {
		return Arr::last( $this->items, $callback, $default );
	}

	/**
	 * Get the values of a given key.
	 *
	 * @param  string|int|array<array-key, string> $value
	 * @param  string|null                         $key
	 * @return static<array-key, mixed>
	 */
	public function pluck( $value, $key = null ) {
		return new static( Arr::pluck( $this->items, $value, $key ) );
	}

	/**
	 * Run a map over each of the items.
	 *
	 * @template TMapValue
	 *
	 * @param  callable(TValue, TKey): TMapValue $callback
	 * @return static<TKey, TMapValue>
	 */
	public function map( callable $callback ) {
		$keys = array_keys( $this->items );

		$items = array_map( $callback, $this->items, $keys );

		return new static( array_combine( $keys, $items ) );
	}

	/**
	 * Run a dictionary map over the items.
	 *
	 * The callback should return an associative array with a single key/value pair.
	 *
	 * @template TMapToDictionaryKey of array-key
	 * @template TMapToDictionaryValue
	 *
	 * @param  callable(TValue, TKey): array<TMapToDictionaryKey, TMapToDictionaryValue> $callback
	 * @return static<TMapToDictionaryKey, array<int, TMapToDictionaryValue>>
	 */
	public function map_to_dictionary( callable $callback ) {
		$dictionary = [];

		foreach ( $this->items as $key => $item ) {
			$pair = $callback( $item, $key );

			if ( ! $pair || ! is_array( $pair ) ) {
				continue;
			}

			$key = key( $pair );

			$value = reset( $pair );

			if ( ! isset( $dictionary[ $key ] ) ) {
				$dictionary[ $key ] = [];
			}

			$dictionary[ $key ][] = $value;
		}

		return new static( $dictionary );
	}

	/**
	 * Run an associative map over each of the items.
	 *
	 * The callback should return an associative array with a single key/value pair.
	 *
	 * @template TMapWithKeysKey of array-key
	 * @template TMapWithKeysValue
	 *
	 * @param  callable(TValue, TKey): array<TMapWithKeysKey, TMapWithKeysValue> $callback
	 * @return static<TMapWithKeysKey, TMapWithKeysValue>
	 */
	public function map_with_keys( callable $callback ) {
		$result = [];

		foreach ( $this->items as $key => $value ) {
			$assoc = $callback( $value, $key );

			if ( ! is_array( $assoc ) ) {
				continue;
			}

			foreach ( $assoc as $map_key => $map_value ) {
				$result[ $map_key ] = $map_value;
			}
		}

		return new static( $result );
	}

	/**
	 * Map all items to integers.
	 *
	 * @return static<TKey, int>
	 */
	public function map_to_integers(): static {
		return $this->map( fn ( $value ) => (int) $value );
	}

	/**
	 * Merge the collection with the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @return static
	 */
	public function merge( $items ) {
		return new static( array_merge( $this->items, $this->get_arrayable_items( $items ) ) );
	}

	/**
	 * Recursively merge the collection with the given items.
	 *
	 * @template TMergeRecursiveValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TMergeRecursiveValue>|iterable<TKey, TMergeRecursiveValue> $items
	 * @return static<TKey, TValue|TMergeRecursiveValue>
	 */
	public function merge_recursive( $items ) {
		return new static( array_merge_recursive( $this->items, $this->get_arrayable_items( $items ) ) );
	}

	/**
	 * Create a collection by using this collection for keys and another for its values.
	 *
	 * @template TCombineValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TCombineValue>|iterable<array-key, TCombineValue> $values
	 * @return static<TValue, TCombineValue>
	 */
	public function combine( $values ) {
		return new static( array_combine( $this->all(), $this->get_arrayable_items( $values ) ) );
	}

	/**
	 * Union the collection with the given items.
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @return static
	 */
	public function union( $items ) {
		return new static( $this->items + $this->get_arrayable_items( $items ) );
	}

	/**
	 * Create a new collection consisting of every n-th element.
	 *
	 * @param    int $step
	 * @param    int $offset
	 * @return static
	 */
	public function nth( $step, $offset = 0 ) {
		$new = [];

		$position = 0;

		foreach ( $this->items as $item ) {
			if ( $position % $step === $offset ) {
				$new[] = $item;
			}

			$position++;
		}

		return new static( $new );
	}

	/**
	 * Get the items with the specified keys.
	 *
	 * @param  \Mantle\Support\Enumerable<array-key, TKey>|array<array-key, TKey>|string|null $keys
	 * @return static
	 */
	public function only( $keys ) {
		if ( is_null( $keys ) ) {
			return new static( $this->items );
		}

		if ( $keys instanceof Enumerable ) {
			$keys = $keys->all();
		}

		$keys = is_array( $keys ) ? $keys : func_get_args();

		return new static( Arr::only( $this->items, $keys ) );
	}

	/**
	 * Get the items in an collection of arrays with filtered child keys.
	 *
	 * @param TKey[]|TKey|static<int, TKey> $keys The keys to filter by.
	 * @return static<TKey, array<array-key, mixed>>
	 */
	public function only_children( $keys ): static {
		if ( empty( $keys ) ) {
			return new static( $this->items );
		}

		if ( $keys instanceof Arrayable ) {
			$keys = $keys->to_array();
		}

		$keys = is_array( $keys ) ? $keys : func_get_args();

		return $this->map(
			fn ( $item ) => Arr::only( (array) $item, $keys ),
		);
	}

	/**
	 * Get and remove the last item from the collection.
	 *
	 * @return static<int, TValue>|TValue|null
	 */
	public function pop(): mixed {
		return array_pop( $this->items );
	}

	/**
	 * Push an item onto the beginning of the collection.
	 *
	 * To push multiple items onto the beginning of the collection, use `prepend_many()`.
	 *
	 * @param  TValue          $value
	 * @param  int|string|null $key Array key to use for the value, optional.
	 */
	public function prepend( mixed $value, int|string|null $key = null ): static {
		$this->items = Arr::prepend( $this->items, $value, $key );

		return $this;
	}

	/**
	 * Push one or more items onto the beginning of the collection.
	 *
	 * @param  TValue ...$values
	 */
	public function prepend_many( ...$values ): static {
		array_unshift( $this->items, ...$values );

		return $this;
	}

	/**
	 * Push one or more items onto the end of the collection.
	 *
	 * @param  TValue ...$values
	 */
	public function push( ...$values ): static {
		foreach ( $values as $value ) {
			$this->items[] = $value;
		}

		return $this;
	}

	/**
	 * Push all of the given items onto the collection.
	 *
	 * @param  iterable<array-key, TValue> $source
	 * @return static
	 */
	public function concat( $source ) {
		$result = new static( $this );

		foreach ( $source as $item ) {
			$result->push( $item );
		}

		return $result;
	}

	/**
	 * Get and remove an item from the collection.
	 *
	 * @template TPullDefault
	 *
	 * @param  TKey                                    $key
	 * @param  TPullDefault|(\Closure(): TPullDefault) $default
	 * @return TValue|TPullDefault
	 */
	public function pull( $key, $default = null ) {
		return Arr::pull( $this->items, $key, $default );
	}

	/**
	 * Put an item in the collection by key.
	 *
	 * @param    TKey   $key
	 * @param    TValue $value
	 * @return static
	 */
	public function put( $key, $value ) {
		$this->offsetSet( $key, $value );

		return $this;
	}

	/**
	 * Get one or a specified number of items randomly from the collection.
	 *
	 * @param  (callable(self<TKey, TValue>): int)|int|null $number
	 * @return static<int, TValue>|TValue
	 *
	 * @throws \InvalidArgumentException Throws on number larger than collection length.
	 */
	public function random( $number = null ) {
		if ( is_null( $number ) ) {
			return Arr::random( $this->items );
		}

		return new static( Arr::random( $this->items, $number ) );
	}

	/**
	 * Reduce the collection to a single value.
	 *
	 * @template TReduceInitial
	 * @template TReduceReturnType
	 *
	 * @param  callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType $callback
	 * @param  TReduceInitial                                                              $initial
	 * @return TReduceReturnType|TReduceInitial
	 */
	public function reduce( callable $callback, $initial = null ) {
		$result = $initial;

		foreach ( $this as $key => $value ) {
			$result = $callback( $result, $value, $key );
		}

		return $result;
	}

	/**
	 * Replace the collection items with the given items.
	 *
	 * @param \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @return static
	 */
	public function replace( $items ) {
		return new static( array_replace( $this->items, $this->get_arrayable_items( $items ) ) );
	}

	/**
	 * Recursively replace the collection items with the given items.
	 *
	 * @param \Mantle\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
	 * @return static
	 */
	public function replace_recursive( $items ) {
		return new static( array_replace_recursive( $this->items, $this->get_arrayable_items( $items ) ) );
	}

	/**
	 * Reverse items order.
	 *
	 * @return static
	 */
	public function reverse() {
		return new static( array_reverse( $this->items, true ) );
	}

	/**
	 * Search the collection for a given value and return the corresponding key if successful.
	 *
	 * @param  TValue|(callable(TValue,TKey): bool) $value
	 * @param  bool                                 $strict
	 * @return TKey|false
	 */
	public function search( $value, $strict = false ) {
		if ( ! $this->use_as_callable( $value ) ) {
			return array_search( $value, $this->items, $strict ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		}

		foreach ( $this->items as $key => $item ) {
			if ( $value( $item, $key ) ) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Get the item before the given item.
	 *
	 * @param  TValue|(callable(TValue,TKey): bool) $value
	 * @param  bool                                 $strict
	 * @return TValue|null
	 */
	public function before( mixed $value, bool $strict = false ): mixed {
		$key = $this->search( $value, $strict );

		if ( $key === false ) {
			return null;
		}

		$keys     = $this->keys();
		$position = $keys->search( $key );

		if ( $position === 0 || $position === false ) {
			return null;
		}

		return $this->get( $keys->get( $position - 1 ) ); // @phpstan-ignore-line
	}

	/**
	 * Get the item after the given item.
	 *
	 * @param  TValue|(callable(TValue,TKey): bool) $value
	 * @param  bool                                 $strict
	 * @return TValue|null
	 */
	public function after( mixed $value, bool $strict = false ): mixed {
		$key = $this->search( $value, $strict );

		if ( $key === false ) {
			return null;
		}

		$keys     = $this->keys();
		$position = $keys->search( $key );

		if ( $position === $keys->count() - 1 || $position === false ) {
			return null;
		}

		return $this->get( $keys->get( $position + 1 ) ); // @phpstan-ignore-line
	}

	/**
	 * Get and remove the first item from the collection.
	 *
	 * @return TValue|null
	 */
	public function shift(): mixed {
		return array_shift( $this->items );
	}

	/**
	 * Shuffle the items in the collection.
	 *
	 * @param    int|null $seed
	 * @return static
	 */
	public function shuffle( $seed = null ) {
		return new static( Arr::shuffle( $this->items, $seed ) );
	}

	/**
	 * Skip the first {$count} items.
	 *
	 * @param    int $count
	 * @return static
	 */
	public function skip( $count ) {
		return $this->slice( $count );
	}

	/**
	 * Skip items in the collection until the given condition is met.
	 *
	 * @param  TValue|callable(TValue,TKey): bool $value
	 */
	public function skip_until( mixed $value ): static {
		$callback = $this->use_as_callable( $value ) ? $value : fn( $item ) => $item === $value;

		return $this->skip_while( fn( $item, $key ) => ! $callback( $item, $key ) );
	}

	/**
	 * Skip items in the collection while the given condition is met.
	 *
	 * @param  TValue|callable(TValue,TKey): bool $value
	 */
	public function skip_while( mixed $value ): static {
		$callback = $this->use_as_callable( $value ) ? $value : fn( $item ) => $item === $value;

		$items = [];
		$skip  = true;

		foreach ( $this->items as $key => $item ) {
			if ( $skip && ! $callback( $item, $key ) ) {
				$skip = false;
			}

			if ( ! $skip ) {
				$items[ $key ] = $item;
			}
		}

		return new static( $items );
	}

	/**
	 * Slice the underlying collection array.
	 *
	 * @param    int      $offset
	 * @param    int|null $length
	 * @return static
	 */
	public function slice( $offset, $length = null ) {
		return new static( array_slice( $this->items, $offset, $length, true ) );
	}

	/**
	 * Split a collection into a certain number of groups.
	 *
	 * @param  int $number_of_groups
	 * @return static<int, static>
	 */
	public function split( $number_of_groups ) {
		if ( $this->is_empty() ) {
			return new static();
		}

		$groups = new static();

		$group_size = floor( $this->count() / $number_of_groups );

		$remain = $this->count() % $number_of_groups;

		$start = 0;

		for ( $i = 0; $i < $number_of_groups; $i++ ) {
			$size = $group_size;

			if ( $i < $remain ) {
				$size++;
			}

			if ( $size !== 0.0 ) {
				$groups->push( new static( array_slice( $this->items, $start, (int) $size ) ) );

				$start += $size;
			}
		}

		return $groups;
	}

	/**
	 * Chunk the collection into chunks of the given size.
	 *
	 * @param  int $size
	 * @return static<int, static>
	 */
	public function chunk( $size ) {
		if ( $size <= 0 ) {
			return new static();
		}

		$chunks = [];

		foreach ( array_chunk( $this->items, $size, true ) as $chunk ) {
			$chunks[] = new static( $chunk );
		}

		return new static( $chunks );
	}

	/**
	 * Split a collection into a certain number of groups, and fill the first groups completely.
	 *
	 * @param  int $number_of_groups
	 * @return static<int, static>
	 */
	public function split_in( int $number_of_groups ) {
		return $this->chunk( (int) ceil( $this->count() / $number_of_groups ) );
	}

	/**
	 * Chunk the collection into chunks with a callback.
	 *
	 * @param  callable(TValue, TKey, static<int, TValue>): bool $callback
	 * @return static<int, static<int, TValue>>
	 */
	public function chunk_while( callable $callback ): static {
		$chunks = [];
		$chunk  = new static();

		foreach ( $this->items as $key => $value ) {
			if ( $chunk->is_not_empty() && ! $callback( $value, $key, $chunk ) ) {
				$chunks[] = $chunk;
				$chunk    = new static();
			}

			$chunk->put( $key, $value );
		}

		if ( $chunk->is_not_empty() ) {
			$chunks[] = $chunk;
		}

		return new static( $chunks );
	}

	/**
	 * Create chunks representing a "sliding window" view of the items in the collection.
	 *
	 * @param  int $size
	 * @param  int $step
	 * @return static<int, static>
	 */
	public function sliding( int $size = 2, int $step = 1 ): static {
		$chunks = [];
		$keys   = array_keys( $this->items );
		$values = array_values( $this->items );

		$counter = count( $this->items );

		for ( $i = 0; $i < $counter; $i += $step ) {
			$chunk_keys   = array_slice( $keys, $i, $size );
			$chunk_values = array_slice( $values, $i, $size );

			if ( count( $chunk_keys ) === $size ) {
				$chunks[] = new static( array_combine( $chunk_keys, $chunk_values ) );
			}
		}

		return new static( $chunks );
	}

	/**
	 * Sort through each item with a callback.
	 *
	 * @param  (callable(TValue, TValue): int)|null|int $callback
	 * @return static
	 */
	public function sort( $callback = null ) {
		$items = $this->items;

		$callback && is_callable( $callback )
			? uasort( $items, $callback )
			: asort( $items, $callback ?? SORT_REGULAR );

		return new static( $items );
	}

	/**
	 * Sort items in descending order.
	 *
	 * @param    int $options
	 * @return static
	 */
	public function sort_desc( $options = SORT_REGULAR ) {
		$items = $this->items;

		arsort( $items, $options );

		return new static( $items );
	}

	/**
	 * Sort the collection using the given callback.
	 *
	 * @param  array<array-key, (callable(TValue, TValue): mixed)|(callable(TValue, TKey): mixed)|string|array{string, string}>|(callable(TValue, TKey): mixed)|string $callback
	 * @param    int                                                                                                                                                     $options
	 * @param    bool                                                                                                                                                    $descending
	 * @return static
	 */
	public function sort_by( $callback, $options = SORT_REGULAR, $descending = false ) {
		$results = [];

		$callback = $this->value_retriever( $callback );

		// First we will loop through the items and get the comparator from a callback
		// function which we were given. Then, we will sort the returned values and
		// and grab the corresponding values for the sorted keys from this array.
		foreach ( $this->items as $key => $value ) {
			$results[ $key ] = $callback( $value, $key );
		}

		$descending ? arsort( $results, $options )
							: asort( $results, $options );

		// Once we have sorted all of the keys in the array, we will loop through them
		// and grab the corresponding model so we can set the underlying items list
		// to the sorted version. Then we'll just return the collection instance.
		foreach ( array_keys( $results ) as $key ) {
			$results[ $key ] = $this->items[ $key ];
		}

		return new static( $results );
	}

	/**
	 * Sort the collection in descending order using the given callback.
	 *
	 * @param  array<array-key, (callable(TValue, TValue): mixed)|(callable(TValue, TKey): mixed)|string|array{string, string}>|(callable(TValue, TKey): mixed)|string $callback
	 * @param    int                                                                                                                                                     $options
	 * @return static
	 */
	public function sort_by_desc( $callback, $options = SORT_REGULAR ) {
		return $this->sort_by( $callback, $options, true );
	}

	/**
	 * Sort the collection keys.
	 *
	 * @param    int  $options
	 * @param    bool $descending
	 * @return static
	 */
	public function sort_keys( $options = SORT_REGULAR, $descending = false ) {
		$items = $this->items;

		$descending ? krsort( $items, $options ) : ksort( $items, $options );

		return new static( $items );
	}

	/**
	 * Sort the collection keys in descending order.
	 *
	 * @param    int $options
	 * @return static
	 */
	public function sort_keys_desc( $options = SORT_REGULAR ) {
		return $this->sort_keys( $options, true );
	}

	/**
	 * Sort the collection keys using a callback.
	 *
	 * @param  callable(TKey, TKey): int $callback
	 */
	public function sort_keys_using( callable $callback ): static {
		$items = $this->items;

		uksort( $items, $callback );

		return new static( $items );
	}

	/**
	 * Splice a portion of the underlying collection array.
	 *
	 * @param    int                      $offset
	 * @param    int|null                 $length
	 * @param    array<array-key, TValue> $replacement
	 * @return static
	 */
	public function splice( $offset, $length = null, $replacement = [] ) {
		if ( func_num_args() === 1 ) {
			return new static( array_splice( $this->items, $offset ) );
		}

		return new static( array_splice( $this->items, $offset, $length, $replacement ) );
	}

	/**
	 * Take the first or last {$limit} items.
	 *
	 * @param    int $limit
	 * @return static
	 */
	public function take( $limit ) {
		if ( $limit < 0 ) {
			return $this->slice( $limit, abs( $limit ) );
		}

		return $this->slice( 0, $limit );
	}

	/**
	 * Take items in the collection until the given condition is met.
	 *
	 * @param  TValue|callable(TValue,TKey): bool $value
	 * @return static<TKey, TValue>
	 */
	public function take_until( $value ): static {
		$callback = $this->use_as_callable( $value ) ? $value : fn( $item ) => $item === $value;

		$items = [];

		foreach ( $this->items as $key => $item ) {
			if ( $callback( $item, $key ) ) {
				break;
			}

			$items[ $key ] = $item;
		}

		return new static( $items );
	}

	/**
	 * Take items in the collection while the given condition is met.
	 *
	 * @param  TValue|callable(TValue,TKey): bool $value
	 * @return static<TKey, TValue>
	 */
	public function take_while( $value ): static {
		$callback = $this->use_as_callable( $value ) ? $value : fn( $item ) => $item === $value;

		return $this->take_until( fn( $item, $key ) => ! $callback( $item, $key ) );
	}

	/**
	 * Transform each item in the collection using a callback.
	 *
	 * @param  callable(TValue, TKey): TValue $callback
	 * @return static
	 */
	public function transform( callable $callback ) {
		$this->items = $this->map( $callback )->all();

		return $this;
	}

	/**
	 * Reset the keys on the underlying array.
	 *
	 * @return static<int, TValue>
	 */
	public function values() {
		return new static( array_values( $this->items ) );
	}

	/**
	 * Convert a flatten "dot" notation array into an expanded array.
	 *
	 * @return static
	 */
	public function undot() {
		$results = [];

		foreach ( $this->items as $key => $value ) {
			Arr::set( $results, $key, $value );
		}

		return new static( $results );
	}

	/**
	 * Zip the collection together with one or more arrays.
	 *
	 * E.g. new Collection([1, 2, 3])->zip([4, 5, 6]);
	 *          => [[1, 4], [2, 5], [3, 6]]
	 *
	 * @template TZipValue
	 *
	 * @param  \Mantle\Contracts\Support\Arrayable<array-key, TZipValue>|iterable<array-key, TZipValue> ...$items
	 * @return static<int, static<int, TValue|TZipValue>>
	 */
	public function zip( ...$items ) {
		$arrayable_items = array_map(
			$this->get_arrayable_items( ... ),
			$items,
		);

		$params = array_merge(
			[
				fn () => new static( func_get_args() ),
				$this->items,
			],
			$arrayable_items
		);

		return new static( array_map( ...$params ) ); // @phpstan-ignore-line return.type
	}

	/**
	 * Trim all values in the collection.
	 *
	 * @param  string $char_list Characters to trim, optional.
	 * @return static<TKey, string>
	 */
	public function trim( string $char_list = "\n\r\t\v\x00" ) {
		return new static( $this->map( fn ( $item ) => trim( (string) $item, $char_list ) ) ); // @phpstan-ignore-line return.type
	}

	/**
	 * Pad collection to the specified length with a value.
	 *
	 * @template TPadValue
	 *
	 * @param  int       $size
	 * @param  TPadValue $value
	 * @return static<int, TValue|TPadValue>
	 */
	public function pad( $size, $value ) {
		return new static( array_pad( $this->items, $size, $value ) );
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return \ArrayIterator<TKey, TValue>
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->items );
	}

	/**
	 * Count the number of items in the collection.
	 */
	public function count(): int {
		return count( $this->items );
	}

	/**
	 * Add an item to the collection.
	 *
	 * @param  TValue $item
	 * @return static
	 */
	public function add( $item ) {
		$this->items[] = $item;

		return $this;
	}

	/**
	 * Get a base Support collection instance from this collection.
	 *
	 * @return \Mantle\Support\Collection
	 */
	public function to_base() {
		return new self( $this );
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param mixed $key
	 */
	public function offsetExists( mixed $key ): bool {
		return ! is_null( $key ) && array_key_exists( $key, $this->items );
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param  mixed $key
	 */
	public function offsetGet( mixed $key ): mixed {
		return $this->items[ $key ];
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param    mixed $key
	 * @param    mixed $value
	 */
	public function offsetSet( mixed $key, mixed $value ): void {
		if ( is_null( $key ) ) {
			$this->items[] = $value;
		} else {
			$this->items[ $key ] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param mixed $key
	 */
	public function offsetUnset( mixed $key ): void {
		unset( $this->items[ $key ] );
	}
}
