/**
 * Copies the prebuilt browser files of the third party libraries into `assets/vendor`.
 *
 * The libraries already ship files that are ready to use with a plain `<script>` tag, so
 * the plugin needs no bundler at all: this script only copies them, and merges their
 * stylesheets into a single `vendor.css`.
 *
 * Run it after installing or updating the dependencies:
 *
 *   npm run vendors
 *
 * @link    https://racmanuel.dev
 * @since   3.0.0
 */

import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';

/**
 * Directory the files are copied to, relative to the plugin root.
 *
 * @type {string}
 */
const target = 'assets/vendor';

/**
 * Scripts copied as they are: [source, file name].
 *
 * The UMD builds are used on purpose: they are plain scripts that publish their library as
 * a global, which is what `wp_enqueue_script` and the on demand loader expect. The ESM
 * builds (`dist/model-viewer.min.js`, for example) cannot be loaded that way.
 *
 * @type {Array<Array<string>>}
 */
const scripts = [
	['node_modules/@google/model-viewer/dist/model-viewer-umd.min.js', 'model-viewer.min.js'],
	['node_modules/alertifyjs/build/alertify.min.js', 'alertify.min.js'],
	['node_modules/driver.js/dist/driver.js.iife.js', 'driver.min.js'],
	['node_modules/tabulator-tables/dist/js/tabulator.min.js', 'tabulator.min.js'],
];

/**
 * Stylesheets merged into `vendor.css`.
 *
 * The tasks table is not included: the plugin styles it with its own theme.
 *
 * @type {Array<string>}
 */
const styles = [
	'node_modules/alertifyjs/build/css/alertify.min.css',
	'node_modules/alertifyjs/build/css/themes/default.min.css',
	'node_modules/driver.js/dist/driver.css',
];

mkdirSync(target, { recursive: true });

scripts.forEach(([source, name]) => {
	// Read and write instead of copying: `copyFileSync` keeps the modification time of the
	// source, and WordPress builds the cache busting version of an asset from that time. A
	// stale timestamp would keep serving the previous file from the browser cache.
	writeFileSync(`${target}/${name}`, readFileSync(source));
	console.log(`${name} <- ${source}`);
});

// `@charset` is only valid at the very beginning of a stylesheet, so it is stripped
// before merging the files.
const css = styles.map((file) => readFileSync(file, 'utf8').replace(/@charset[^;]+;/gi, '')).join('\n');

writeFileSync(`${target}/vendor.css`, css);
console.log(`vendor.css <- ${styles.length} stylesheets`);
