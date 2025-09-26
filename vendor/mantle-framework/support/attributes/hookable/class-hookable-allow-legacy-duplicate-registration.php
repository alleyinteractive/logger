<?php
/**
 * Allow_Legacy_Duplicate_Registration class file
 *
 * @package Mantle
 */

namespace Mantle\Support\Attributes\Hookable;

/**
 * Allow for legacy duplicate registration of hooks using Hookable.
 *
 * Before Mantle v1.6, the Hookable trait would allow for duplicate registration
 * of hooks for a class method if the method name used the `on_{hook}` or
 * `action_{hook}` naming convention and the method used the `Action` or
 * `Filter` attribute. If it had the same priority it would be deduplicated.
 * With v1.6, if a method had a `Action` or `Filter` attribute at all, it would
 * completely ignore the method name for hook registration consideration.
 *
 * This attribute allows for the legacy behavior to be restored, where the
 * method name won't be ignored when an attribute is present on the method. This
 * can cause duplicate hook registration.
 *
 * This will be removed with Mantle v2.0.
 *
 * @see \Mantle\Support\Traits\Hookable
 */
#[\Attribute( \Attribute::TARGET_CLASS )]
class Allow_Legacy_Duplicate_Registration {}
