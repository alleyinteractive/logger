<?php
/**
 * Meta Box display.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

use Psr\Log\LogLevel;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// $logs comes from the parent function.
if ( empty( $logs ) ) {
	return;
}

$ai_logger_timestamp_format = 'm/d/Y H:i:s';
?>

<table class="widefat fixed meta-box-logs" width="100%">
	<thead>
		<tr>
			<th align="left"><?php esc_html_e( 'Level', 'ai-logger' ); ?></th>
			<th align="left"><?php esc_html_e( 'Message', 'ai-logger' ); ?></th>
			<th align="left"><?php esc_html_e( 'Context', 'ai-logger' ); ?></th>
			<th align="left"><?php esc_html_e( 'Timestamp', 'ai-logger' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $logs as $log ) : ?>
			<tr
				<?php if ( in_array( $log[0], [ LogLevel::EMERGENCY, LogLevel::CRITICAL ], true ) ) : ?>
					bgcolor="red"
				<?php elseif ( LogLevel::ALERT === $log[0] ) : ?>
					bgcolor="orange"
				<?php endif; ?>
			>
				<td><?php echo esc_html( $log[0] ?? '' ); ?></td>
				<td><?php echo esc_html( $log[1] ?? '' ); ?></td>
				<td>
					<?php
					if ( ! empty( $log[2] ) ) {
						if ( is_array( $log[2] ) ) {
							printf( '<code>%s</code>', wp_json_encode( $log[2] ) );
						} else {
							echo esc_html( $log[2] );
						}
					}
					?>
				</td>
				<td>
					<?php echo esc_html( date_i18n( $ai_logger_timestamp_format, (int) $log[3] ?? '', false ) ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
