module.exports = function( grunt ) {

	'use strict';
	// Project configuration

	var timestamp = new Date().getTime();

	grunt.initConfig(
		{

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
		}
	);

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-php-set-constant' );

	grunt.registerTask( 'i18n', ['addtextdomain', 'makepot'] );
	//  grunt.registerTask( 'setPHPConstant' );

	grunt.util.linefeed = '\n';

};
