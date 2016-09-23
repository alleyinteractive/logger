/**
 * Enable ai-logger JavaScript logging.
 *
 * @param string Key for your log message.
 * @param string Log message.
 * @param object Arguments for the logger method e.g. { level: 'info', context: 'Testing', include_stack_trace: false }
 */
function aiLoggerInsert( key, message, args ) {
	if ( 'object' !== typeof window.aiLogger
		|| 'undefined' === typeof window.aiLogger.url
		|| 'undefined' === typeof window.aiLogger.nonce
			) {
		return false;
	}

	// Args are optional.
	args = args || {};

	// Post data to the admin.
	jQuery.post( window.aiLogger.url, {
		'action': 'ai_logger_insert',
		'key': key,
		'message': message,
		'args': args,
		'ai_logger_nonce': window.aiLogger.nonce,
	} );
}
