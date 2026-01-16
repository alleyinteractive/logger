<?php
/**
 * Slack_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use AI_Logger\Settings;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Logger;

/**
 * Slack notification handler.
 */
class Slack_Handler extends SlackWebhookHandler {

	/**
	 * Constructor.
	 *
	 * @param string|int $level The minimum logging level at which this handler will be triggered.
	 * @param array $args Optional arguments for customization.
	 */
	public function __construct( $level = Logger::ALERT, array $args = [] ) {
		$webhook_url = Settings::instance()->get( 'slack_webhook_url' ) ?: '';

		$args = wp_parse_args( $args, [
			'channel'    => null,
			'username'   => 'Logger',
			'icon_emoji' => ':log:',
		] );

		parent::__construct(
			$webhook_url,
			$args['channel'],
			$args['username'],
			true,
			$args['icon_emoji'],
			false,
			false,
			Logger::toMonologLevel( $level )
		);
	}

	/**
	 * Checks whether the given record will be handled by this handler.
	 *
	 * @param array $record Log Record.
	 *
	 * @return bool
	 */
	public function isHandling( array $record ): bool {
		// Don't handle if webhook URL is not configured.
		$webhook_url = Settings::instance()->get( 'slack_webhook_url' );
		if ( empty( $webhook_url ) ) {
			return false;
		}

		return parent::isHandling( $record );
	}
}
