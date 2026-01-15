<?php
/**
 * Slack_Notifier class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

use Monolog\Handler\SlackWebhookHandler;
use Monolog\Logger;

/**
 * Slack notification helper using the existing AI_Logger infrastructure.
 */
class Slack_Notifier {

	/**
	 * Send a message to Slack using AI_Logger with a Slack handler.
	 *
	 * @param string $message The message to send.
	 * @param string $level Log level (debug, info, warning, error, critical, etc.).
	 * @param array  $context Additional context.
	 * @param array  $args Optional arguments for customization.
	 * @return void
	 */
	public static function send(
		string $message,
		string $level = 'alert',
		array $context = [],
		array $args = []
	): void {

		$webhook_url = Settings::instance()->get( 'ai_logger_slack_webhook_url' );

		if ( empty( $webhook_url ) ) {
			return;
		}

		$args = wp_parse_args( $args, [
			'channel'    => null,
			'username'   => 'Logger',
			'icon_emoji' => ':log:',
		] );

		$slack_handler = new SlackWebhookHandler(
			$webhook_url,
			$args['channel'],
			$args['username'],
			true,
			$args['icon_emoji'],
			false,
			true,
			Logger::ALERT
		);

		AI_Logger::instance()
		         ->with_handlers( [ $slack_handler ] )
		         ->log( $level, $message, $context );
	}

	/**
	 * Send an alert notification.
	 *
	 * @param string $message Alert message.
	 * @param array  $context Additional context.
	 * @return void
	 */
	public static function alert( string $message, array $context = [] ): void {
		self::send( $message, 'alert', $context );
	}

}
