### AI Logger Client-side Source Files

This is the root directory of client-side source file assets. Grunt tasks will do the necessary operations to get them ready to be publically available in `static`, (aka `build` or `dist`). Available tasks are:

* `grunt watch`: Watch for changes to any source files and on change perform the related grunt tasks.
* `grunt build` or `grunt [default]`: Run all grunt operations on your source files.
* `grunt styles`: [Internal] Perform operations on the sass and sprite files, once.
* `grunt scripts`: [Internal] Perform operations on the javascript source, once.
* `grunt imagemin`: [Internal] Optimize images in the `static/images` dir, once.

This is meant to be a starting framework for grunt in your theme. You may extend the grunt tasks as necessary to break up javascript into chunks, use `grunt-hub` to work on sub-project Gruntfiles, optimize svg, etc.
