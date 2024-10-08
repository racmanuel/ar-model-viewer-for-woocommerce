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
        $this->logger = new Ar_Model_Viewer_For_Woocommerce_Logger($this->plugin_name, $plugin_prefix, $version);
    }

    /**
     * Handles the [ar-model-viewer-for-woocommerce-shortcode] shortcode.
     *
     * Shortcode usage: [ar-model-viewer-for-woocommerce-shortcode id='123']
     * Enclosing content: [ar-model-viewer-for-woocommerce-shortcode id='123']custom content[/ar-model-viewer-for-woocommerce-shortcode]
     *
     * @since 1.0.0
     * @param array  $atts    Shortcode attributes.
     * @param string $content Shortcode enclosed content.
     * @return string HTML output of the model viewer.
     */
    public function ar_model_viewer_for_woocommerce_shortcode_func($atts, $content = null)
    {
        // Combine user attributes with known attributes.
        $atts = shortcode_atts(
            array(
                'id' => 0, // Default to 0 if no ID is passed.
            ),
            $atts,
            $this->plugin_prefix . 'shortcode'
        );

        // Sanitize and validate the 'id' attribute.
        $product_id = absint($atts['id']);

        if (empty($product_id)) {
            $this->logger->log_to_woocommerce('Invalid product ID provided in shortcode.', 'error');
            return esc_html__('Invalid product ID.', 'ar-model-viewer-for-woocommerce');
        }

        // Get the product object.
        $product = wc_get_product($product_id);

        if (!$product) {
            $this->logger->log_to_woocommerce("Product with ID {$product_id} not found.", 'error');
            return esc_html__('Product not found.', 'ar-model-viewer-for-woocommerce');
        }

        // Retrieve the 3D model file from the product's custom field.
        $file_object = $product->get_meta('ar_model_viewer_for_woocommerce_file_object', true);

        if (empty($file_object)) {
            $this->logger->log_to_woocommerce("No 3D model file found for product ID {$product_id}.", 'warning');
            return esc_html__('No 3D Model File found.', 'ar-model-viewer-for-woocommerce');
        }

        // Sanitize the retrieved fields.
        $file_object = esc_url_raw($file_object);
        $poster = esc_url_raw($product->get_meta('ar_model_viewer_for_woocommerce_file_poster'));
        $alt_text = sanitize_text_field($product->get_meta('ar_model_viewer_for_woocommerce_file_alt'));

        // Retrieve options from CMB2.
        $loading = sanitize_text_field(cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_loading'));
        $reveal = sanitize_text_field(cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_reveal'));
        $ar_enabled = sanitize_text_field(cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar'));
        $ar_modes = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_modes');
        $ar_scale = sanitize_text_field(cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_scale'));
        $ar_placement = sanitize_text_field(cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_placement'));
        $background_color = sanitize_hex_color(cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_poster_color'));

        $ar_modes = is_array($ar_modes) ? array_map('sanitize_text_field', $ar_modes) : array();

        // Set default values if fields are empty.
        $alt_text = $alt_text ? $alt_text : $product->get_name();
        $loading = $loading ? $loading : 'auto';
        $reveal = $reveal ? $reveal : 'auto';
        $background_color = $background_color ? $background_color : '#FFFFFF';
        $ar_enabled = ('activate' === $ar_enabled);

        // Validate the $ar_scale value.
        $allowed_ar_scales = array('auto', 'fixed');
        if (!in_array($ar_scale, $allowed_ar_scales, true)) {
            $ar_scale = 'auto'; // Default value
        }

        // Prepare AR attributes if enabled.
        $ar_attributes = '';
        if ($ar_enabled) {
            $ar_modes_attr = $ar_modes ? 'ar-modes="' . esc_attr(implode(' ', $ar_modes)) . '"' : '';
            $ar_placement_attr = $ar_placement ? 'ar-placement="' . esc_attr($ar_placement) . '"' : '';
            $ar_scale_attr = 'ar-scale="' . esc_attr($ar_scale) . '"';
            $ar_attributes = 'ar ' . $ar_modes_attr . ' ' . $ar_placement_attr . ' ' . $ar_scale_attr;
        }

        // Build the model-viewer element.
        $output = sprintf(
            '<model-viewer src="%1$s" alt="%2$s" poster="%3$s" loading="%4$s" reveal="%5$s" style="background-color: %6$s;" camera-controls auto-rotate %7$s></model-viewer>',
            esc_url($file_object),
            esc_attr($alt_text),
            esc_url($poster),
            esc_attr($loading),
            esc_attr($reveal),
            esc_attr($background_color),
            $ar_attributes
        );

        // Log successful shortcode rendering.
        $this->logger->log_to_woocommerce("Model viewer shortcode rendered for product ID {$product_id}.", 'info');

        // Process enclosed content if any.
        if (!empty($content)) {
            $content = do_shortcode($content);
            $output .= '<div class="shortcode-content">' . wp_kses_post($content) . '</div>';
        }

        /**
         * Filter the output of the model viewer shortcode.
         *
         * @since 1.0.0
         *
         * @param string $output  The HTML output.
         * @param array  $atts    The shortcode attributes.
         * @param string $content The shortcode content.
         */
        return apply_filters('ar_model_viewer_for_woocommerce_shortcode_output', $output, $atts, $content);
    }
}
