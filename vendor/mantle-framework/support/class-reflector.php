<?php
/**
 * Reflector class file.
 *
 * @package Mantle
 */

namespace Mantle\Support;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * Reflector class.
 *
 * Provides methods to inspect classes, methods, and parameters, including
 * reading attributes and determining parameter types.
 */
class Reflector {
	/**
	 * Get the class name of the given parameter's type, if possible.
	 *
	 * @param  \ReflectionParameter $parameter
	 * @return string|null
	 */
	public static function get_parameter_class_name( $parameter ) {
		$type = $parameter->getType();

		if ( ! $type instanceof ReflectionNamedType || $type->isBuiltin() ) {
			return null;
		}

		$name = $type->getName();

		if ( ! is_null( $class = $parameter->getDeclaringClass() ) ) {
			if ( 'self' === $name ) {
				return $class->getName();
			}

			if ( 'parent' === $name && $parent = $class->getParentClass() ) {
				return $parent->getName();
			}
		}

		return $name;
	}

	/**
	 * Get the class names of the given parameter's type, including union types.
	 *
	 * @param  \ReflectionParameter $parameter
	 * @return array<string>
	 */
	public static function get_parameter_class_names( $parameter ): array {
		$type = $parameter->getType();

		if ( ! $type instanceof ReflectionUnionType ) {
			return array_filter( [ static::get_parameter_class_name( $parameter ) ] );
		}

		$union_types = [];

		foreach ( $type->getTypes() as $listed_type ) {
			if ( ! $listed_type instanceof ReflectionNamedType || $listed_type->isBuiltin() ) {
				continue;
			}

			$union_types[] = static::get_type_name( $parameter, $listed_type );
		}

		return array_filter( $union_types );
	}

	/**
	 * Get the given type's class name.
	 *
	 * @param  \ReflectionParameter $parameter
	 * @param  \ReflectionNamedType $type
	 * @return string
	 */
	protected static function get_type_name( $parameter, $type ) {
		$name = $type->getName();

		if ( ! is_null( $class = $parameter->getDeclaringClass() ) ) {
			if ( 'self' === $name ) {
				return $class->getName();
			}

			if ( 'parent' === $name && $parent = $class->getParentClass() ) {
				return $parent->getName();
			}
		}

		return $name;
	}

	/**
	 * Determine if the parameter's type is a subclass of the given type.
	 *
	 * @param  \ReflectionParameter $parameter
	 * @param  string               $class_name
	 */
	public static function is_parameter_subclass_of( \ReflectionParameter $parameter, string $class_name ): bool {
		$param_class_name = static::get_parameter_class_name( $parameter );

		return $param_class_name && class_exists( $param_class_name ) && ( new ReflectionClass( $param_class_name ) )->isSubclassOf( $class_name );
	}

	/**
	 * Get the attributes for a class or method.
	 *
	 * @see https://www.php.net/manual/en/reflectionclass.getattributes.php
	 *
	 * @template T of object
	 *
	 * @param  object|class-string  $class     The class name.
	 * @param  string               $method    The method name.
	 * @param  class-string<T>|null $attribute The attribute name to filter by, or null for all attributes.
	 * @param  int                  $flags     Flags to pass to getAttributes().
	 * @param  bool                 $inherit   Whether to include attributes from parent classes.
	 * @param  bool                 $inherit_from_class Whether to include attributes from the class itself.
	 * @return array<\ReflectionAttribute>
	 *
	 * @phpstan-param class-string<T>|null $attribute
	 * @phpstan-return ($attribute is null ? array<\ReflectionAttribute> : array<\ReflectionAttribute<T>>)
	 */
	public static function get_attributes_for_method( object|string $class, string $method, ?string $attribute = null, int $flags = 0, bool $inherit = true, bool $inherit_from_class = true ): array {
		$reflection = new ReflectionClass( $class );

		if ( ! $reflection->hasMethod( $method ) ) {
			return [];
		}

		return [
			...( $inherit_from_class ? static::get_attributes_for_class( $class, $attribute, $flags, $inherit ) : [] ),
			...$reflection->getMethod( $method )->getAttributes( $attribute, $flags ),
		];
	}

	/**
	 * Retrieve attributes for a class.
	 *
	 * Supports attributes on the class and all parent classes.
	 *
	 * @template T of object
	 *
	 * @param object|class-string  $class The class name or object instance.
	 * @param class-string<T>|null $attribute The attribute name to filter by, or null for all attributes.
	 * @param int                  $flags Flags to pass to getAttributes().
	 * @param bool                 $inherit Whether to include attributes from parent classes.
	 * @return array<\ReflectionAttribute> Returned in inheritance order (parent -> child).
	 *
	 * @phpstan-param class-string<T>|null $attribute
	 * @phpstan-return ($attribute is null ? array<\ReflectionAttribute> : array<\ReflectionAttribute<T>>)
	 */
	public static function get_attributes_for_class( object|string $class, ?string $attribute = null, int $flags = 0, bool $inherit = true ): array {
		$reflection = new ReflectionClass( $class );

		$attributes = $reflection->getAttributes( $attribute, $flags );

		if ( ! $inherit ) {
			return $attributes;
		}

		while ( $reflection = $reflection->getParentClass() ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$attributes = array_merge( $attributes, $reflection->getAttributes( $attribute, $flags ) );
		}

		// Reverse the order of attributes to maintain inheritance order (parent -> child).
		return array_reverse( $attributes );
	}
}
