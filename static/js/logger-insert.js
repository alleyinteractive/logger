/**
 * Enable ai-logger JavaScript logging.
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
