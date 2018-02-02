module.exports = function(grunt) {
    // require("matchdep").filterDev("grunt-*").forEach(grunt.loadNpmTasks);
    // var moodleroot = path.dirname(path.dirname(__dirname)); // jshint ignore:line
    var path = require('path'),
        fs = require('fs'),
        tasks = {},
        cwd = process.env.PWD || process.cwd();

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        jshint: {
            options: {jshintrc: '../../.jshintrc'},
            files: ['**/amd/src/*.js']
        },
        uglify: {
            // dynamic_mappings: {
            //     files: grunt.file.expandMapping(
            //         ['**/src/*.js', '!**/node_modules/**'],
            //         '',
            //         {
            //             cwd: cwd,
            //             rename: function(destBase, destPath) {
            //                 destPath = destPath.replace('src', 'build');
            //                 destPath = destPath.replace('.js', '.min.js');
            //                 destPath = path.resolve(cwd, destPath);
            //                 return destPath;
            //             }
            //         }
            //     )
            // }
            my_target: {
            	files: {
            		'amd/build/rollovertable.min.js': ['amd/src/rollovertable.js'],
            		'amd/build/defaultoptions.min.js': ['amd/src/defaultoptions.js'],
            		// 'amd/build/x-editable.min.js': ['amd/src/x-editable.js'],
                    'amd/build/selectcourses.min.js': ['amd/src/selectcourses.js'],
            		'amd/build/reviewsettings.min.js': ['amd/src/reviewsettings.js']
            	}
            }
        }
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');

    // Default task(s).
    grunt.registerTask('default', ['uglify']);
    grunt.registerTask("amd", ["jshint", "uglify"]);
};