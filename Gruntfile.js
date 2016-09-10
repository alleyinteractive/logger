'use strict';

module.exports = function(grunt) {
	var path = require('path');

	require('load-grunt-config')(grunt, {
		// path to task.js files
		configPath: path.join(process.cwd(), 'client/grunt'),
	});
};
