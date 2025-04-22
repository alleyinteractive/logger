<?php
/**
 * Action class file
 *
 * @package Mantle
 */

namespace Mantle\Support\Attributes;

use Attribute;

/**
 * Hook Action Attribute
 *
 * Used to hook a method to an WordPress hook at a specific priority.
 */
#[Attribute( Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION )]
class Action extends Filter {}
