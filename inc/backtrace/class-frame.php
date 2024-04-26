<?php
namespace AI_Logger\Backtrace;

use Spatie\Backtrace\Frame as SpatieFrame;

/**
 * Frame extension class.
 *
 * Stores the frame's code snippet in the frame itself for serialization and storage.
 */
class Frame extends SpatieFrame {
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
		return new self(
			$frame->file,
			$frame->lineNumber,
			$frame->arguments,
			$frame->method,
			$frame->class,
			$frame->applicationFrame,
			$frame->textSnippet
		);
	}

	/**
	 * Load the code snippet for this frame.
	 *
	 * @param int $line_count Number of lines to load.
	 */
	public function load_snippet( int $line_count ): void {
		$this->snippet = $this->getSnippet( $line_count );
	}
}
