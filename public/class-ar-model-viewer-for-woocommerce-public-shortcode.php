<?php
/**
 * The public-facing shortcode functionality of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public
 */

/**
 * The public-facing shortcode functionality of the plugin.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public
 * @author
 */
class Ar_Model_Viewer_For_Woocommerce_Public_Shortcode
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
     * The logger of WooCommerce.
     *
     * @since 1.0.0
     * @var string $version The current version of this plugin.
     */
    private $logger;

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
        $this->logger = new Ar_Model_Viewer_For_Woocommerce_Logger($plugin_name, $plugin_prefix, $version);
    }

    /**
     * Handles the [ar-model-viewer-for-woocommerce-shortcode] shortcode.
     *
     * Shortcode usage: [ar-model-viewer-for-woocommerce-shortcode id='123']
     * Enclosing content: [ar-model-viewer-for-woocommerce-shortcode id='123']custom content[/ar-model-viewer-for-woocommerce-shortcode]
     *
     * @since 1.0.0
     *
     * @param array  $atts    Shortcode attributes.
     *  - 'id' (int) ID of the WooCommerce product. Default is 0.
     *
     * @param string|null $content Shortcode enclosed content (optional).
     *
     * @return string HTML output of the model viewer or an error message if the product is invalid.
     */
    public function ar_model_viewer_for_woocommerce_shortcode_func($atts, $content = null)
    {
        // Merge user-provided attributes with defaults (default ID is 0).
        $atts = shortcode_atts(
            array(
                'id' => 0, // Default to 0 if no ID is passed.
            ),
            $atts,
            $this->plugin_prefix . 'shortcode'
        );

        // Sanitize and validate the 'id' attribute.
        $product_id = absint($atts['id']);

        // Check if the product ID is valid, if not log an error and return a message.
        if (empty($product_id)) {
            $this->logger->log_to_woocommerce('Invalid product ID provided in shortcode.', 'error');
            return esc_html__('Invalid product ID.', 'ar-model-viewer-for-woocommerce');
        }

        // Retrieve the product object by its ID.
        $product = wc_get_product($product_id);

        // If the product doesn't exist, log an error and return a message.
        if (!$product) {
            $this->logger->log_to_woocommerce("Product with ID {$product_id} not found.", 'error');
            return esc_html__('Product not found.', 'ar-model-viewer-for-woocommerce');
        }

        // Retrieve the 3D model file or fallback.
        $file_object = $this->get_model_3d_file_or_fallback($product);

        // Retrieve the poster image for the 3D model or fallback.
        $file_poster = $this->get_model_poster_or_fallback($product);

        // Retrieve the alt text for the 3D model or fallback.
        $file_alt = $this->get_model_alt_or_fallback($product);

        // Retrieve the AR settings from the plugin's options.
        $settings = $this->get_ar_model_viewer_settings();

        // Initialize AR attributes based on the retrieved settings.
        $ar_attributes = '';
        if ($settings['ar'] === 'active') {
            $ar_attributes .= 'ar ar-modes="' . esc_attr(implode(' ', $settings['ar_modes'])) . '" ';
            if ($settings['scale']) {
                $ar_attributes .= 'ar-scale="' . esc_attr($settings['scale']) . '" ';
            }
            if ($settings['placement']) {
                $ar_attributes .= 'ar-placement="' . esc_attr($settings['placement']) . '" ';
            }
            if ($settings['xr_environment'] === 'active') {
                $ar_attributes .= 'xr-environment ';
            }
        }

        // Generate the HTML for the model-viewer element with all attributes and settings.
        $output = sprintf(
            '<model-viewer src="%1$s" alt="%2$s" poster="%3$s" loading="%4$s" reveal="%5$s" style="background-color: %6$s;" camera-controls auto-rotate %7$s></model-viewer>',
            esc_url($file_object),
            esc_attr($file_alt),
            esc_url($file_poster),
            esc_attr($settings['loading']),
            esc_attr($settings['reveal']),
            esc_attr($settings['poster_color']),
            $ar_attributes
        );

        /**
         * Filters the output of the model viewer shortcode.
         *
         * @since 1.0.0
         *
         * @param string $output  The HTML output generated by the shortcode.
         * @param array  $atts    The shortcode attributes.
         * @param string $content The shortcode enclosed content.
         */
        return apply_filters('ar_model_viewer_for_woocommerce_shortcode_output', $output, $atts, $content);
    }

    /**
     * Retrieves the 3D model file for the product or sets a fallback file from the plugin's includes directory.
     *
     * This function checks if the 3D model file is set for the product. If not, it will log an error and
     * terminate the execution. The function will attempt to provide a helpful error message for the user.
     *
     * @since 1.0.0
     *
     * @param WC_Product $product The WooCommerce product object.
     *
     * @return string The URL of the 3D model file.
     */
    private function get_model_3d_file_or_fallback($product)
    {
        // Retrieve the 3D model file from the product's metadata using WooCommerce's get_meta function.
        $model_3d_file = $product->get_meta('ar_model_viewer_for_woocommerce_file_object', true);

        // Check if the 3D model file exists. If not, log an error and terminate the request.
        if (!$model_3d_file) {
            // Log an error indicating that the 3D model file is missing.
            $this->logger->log_to_woocommerce(
                sprintf('3D model file missing for product: %s (ID: %d)', $product->get_name(), $product->get_id()),
                'error'
            );
            // Send a JSON error response and stop further execution.
            wp_send_json_error('3D model file missing for product. Please save the product before attempting to preview.');
            wp_die(); // End script execution to prevent further errors.
        }

        // If the 3D model file is found, log success.
        $this->logger->log_to_woocommerce(
            sprintf('3D model file found for product: %s (ID: %d) (SKU: %s)', $product->get_name(), $product->get_id(), $product->get_sku()),
            'info'
        );

        // Return the URL of the 3D model file.
        return $model_3d_file;
    }

    /**
     * Retrieves the 3D model poster URL or falls back to the product's main image URL.
     *
     * This function checks if the 3D model poster URL is set for the product. If not, it attempts to
     * retrieve the main image URL of the product. In case the product has no main image, it logs an
     * error and returns an empty string.
     *
     * @since 1.0.0
     *
     * @param WC_Product $product The WooCommerce product object.
     *
     * @return string The URL of the 3D model poster or the product's main image. If neither is available, an empty string is returned.
     */
    private function get_model_poster_or_fallback($product)
    {
        // Retrieve the 3D model poster URL from the product's metadata.
        $model_poster = $product->get_meta('ar_model_viewer_for_woocommerce_file_poster', true);

        // If the 3D model poster URL is empty, attempt to retrieve the main product image.
        if (empty($model_poster)) {
            // Get the ID of the main image from the product.
            $main_image_id = $product->get_image_id();

            // If the product has no main image, log an error and return an empty string.
            if (empty($main_image_id)) {
                $this->logger->log_to_woocommerce(
                    sprintf('No poster or main image found for product ID %d', $product->get_id()),
                    'error'
                );
                return ''; // Return an empty string as a fallback.
            }

            // Retrieve the URL of the main image.
            $main_image_url = wp_get_attachment_url($main_image_id);

            // Return the main image URL if available. Otherwise, log an error and return an empty string.
            if ($main_image_url) {
                return $main_image_url;
            } else {
                $this->logger->log_to_woocommerce(
                    sprintf('Main image URL could not be retrieved for product ID %d', $product->get_id()),
                    'error'
                );
                return ''; // Return an empty string if the URL could not be retrieved.
            }
        }

        // If the 3D model poster URL exists, return it.
        return $model_poster;
    }

    /**
     * Retrieves AR model viewer settings from the options page.
     *
     * This function fetches the AR model viewer settings configured in the WordPress options page using the CMB2 framework.
     * It retrieves settings related to how the AR model is loaded, displayed, and whether AR functionality is enabled.
     *
     * @since 1.0.0
     *
     * @return array An associative array containing AR model viewer settings such as loading behavior, reveal method, AR modes, and more.
     */
    private function get_ar_model_viewer_settings()
    {
        return [
            // Determines how the model loads, e.g., "auto" starts loading immediately.
            'loading' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_loading', 'auto'),

            // Controls when the model should be revealed, either automatically or after a specific action.
            'reveal' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_reveal', 'auto'),

            // Specifies whether the model viewer should send credentials such as cookies during network requests.
            'with_credentials' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_with_credentials', 'false'),

            // Sets the background color of the model poster, default is transparent white.
            'poster_color' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_poster_color', 'rgba(255,255,255,0)'),

            // Indicates if the AR functionality is enabled (e.g., "active" enables AR features).
            'ar' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar', 'active'),

            // Defines which AR modes are supported, such as "webxr", "scene-viewer", or "quick-look".
            'ar_modes' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_modes', ['webxr', 'scene-viewer', 'quick-look']),

            // Defines the scaling behavior of the model in AR, e.g., "auto" allows the viewer to choose the best scale.
            'scale' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_scale', 'auto'),

            // Sets where the model is placed in AR, e.g., "floor" places the model on the ground.
            'placement' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_placement', 'floor'),

            // Indicates whether an XR (Extended Reality) environment is enabled, e.g., "active" enables XR.
            'xr_environment' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_xr_environment', 'active'),
        ];
    }

    /**
     * Retrieves the alt text for the 3D model or falls back to the product name or short description.
     *
     * This function checks if the 3D model alt text is set in the product metadata. If it is not set,
     * the function will attempt to return the product name. If the product name is also not available,
     * it will return the short description. If none of these fields are available, an error will be
     * logged using the WooCommerce logger, and an empty string will be returned as a fallback.
     *
     * @since 1.0.0
     *
     * @param WC_Product $product The WooCommerce product object.
     *
     * @return string The alt text for the 3D model, or the product name, or the short description. If none are available, it returns an empty string.
     */
    private function get_model_alt_or_fallback($product)
    {
        // Retrieve the 3D model alt text from the product's metadata.
        $model_alt = $product->get_meta('ar_model_viewer_for_woocommerce_file_alt', true);

        // If the 3D model alt text is not available, fallback to the product name.
        if (empty($model_alt)) {
            // Use WooCommerce's native function to get the product name.
            $product_name = $product->get_name();

            // If the product name is not available, fallback to the short description.
            if (empty($product_name)) {
                // Use WooCommerce's native function to get the short description.
                $short_description = $product->get_short_description();

                // If the short description is not available, log an error and return an empty string.
                if (empty($short_description)) {
                    $this->logger->log_to_woocommerce(
                        sprintf('No alt, product name, or short description found for product ID %d', $product->get_id()),
                        'error'
                    );
                    return ''; // Return empty string as a fallback.
                }

                // Return the short description if available.
                return $short_description;
            }

            // Return the product name if available.
            return $product_name;
        }

        // Return the 3D model alt text if it exists.
        return $model_alt;
    }
}
