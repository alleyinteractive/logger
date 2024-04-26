<?php
/**
 * Display details about a specific log record (viewing a single log in the admin).
 *
 * @package AI_Logger
 */

use AI_Logger\Data_Structures;

use function Mantle\Support\Helpers\str;

if ( empty( $log ) ) {
	return;
}

/**
 * Render the legacy backtrace.
 *
 * Previously the backtrace was fetched using the `debug_backtrace` function.
 *
 * @param array $backtrace Backtrace to render.
 */
function ai_logger_render_legacy_backtrace( array $backtrace ): void {
	?>
	<ul>
		<?php foreach ( $backtrace as $item ) : ?>
			<li>
				<?php
				$function = ! empty( $item['class'] ) ? $item['class'] . '::' . $item['function'] : $item['function'];
				printf(
					'<code>%s</code> in <strong>%s</strong> at line <strong>%s</strong>',
					esc_html( $item['file'] ?? 'n/a' ),
					esc_html( $function ),
					esc_html( $item['line'] ?? '?' )
				);
				?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Render the backtrace powered by spatie/backtrace.
 *
 * @param array<\AI_Logger\Backtrace\Frame> $backtrace Backtrace to render.
 */
function ai_logger_render_backtrace( array $backtrace ): void {
	?>
	<div class="ai-log-backtrace">
		<?php
		foreach ( $backtrace as $i => $item ) {
			?>
			<details <?php if ( 0 === $i ) { echo 'open'; } ?>>
				<summary>
					<strong><?php echo esc_html( str( $item->file )->after( ABSPATH ) ); ?></strong>
					<?php esc_html_e( 'in', 'ai-logger' ); ?>
					<?php if ( ! empty( $item->class ) ) : ?>
						<strong><?php echo esc_html( $item->class . '::' . $item->method ); ?></strong>
					<?php else : ?>
						<strong><?php echo esc_html( $item->method ); ?></strong>
					<?php endif; ?>
					<?php if ( ! empty( $item->lineNumber ) ) : ?>
						<?php esc_html_e( 'at line', 'ai-logger' ); ?>
						<strong><?php echo esc_html( $item->lineNumber ); ?></strong>
					<?php endif; ?>
				</summary>

				<?php if ( ! empty( $item->snippet ) && is_array( $item->snippet ) ) : ?>
					<?php
					$attributes = '';

					if ( count( $item->snippet ) > 1 ) {
						$attributes = sprintf(
							'data-start="%d" data-line-offset="%d" data-line="%s"',
							(int) array_key_first( $item->snippet ),
							(int) array_key_first( $item->snippet ),
							(int) $item->lineNumber,
						);
					}

					printf(
						'<pre class="language-php %s" %s><code class="language-php">%s</code></pre>',
						count( $item->snippet ) > 1 ? 'line-numbers' : '',
						$attributes,
						esc_html( implode( PHP_EOL, $item->snippet ) ),
					);
					?>
				<?php else : ?>
					<p><?php esc_html_e( 'No snippet available.', 'ai-logger' ); ?></p>
				<?php endif; ?>
			</details>
			<?php
		}
		?>
	</div>

	<!-- TODO move to wp_enqueue_* -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-highlight/prism-line-highlight.min.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-highlight/prism-line-highlight.min.js"></script>
	<script>
	// Prism.highlightAll();
	</script>
	<?php
}

/**
 * Render a table of data.
 *
 * @param array $data Data to render.
 */
function ai_logger_render_table( array $data ): void {
	foreach ( $data as $key => $value ) {
		// Prevent backtrace display here as it is displayed separately.
		if ( 'backtrace' === $key ) {
			continue;
		}

		?>
		<tr>
			<td>
				<code><?php echo esc_html( $key ); ?></code>
			</td>
			<td>
				<?php if ( 'user' === $key ) : ?>
					<table>
						<?php ai_logger_render_table( (array) $value ); ?>
					</table>
				<?php elseif ( is_scalar( $value ) ) : ?>
					<pre><?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen, Squiz.PHP.EmbeddedPhp.ContentAfterOpen
					if ( 0 === strpos( $value, '{' ) || 0 === strpos( $value, '[' ) ) {
						$maybe_json_value = json_decode( $value );
						if ( ! empty( $maybe_json_value ) ) {
							$value = wp_json_encode( $maybe_json_value, JSON_PRETTY_PRINT );
						}
					}

					echo esc_html( $value );
					?>
				</pre>
				<?php elseif ( null === $value ) : ?>
					<code>(null)</code>
				<?php else : ?>
					<pre><?php echo wp_json_encode( $value, JSON_PRETTY_PRINT ); ?></pre>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}
}

$ai_logger_date_format = 'M d, Y h:i:s A O';

?>
<div class="ai-log-display">
	<h4><?php esc_html_e( 'Log Summary', 'ai-logger' ); ?></h4>
	<table class="widefat">
		<tr>
			<td><?php esc_html_e( 'Message', 'ai-logger' ); ?></td>
			<td><?php echo nl2br( esc_html( $log['message'] ?? '' ) ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Context', 'ai-logger' ); ?></td>
			<td>
				<?php
				$level = get_the_terms( $post->ID, Data_Structures::TAXONOMY_CONTEXT );

				if ( ! empty( $level ) && ! is_wp_error( $level ) ) {
					$level = array_shift( $level );

					printf(
						'<a href="%s">%s</a>',
						esc_url( admin_url( 'edit.php?post_type=ai_log&ai_log_context=' . $level->slug ) ),
						esc_html( $level->name )
					);
				}
				?>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Level', 'ai-logger' ); ?></td>
			<td>
				<?php
				$level = get_the_terms( $post->ID, Data_Structures::TAXONOMY_LEVEL );

				if ( ! empty( $level ) && ! is_wp_error( $level ) ) {
					$level = array_shift( $level );

					printf(
						'<a href="%s">%s</a>',
						esc_url( admin_url( 'edit.php?post_type=ai_log&ai_log_level=' . $level->slug ) ),
						esc_html( $level->name )
					);
				}
				?>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Channel', 'ai-logger' ); ?></td>
			<td><?php echo esc_html( $log['channel'] ?? '' ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Timestamp', 'ai-logger' ); ?></td>
			<td><?php echo esc_html( $log['datetime']->format( $ai_logger_date_format ) ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Timestamp Local', 'ai-logger' ); ?></td>
			<td>
			<?php echo esc_html( $log['datetime']->setTimezone( wp_timezone() )->format( $ai_logger_date_format ) ); ?>
			</td>
		</tr>
	</table>

	<?php if ( ! empty( $log['context'] ) ) : ?>
		<h4><?php esc_html_e( 'Context', 'ai-logger' ); ?></h4>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'ai-logger' ); ?></th>
					<th><?php esc_html_e( 'Value', 'ai-logger' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php ai_logger_render_table( $log['context'] ); ?>
			</tbody>
		</table>
	<?php endif; ?>

	<!-- Backtrace Display -->
	<?php if ( ! empty( $log['extra']['backtrace'] ) ) : ?>
		<h4><?php esc_html_e( 'Backtrace', 'ai-logger' ); ?></h4>
		<?php if ( isset( $log['extra']['backtrace'][0] ) && is_array( $log['extra']['backtrace'][0] ) ) : ?>
			<?php ai_logger_render_legacy_backtrace( $log['extra']['backtrace'] ); ?>
		<?php else : ?>
			<?php ai_logger_render_backtrace( $log['extra']['backtrace'] ); ?>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! empty( $log['extra'] ) ) : ?>
		<h4><?php esc_html_e( 'Extra', 'ai-logger' ); ?></h4>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'ai-logger' ); ?></th>
					<th><?php esc_html_e( 'Value', 'ai-logger' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php ai_logger_render_table( $log['extra'] ); ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
