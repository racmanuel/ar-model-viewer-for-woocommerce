<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress or ClassicPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://racmanuel.dev
 * @since             1.0.0
 * @package           Ar_Model_Viewer_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       AR Model Viewer for WooCommerce
 * Plugin URI:        https://plugin.com/ar-model-viewer-for-woocommerce-uri/
 * Description:       AR Model Viewer for WooCommerce plugin is an all in one solution to allow you to present your 3D models in an interactive AR view directly in your browser on both iOS and Android devices and all the products you have a 3D model, this plugin support formats .glb
 * Version:           1.0.0
 * Author:            Manuel Ramirez Coronel
 * Requires at least: 1.0.0
 * Requires PHP:      7.4
 * Tested up to:      6.0
 * Author URI:        https://racmanuel.dev/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ar-model-viewer-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AR_MODEL_VIEWER_FOR_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * Define the Plugin basename
 */
define( 'AR_MODEL_VIEWER_FOR_WOOCOMMERCE_BASE_NAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 *
 * This action is documented in includes/class-ar-model-viewer-for-woocommerce-activator.php
 * Full security checks are performed inside the class.
 */
function ar_model_viewer_for_woocommerce_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ar-model-viewer-for-woocommerce-activator.php';
	Ar_Model_Viewer_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * This action is documented in includes/class-ar-model-viewer-for-woocommerce-deactivator.php
 * Full security checks are performed inside the class.
 */
function ar_model_viewer_for_woocommerce_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ar-model-viewer-for-woocommerce-deactivator.php';
	Ar_Model_Viewer_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'ar_model_viewer_for_woocommerce_activate' );
register_deactivation_hook( __FILE__, 'ar_model_viewer_for_woocommerce_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ar-model-viewer-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Generally you will want to hook this function, instead of callign it globally.
 * However since the purpose of your plugin is not known until you write it, we include the function globally.
 *
 * @since    1.0.0
 */
function ar_model_viewer_for_woocommerce_run() {

	$plugin = new Ar_Model_Viewer_For_Woocommerce();
	$plugin->run();

}
ar_model_viewer_for_woocommerce_run();
