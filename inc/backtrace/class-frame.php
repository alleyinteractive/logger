<?php
/**
 * Frame class file
 *
 * @package AI_Logger
 */

namespace AI_Logger\Backtrace;

use Spatie\Backtrace\Frame as SpatieFrame;
use WP_Hook;

/**
 * Frame extension class.
 *
 * Stores the frame's code snippet in the frame itself for serialization and storage.
 */
class Frame extends SpatieFrame {
	/**
	 * Hook methods to ignore.
	 *
	 * @var array<string>
	 */
	const HOOK_METHODS = [ 'do_action', 'do_action_ref_array', 'apply_filters', 'apply_filters_ref_array' ];

	/**
	 * Code snippet.
	 *
	 * @var array
	 */
	public array $snippet;

	/**
	 * Create a new instance from a SpatieFrame.
	 *
	 * @param SpatieFrame $frame SpatieFrame to create from.
	 * @return Frame
	 */
	public static function from_base( SpatieFrame $frame ): self {
		$instance = new self(
			file: $frame->file,
			lineNumber: $frame->lineNumber,
			arguments: $frame->arguments,
			method: $frame->method,
			class: $frame->class,
			isApplicationFrame: $frame->applicationFrame,
			textSnippet: $frame->textSnippet
		);

		// Escape the class name to prevent issues when storing backslashes.
		if ( ! empty( $instance->class ) ) {
			// Convert backslashes to forward slashes for storage. For an unknown
			// reason, the backslashes are being stripped out when storing the class
			// name. This is a workaround to prevent that.
			$instance->class = str_replace( '\\', '/', $instance->class );
		}

		return $instance;
	}

	/**
	 * Load the code snippet for this frame.
	 *
	 * @param int $line_count Number of lines to load.
	 */
	public function load_snippet( int $line_count ): void {
		// Prevent snippet from being loaded for specific internal frames which
		// don't make sense to store (such as do_action).
		if ( WP_Hook::class === $this->class ) {
			return;
		}

		if ( ! $this->class && in_array( $this->method, self::HOOK_METHODS, true ) ) {
			return;
		}

		$this->snippet = $this->getSnippet( $line_count );
	}
}
