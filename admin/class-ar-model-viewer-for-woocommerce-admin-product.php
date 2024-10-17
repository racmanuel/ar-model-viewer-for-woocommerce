<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality in product edit and new page of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the admin-facing stylesheet and JavaScript.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 * @author     Manuel Ramirez Coronel <ra_cm@outlook.com>
 */
class Ar_Model_Viewer_For_Woocommerce_Admin_Product
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The unique prefix of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
     */
    private $plugin_prefix;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The variable to call WooCommerce class logger.
     *
     * @since    1.0.0
     * @access   public
     * @var      object    $logger    The variable to call WooCommerce class logger.
     */
    private $logger;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of this plugin.
     * @param    string    $plugin_prefix  The unique prefix of this plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct($plugin_name, $plugin_prefix, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_prefix = $plugin_prefix;
        $this->version = $version;
        $this->logger = new Ar_Model_Viewer_For_Woocommerce_Logger($plugin_name, $plugin_prefix, $version);
    }

    /**
     * Handles the retrieval of 3D model settings and product details via AJAX request.
     *
     * This function is triggered by an AJAX request to get the 3D model settings and product details.
     * It verifies the validity of the product ID, retrieves global settings, and prepares the data for response.
     *
     * @since    1.0.0
     * @return   void
     */
    public function ar_model_viewer_for_woocommerce_get_model_and_settings()
    {
        // Verify if the AJAX request includes the product ID
        if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
            $this->logger->log_to_woocommerce('Invalid Product ID in AJAX request.', 'error'); // Log error
            wp_send_json_error('Invalid Product ID.');
            wp_die();
        }

        // Retrieve the product ID from the AJAX request
        $product_id = intval($_POST['product_id']); // Convert to integer
        if (!$product_id) {
            $this->logger->log_to_woocommerce('Invalid Product ID after intval conversion.', 'error'); // Log error
            wp_send_json_error('Invalid Product ID.');
            wp_die();
        }

        // Log successful retrieval of settings
        $this->logger->log_to_woocommerce('Global settings retrieved successfully.', 'info'); // Log info

        // Retrieve individual settings
        $settings = $this->get_ar_model_viewer_settings();

        // Get the product name
        $product_name = get_the_title($product_id);

        // Log product retrieval
        $this->logger->log_to_woocommerce("Product retrieved: $product_name (ID: $product_id)", 'info'); // Log info

        // Retrieve product metadata
        $model_alt = $this->get_model_alt_or_fallback($product_id);
        $model_poster = $this->get_model_poster_or_fallback($product_id);
        $model_3d_file = $this->get_model_3d_file_or_fallback($product_id);

        // Prepare data for response
        $data = array_merge($settings, [
            'product_name' => $product_name,
            'model_3d_file' => $model_3d_file,
            'model_alt' => $model_alt,
            'model_poster' => $model_poster,
        ]);

        // Send JSON response and log success
        $this->logger->log_to_woocommerce("Successfully prepared 3D model data for product: $product_name (ID: $product_id)", 'info'); // Log success
        wp_send_json_success($data);
        wp_die();
    }

    /**
     * Retrieves AR model viewer settings from the options page.
     *
     * This function gets the AR model viewer settings configured in the options page using CMB2.
     *
     * @since    1.0.0
     * @return   array    An array of AR model viewer settings.
     */
    private function get_ar_model_viewer_settings()
    {
        return [
            // Determines how the model loads (e.g., "auto" means it starts loading immediately)
            'loading' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_loading', 'auto'),

            // Controls when the model should be revealed, such as after a specific action or automatically
            'reveal' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_reveal', 'auto'),

            // Indicates whether the model viewer should send credentials such as cookies when making network requests
            'with_credentials' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_with_credentials', 'false'),

            // Sets the color of the poster background for the model viewer (default is transparent white)
            'poster_color' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_poster_color', 'rgba(255,255,255,0)'),

            // Determines if the AR feature should be enabled or not (e.g., "active" enables AR functionality)
            'ar' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar', 'active'),

            // Defines the scaling behavior of the model in AR (e.g., "auto" lets the viewer decide the best scale)
            'scale' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_scale', 'auto'),

            // Sets the placement of the model in AR (e.g., "floor" means the model is placed on the floor)
            'placement' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_placement', 'floor'),

            // Indicates whether an XR (Extended Reality) environment is used (e.g., "deactive" means no XR environment)
            'xr_environment' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_xr_environment', 'deactive'),

            // Defines which AR modes are supported, such as "webxr" or "scene-viewer" (these determine how the AR experience is presented)
            'ar_modes' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_modes', ['webxr', 'scene-viewer']),
        ];
    }

    /**
     * Retrieves the alt text for the 3D model or falls back to the product name or short description.
     *
     * This function checks if the 3D model alt text is set. If not, it will try to return the product name.
     * If the product name is also not set, it will return the short description.
     * If none of these fields are available, it will log an error using the WooCommerce logger.
     *
     * @since    1.0.0
     * @param    int    $product_id    The product ID.
     * @return   string The alt text for the 3D model, or the product name, or the short description.
     */
    private function get_model_alt_or_fallback($product_id)
    {
        // Retrieve the WooCommerce product object
        $product = wc_get_product($product_id);

        // If the product doesn't exist, log an error and return an empty string
        if (!$product) {
            $this->logger->log_to_woocommerce('Product not found for ID ' . $product_id, 'error'); // Log error
            return '';
        }

        // Get the 3D model alt text using WooCommerce's get_meta function
        $model_alt = $product->get_meta('ar_model_viewer_for_woocommerce_file_alt', true);

        // If the 3D model alt text is not available, fallback to the product name
        if (empty($model_alt)) {
            $product_name = $product->get_name(); // Use WooCommerce's native function to get the product name

            // If the product name is also not available, fallback to the short description
            if (empty($product_name)) {
                $short_description = $product->get_short_description(); // Use WooCommerce's native function to get the short description

                // If the short description is not available, log an error and return an empty string
                if (empty($short_description)) {
                    $this->logger->log_to_woocommerce('No alt, product name, or short description found for product ID ' . $product_id, 'error'); // Log error
                    return ''; // Return empty string as fallback
                }

                // Return the short description if available
                return $short_description;
            }

            // Return the product name if available
            return $product_name;
        }

        // Return the 3D model alt text if it exists
        return $model_alt;
    }

    /**
     * Retrieves the 3D model poster URL or falls back to the product's main image URL.
     *
     * This function checks if the 3D model poster URL is set. If not, it will try to return the product's main image URL.
     * If the product doesn't have a main image, it will log an error using the WooCommerce logger.
     *
     * @since    1.0.0
     * @param    int    $product_id    The product ID.
     * @return   string The URL of the poster image or the product's main image.
     */
    private function get_model_poster_or_fallback($product_id)
    {
        // Retrieve the WooCommerce product object
        $product = wc_get_product($product_id);

        // If the product doesn't exist, log an error and return an empty string
        if (!$product) {
            $this->logger->log_to_woocommerce('Product not found for ID ' . $product_id, 'error');
            return '';
        }

        // Get the 3D model poster URL using WooCommerce's get_meta function
        $model_poster = $product->get_meta('ar_model_viewer_for_woocommerce_file_poster', true);

        // If the 3D model poster URL is empty or null, try to get the product's main image URL
        if (empty($model_poster)) {
            $main_image_id = $product->get_image_id(); // Get the ID of the main image

            // If the product has no main image, log an error and return empty
            if (empty($main_image_id)) {
                $this->logger->log_to_woocommerce('No poster or main image found for product ID ' . $product_id, 'error');
                return ''; // Return empty string as fallback
            }

            // Get the URL of the main image
            $main_image_url = wp_get_attachment_url($main_image_id);

            // Return the main image URL if available
            if ($main_image_url) {
                return $main_image_url;
            } else {
                $this->logger->log_to_woocommerce('Main image URL could not be retrieved for product ID ' . $product_id, 'error');
                return ''; // Return empty if no URL could be retrieved
            }
        }

        // Return the 3D model poster URL if it exists
        return $model_poster;
    }

    /**
     * Retrieves the 3D model file for the product or sets a fallback file from the plugin's includes directory.
     *
     * This function checks if the 3D model file is set for the product. If not, it will use a default 3D model
     * file located in the includes folder of the plugin. It also logs an error if the model is missing.
     *
     * @since    1.0.0
     * @param    int    $product_id    The product ID.
     * @return   string The URL of the 3D model file.
     */
    public function get_model_3d_file_or_fallback($product_id)
    {
        // Retrieve the WooCommerce product object
        $product = wc_get_product($product_id);

        // If the product doesn't exist, log an error and return an empty string
        if (!$product) {
            $this->logger->log_to_woocommerce("Product not found for ID $product_id", 'error');
            return '';
        }

        // Get the 3D model file using WooCommerce's get_meta function
        $model_3d_file = $product->get_meta('ar_model_viewer_for_woocommerce_file_object', true);

        // Check if the 3D model file exists
        if (!$model_3d_file) {
            $this->logger->log_to_woocommerce("3D model file missing for product: {$product->get_name()} (ID: $product_id)", 'error'); // Log error
            wp_send_json_error('3D model file missing for product. Try save the product before view a preview.');
            wp_die();
        }

        // Log success if the 3D model file is found
        $this->logger->log_to_woocommerce("3D model file found for product: {$product->get_name()} (ID: $product_id)", 'info'); // Log info

        // Return the 3D model file URL
        return $model_3d_file;
    }
}
