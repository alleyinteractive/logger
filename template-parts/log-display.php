<?php
/**
 * Display details about a specific log record.
 *
 * @package AI_Logger
 */

if ( empty( $log ) ) {
	return;
}

/**
 * Render a table of data.
 *
 * @param array $data Data to render.
 */
function ai_logger_render_table( array $data ) {
	?>
	<?php foreach ( $data as $key => $value ) : ?>
		<tr>
			<td>
				<code><?php echo esc_html( $key ); ?></code>
			</td>
			<td>
				<?php if ( 'backtrace' === $key && is_array( $value ) ) : ?>
					<ul>
						<?php foreach ( $value as $item ) : ?>
							<li>
								<?php
								$function = ! empty( $item['class'] ) ? $item['class'] . '::' . $item['function'] : $item['function'];
								printf(
									'<code>%s</code> @ <code>%s</code> (%s)',
									esc_html( $item['file'] ?? 'n/a' ),
									esc_html( $function ),
									esc_html( $item['line'] ?? '?' )
								);
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php elseif ( is_scalar( $value ) ) : ?>
					<code><?php echo esc_html( $value ); ?></code>
				<?php elseif ( null === $value ) : ?>
					<code>(null)</code>
				<?php else : ?>
					<code><?php echo wp_json_encode( $value ); ?></code>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	endforeach;
}

$ai_logger_date_format = 'M d, Y h:i:s A O';

?>
<div class="ai-log-display">
	<h4><?php esc_html_e( 'Log Summary', 'ai-logger' ); ?></h4>
	<table class="widefat">
		<tr>
			<td><?php esc_html_e( 'Message', 'ai-logger' ); ?></td>
			<td><?php echo esc_html( $log['message'] ?? '' ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Level', 'ai-logger' ); ?></td>
			<td><?php echo esc_html( $log['level_name'] ?? '' ); ?></td>
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
