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
 * Version:           1.0.7
 * Author:            Manuel Ramirez Coronel
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Tested up to:      6.6
 * Requires Plugins: woocommerce
 * WC requires at least: 3.9
 * WC tested up to: 8.8
 * Author URI:        https://racmanuel.dev/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ar-model-viewer-for-woocommerce
 * Domain Path:       /languages
 *
 * @fs_premium_only /js/ar-model-viewer-for-woocommerce-admin-ctp.js, /js/ar-model-viewer-for-woocommerce-admin-ctp-dist.js, /css/ar-model-viewer-for-woocommerce-admin-ctp.css, /admin/class-ar-model-viewer-for-woocommerce-admin-ajax.php, /admin/class-ar-model-viewer-for-woocommerce-admin-custom-post-type.php
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Get the bootstrap!
 * (Update path to use cmb2 or CMB2, depending on the name of the folder.
 * Case-sensitive is important on some systems.)
 */
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

if (!function_exists('ar_model_viewer_for_woocommerce_fs')) {
    // Create a helper function for easy SDK access.
    function ar_model_viewer_for_woocommerce_fs()
    {
        global $ar_model_viewer_for_woocommerce_fs;

        if (!isset($ar_model_viewer_for_woocommerce_fs)) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/vendor/freemius/wordpress-sdk/start.php';

            $ar_model_viewer_for_woocommerce_fs = fs_dynamic_init(array(
                'id' => '16088',
                'slug' => 'ar-model-viewer-for-woocommerce',
                'type' => 'plugin',
                'public_key' => 'pk_5143076d3ed4661ac299aa66baabc',
                'is_premium' => true,
                'premium_suffix' => 'Pro',
                // If your plugin is a serviceware, set this option to false.
                'has_premium_version' => true,
                'has_addons' => false,
                'has_paid_plans' => true,
                'menu' => array(
                    'slug' => 'ar_model_viewer_for_woocommerce_settings',
                    'parent' => array(
                        'slug' => 'options-general.php',
                    ),
                ),
            ));
        }

        return $ar_model_viewer_for_woocommerce_fs;
    }

    // Init Freemius.
    ar_model_viewer_for_woocommerce_fs();
    // Signal that SDK was initiated.
    do_action('ar_model_viewer_for_woocommerce_fs_loaded');
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('AR_MODEL_VIEWER_FOR_WOOCOMMERCE_VERSION', '1.0.7');

/**
 * Define the Plugin basename
 */
define('AR_MODEL_VIEWER_FOR_WOOCOMMERCE_BASE_NAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 *
 * This action is documented in includes/class-ar-model-viewer-for-woocommerce-activator.php
 * Full security checks are performed inside the class.
 */
function ar_model_viewer_for_woocommerce_activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-ar-model-viewer-for-woocommerce-activator.php';
    Ar_Model_Viewer_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * This action is documented in includes/class-ar-model-viewer-for-woocommerce-deactivator.php
 * Full security checks are performed inside the class.
 */
function ar_model_viewer_for_woocommerce_deactivate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-ar-model-viewer-for-woocommerce-deactivator.php';
    Ar_Model_Viewer_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'ar_model_viewer_for_woocommerce_activate');
register_deactivation_hook(__FILE__, 'ar_model_viewer_for_woocommerce_deactivate');

// Not like register_uninstall_hook(), you do NOT have to use a static function.
ar_model_viewer_for_woocommerce_fs()->add_action('after_uninstall', 'ar_model_viewer_for_woocommerce_uninstall');

function ar_model_viewer_for_woocommerce_uninstall()
{

    if (!defined('WP_UNINSTALL_PLUGIN')
        || empty($_REQUEST)
        || !isset($_REQUEST['plugin'])
        || !isset($_REQUEST['action'])
        || 'ar-model-viewer-for-woocommerce/ar-model-viewer-for-woocommerce.php' !== $_REQUEST['plugin']
        || 'delete-plugin' !== $_REQUEST['action']
        || !check_ajax_referer('updates', '_ajax_nonce')
        || !current_user_can('activate_plugins')
    ) {
        exit;
    }
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-ar-model-viewer-for-woocommerce.php';

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
function ar_model_viewer_for_woocommerce_run()
{

    $plugin = new Ar_Model_Viewer_For_Woocommerce();
    $plugin->run();

}
ar_model_viewer_for_woocommerce_run();
