'use strict';
module.exports = function(grunt) {

    // load all grunt tasks matching the `grunt-*` pattern
    // Ref. https://npmjs.org/package/load-grunt-tasks
    require('load-grunt-tasks')(grunt);
    grunt.initConfig({

        // Check text domain
        checktextdomain: {
            options: {
                text_domain: ['ld-dashboard', 'wb-todo', 'reign-learndash-addon', 'acf'], // Specify allowed domain(s)
                keywords: [ // List keyword specifications
                    '__:1,2d',
                    '_e:1,2d',
                    '_x:1,2c,3d',
                    'esc_html__:1,2d',
                    'esc_html_e:1,2d',
                    'esc_html_x:1,2c,3d',
                    'esc_attr__:1,2d',
                    'esc_attr_e:1,2d',
                    'esc_attr_x:1,2c,3d',
                    '_ex:1,2c,3d',
                    '_n:1,2,4d',
                    '_nx:1,2,4c,5d',
                    '_n_noop:1,2,3d',
                    '_nx_noop:1,2,3c,4d'
                ]
            },
            target: {
                files: [{
                    src: [
                        '*.php',
                        '**/*.php',
                        '!node_modules/**',
                        '!options/framework/**',
                        '!tests/**',
                        '!includes/acf/**'
                    ], // all php
                    expand: true
                }]
            }
        },
        // rtlcss
        rtlcss: {
            myTask: {
                // task options
                options: {
                    // generate source maps
                    map: { inline: false },
                    // rtlcss options
                    opts: {
                        clean: false
                    },
                    // rtlcss plugins
                    plugins: [],
                    // save unmodified files
                    saveUnmodified: true
                },
                expand: true,
                cwd: 'public/css',
                dest: 'public/css-rtl',
                src: ['ld-dashboard-public.css']
            }
        },
    });

    // register task  'checktextdomain', 'makepot',
    grunt.registerTask('default', ['checktextdomain', 'rtlcss']);
};