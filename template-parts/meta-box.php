<?php
/**
 * Meta Box display.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

use Monolog\Logger;

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
			<th align="left"><?php esc_html_e( 'Channel', 'ai-logger' ); ?></th>
			<th align="left"><?php esc_html_e( 'Message', 'ai-logger' ); ?></th>
			<th align="left"><?php esc_html_e( 'Context', 'ai-logger' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $logs as $log ) : ?>
			<tr
				<?php if ( $log['level'] >= Logger::ALERT ) : ?>
					bgcolor="red"
				<?php elseif ( $log['level'] >= Logger::ERROR ) : ?>
					bgcolor="orange"
				<?php endif; ?>
			>
				<td>
					<?php echo esc_html( $log['level_name'] ?? '' ); ?>
					<br />
					<?php echo esc_html( date_i18n( $ai_logger_timestamp_format, $log['datetime']->format( 'U' ), false ) ); ?>
				</td>
				<td><?php echo esc_html( $log['channel'] ?? '' ); ?></td>
				<td><?php echo esc_html( $log['message'] ?? '' ); ?></td>
				<td>
					<?php
					if ( ! empty( $log['context'] ) ) {
						printf( '<code>%s</code>', wp_json_encode( $log['context'] ) );
					}

					if ( ! empty( $log['extra'] ) ) {
						printf( '<code>%s</code>', wp_json_encode( $log['extra'] ) );
					}
					?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
