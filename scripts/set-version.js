/**
 * Updates CBOXOL_PLUGIN_VER and CBOXOL_ASSET_VER constants in the main plugin file.
 * Replaces `grunt setPHPConstant`. Run via `npm run set-version`.
 */

const fs = require( 'fs' );
const path = require( 'path' );

const pluginFile = path.join( __dirname, '..', 'cbox-openlab-core.php' );
const { version } = require( '../package.json' );

const timestamp = Date.now();
const fullVersion = `${ version }-${ timestamp }`;

let content = fs.readFileSync( pluginFile, 'utf8' );

content = content.replace(
	/define\( 'CBOXOL_PLUGIN_VER', '[^']*' \);/,
	`define( 'CBOXOL_PLUGIN_VER', '${ fullVersion }' );`
);

content = content.replace(
	/define\( 'CBOXOL_ASSET_VER', '[^']*' \);/,
	`define( 'CBOXOL_ASSET_VER', '${ fullVersion }' );`
);

fs.writeFileSync( pluginFile, content );
console.log( `Version set to ${ fullVersion }` );
