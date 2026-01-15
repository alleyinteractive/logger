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
		string $level = 'info',
		array $context = [],
		array $args = []
	): void {

		$webhook_url = Settings::instance()->get( 'ai_logger_slack_webhook_url' );

		if ( empty( $webhook_url ) ) {
			return;
		}

		$args = wp_parse_args( $args, [
			'channel'    => null,
			'username'   => 'WordPress',
			'icon_emoji' => ':speech_balloon:',
		] );

		$slack_handler = new SlackWebhookHandler(
			$webhook_url,
			$args['channel'],
			$args['username'],
			true,
			$args['icon_emoji'],
			false,
			true,
			Logger::DEBUG
		);

		AI_Logger::instance()
		         ->with_handlers( [ $slack_handler ] )
		         ->log( $level, $message, $context );
	}

	/**
	 * Send an error notification.
	 *
	 * @param string $message Error message.
	 * @param array  $context Additional context.
	 * @return void
	 */
	public static function error( string $message, array $context = [] ): void {
		self::send( $message, 'error', $context, [
			'icon_emoji' => ':x:',
		] );
	}

	/**
	 * Send a warning notification.
	 *
	 * @param string $message Warning message.
	 * @param array  $context Additional context.
	 * @return void
	 */
	public static function warning( string $message, array $context = [] ): void {
		self::send( $message, 'warning', $context, [
			'icon_emoji' => ':warning:',
		] );
	}

	/**
	 * Send a critical notification.
	 *
	 * @param string $message Critical message.
	 * @param array  $context Additional context.
	 * @return void
	 */
	public static function critical( string $message, array $context = [] ): void {
		self::send( $message, 'critical', $context, [
			'icon_emoji' => ':fire:',
		] );
	}

}
