module.exports = function( grunt ) {

	'use strict';
	var banner = '/**\n * <%= pkg.homepage %>\n * Copyright (c) <%= grunt.template.today("yyyy") %>\n * This file is generated automatically. Do not edit.\n */\n';
	// Project configuration

	var timestamp = new Date().getTime();

	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		addtextdomain: {
			options: {
				textdomain: 'commons-in-a-box',
			},
			target: {
				files: {
					src: [ '*.php', '**/*.php', '!node_modules/**', '!php-tests/**', '!bin/**' ]
				}
			}
		},

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					mainFile: 'cbox-openlab-core.php',
					potFilename: 'cbox-openlab-core.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: true
				}
			}
		},

        less: {
            development: {
                options: {
                    compress: true,
                    optimization: 2
                },
                files: {
                    "assets/css/openlab-toolbar.css": "assets/css/openlab-toolbar.less"
                }
            }
        },

        watch: {
            styles: {
                files: ['assets/css/*.less'],
                tasks: ['less'],
                options: {
                    nospawn: true
                }
            },
		},
		setPHPConstant: {
			assetVersion: {
				constant: 'CBOXOL_ASSET_VER',
				value: '<%= pkg.version %>-' + timestamp,
				file: 'cbox-openlab-core.php'
			},
			pluginVersion: {
				constant: 'CBOXOL_PLUGIN_VER',
				value: '<%= pkg.version %>-' + timestamp,
				file: 'cbox-openlab-core.php'
			}
		}
	} );

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks( 'grunt-contrib-less' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );
    grunt.loadNpmTasks('grunt-php-set-constant');

	grunt.registerTask( 'i18n', ['addtextdomain', 'makepot'] );
//	grunt.registerTask( 'setPHPConstant' );

	grunt.util.linefeed = '\n';

};
