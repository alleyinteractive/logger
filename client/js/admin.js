(function( $ ) {

	'use strict';

	// main object used to organize the event listeners and callbacks
	aiLogger = {

		/* PROPERTIES */

		// helper variable to determine if edit view plugin is enabled
		someProperty : false,

		/* METHODS */

		/**
		 * Register all of the event listeners and initialize anything
		 * else when the document is ready
		 */
		initialize : function() {
			var self = this;
		},

		/**
		 * Event handler or helper method for this object
		 */
		someMethod : function() {
			var self = this;
		},

	};

	$(function() {
		aiLogger.initialize();
	});

})( jQuery );
