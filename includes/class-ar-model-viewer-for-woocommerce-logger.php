<?php
/**
 * The logger functionality of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public
 */

/**
 * The logger functionality of the plugin.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public
 * @author
 */
class Ar_Model_Viewer_For_Woocommerce_Logger
{
    /**
     * The ID of this plugin.
     *
     * @since 1.0.0
     * @var string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The unique prefix of this plugin.
     *
     * @since 1.0.0
     * @var string $plugin_prefix The string used to uniquely prefix technical functions of this plugin.
     */
    private $plugin_prefix;

    /**
     * The version of this plugin.
     *
     * @since 1.0.0
     * @var string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $plugin_name   The name of the plugin.
     * @param string $plugin_prefix The unique prefix of this plugin.
     * @param string $version       The version of this plugin.
     */
    public function __construct($plugin_name, $plugin_prefix, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_prefix = $plugin_prefix;
        $this->version = $version;
    }

    /**
     * Logs messages to WooCommerce system with varying levels of severity.
     *
     * This function checks if logging is enabled in the plugin's settings and, if so,
     * logs messages using WooCommerce's logging system based on the specified level.
     *
     * @param string $message The message to be logged.
     * @param string $level   The severity level of the log. Default is 'info'.
     */
    public function log_to_woocommerce($message, $level = 'info')
    {
        // Retrieve the logging setting from the plugin options using CMB2 library.
        $log_active = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_logger');

        // Check if logging is enabled in the plugin's settings.
        if (!$log_active) {
            // If logging is not enabled, exit the function without logging.
            return;
        }

        // Obtain an instance of WooCommerce's WC_Logger class.
        $logger = wc_get_logger();

        // Define the logging context, specifically indicating the source of the log.
        // Replace 'ar-model-viewer-for-woocommerce' with the actual source name or plugin name.
        $context = array('source' => 'ar-model-viewer-for-woocommerce');

        // Log the message at the specified severity level.
        switch ($level) {
            case 'emergency':
                $logger->emergency($message, $context);
                break;
            case 'alert':
                $logger->alert($message, $context);
                break;
            case 'critical':
                $logger->critical($message, $context);
                break;
            case 'error':
                $logger->error($message, $context);
                break;
            case 'warning':
                $logger->warning($message, $context);
                break;
            case 'notice':
                $logger->notice($message, $context);
                break;
            case 'info':
                // The default logging level is 'info'.
                $logger->info($message, $context);
                break;
            case 'debug':
                $logger->debug($message, $context);
                break;
            default:
                // If an unrecognized level is specified, default to 'info' level.
                $logger->info($message, $context);
        }
    }
}
