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
     * Handles the [ar_model_viewer_for_woocommerce_shortcode] shortcode.
     *
     * Shortcode usage: [ar_model_viewer_for_woocommerce_shortcode id='123']
     * Enclosing content: [ar_model_viewer_for_woocommerce_shortcode id='123']custom content[/ar_model_viewer_for_woocommerce_shortcode]
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

        // A product whose viewer is switched off renders nothing at all, which is what the store
        // asked for when it cleared the switch.
        if (!Ar_Model_Viewer_For_Woocommerce_Product_Model::is_enabled($product)) {
            return '';
        }

        // The model of the product, with the poster and the alt text already defaulted. This
        // resolution used to live in three separated copies, and the endpoint used by the modal
        // was the one that forgot to apply the fallbacks.
        $model = Ar_Model_Viewer_For_Woocommerce_Product_Model::resolve($product);

        if ('' === trim($model['source'])) {
            $this->logger->log_to_woocommerce(
                sprintf('3D model file missing for product: %s (ID: %d)', $product->get_name(), $product->get_id()),
                'error'
            );

            return esc_html__('This product has no 3D model yet.', 'ar-model-viewer-for-woocommerce');
        }

        $this->logger->log_to_woocommerce(
            sprintf('3D model file found for product: %s (ID: %d) (SKU: %s)', $product->get_name(), $product->get_id(), $product->get_sku()),
            'info'
        );

        // Retrieve the AR settings, with the overrides of this product already applied.
        $settings = $this->get_ar_model_viewer_settings($product);

        // Initialize AR attributes based on the retrieved settings.
        $ar_attributes = '';
        if ($settings['ar']) {
            $ar_attributes .= 'ar ';

            // An empty mode list would be rendered as `ar-modes=""`, which the library cannot
            // parse, so the attribute is omitted and the viewer keeps its own default list.
            if (!empty($settings['ar_modes'])) {
                $ar_attributes .= 'ar-modes="' . esc_attr(implode(' ', $settings['ar_modes'])) . '" ';
            }

            if ($settings['scale']) {
                $ar_attributes .= 'ar-scale="' . esc_attr($settings['scale']) . '" ';
            }
            if ($settings['placement']) {
                $ar_attributes .= 'ar-placement="' . esc_attr($settings['placement']) . '" ';
            }
            if ($settings['xr_environment']) {
                $ar_attributes .= 'xr-environment ';
            }
        }

        // The browser sends cookies and authorization headers when the model is fetched from
        // a server that requires authentication.
        $extra_attributes = '';

        if ($settings['with_credentials']) {
            $extra_attributes .= 'with-credentials ';
        }

        // The custom AR button replaces the default "Enter AR" icon of the viewer. It only
        // makes sense when AR is enabled and the button has a label.
        $ar_button = '';

        if ($settings['ar'] && $settings['ar_button'] && '' !== trim((string) $settings['ar_button_text'])) {
            $ar_button = sprintf(
                '<button slot="ar-button" style="background-color:%1$s;color:%2$s;border:none;border-radius:999px;padding:8px 14px;cursor:pointer;">%3$s</button>',
                esc_attr($settings['ar_button_background_color']),
                esc_attr($settings['ar_button_text_color']),
                esc_html($settings['ar_button_text'])
            );
        }

        // Generate the HTML for the model-viewer element with all attributes and settings.
        $output = sprintf(
            '<model-viewer class="armvw-viewer" data-product-id="%1$d" src="%2$s" alt="%3$s" poster="%4$s" loading="%5$s" reveal="%6$s" style="background-color: %7$s;" %8$s%9$s%10$s%11$s>%12$s</model-viewer>',
            $product_id,
            esc_url($model['source']),
            esc_attr($model['alt']),
            esc_url($model['poster']),
            esc_attr($settings['loading']),
            esc_attr($settings['reveal']),
            esc_attr($settings['poster_color']),
            $ar_attributes,
            $extra_attributes,
            // Lighting and appearance attributes are shared by every placement, so they are
            // built in one place and appended here instead of being repeated per template. The
            // product switches are merged into that shared list instead of being appended, so an
            // override never leaves a duplicated attribute behind.
            Ar_Model_Viewer_For_Woocommerce_Settings::render_attributes(
                Ar_Model_Viewer_For_Woocommerce_Product_Model::merge_shared_attributes(
                    Ar_Model_Viewer_For_Woocommerce_Settings::shared_attributes(),
                    $product
                )
            ),
            // These ones belong to the product: where its camera starts, how it is oriented.
            // A product that overrides nothing prints nothing here.
            Ar_Model_Viewer_For_Woocommerce_Settings::render_attributes(
                Ar_Model_Viewer_For_Woocommerce_Product_Model::attributes($product)
            ),
            $ar_button
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
     * Retrieves AR model viewer settings from the options page.
     *
     * This function fetches the AR model viewer settings configured in the WordPress options page using the CMB2 framework.
     * It retrieves settings related to how the AR model is loaded, displayed, and whether AR functionality is enabled.
     *
     * @since 1.0.0
     *
     * @param WC_Product|null $product Product whose overrides have to be taken into account.
     * @return array An associative array containing AR model viewer settings such as loading behavior, reveal method, AR modes, and more.
     */
    private function get_ar_model_viewer_settings($product = null)
    {
        $settings = Ar_Model_Viewer_For_Woocommerce_Settings::viewer_options();

        if (!$product instanceof WC_Product) {
            return $settings;
        }

        /*
         * The settings screen is the default and the product is the exception, so an override that
         * was never set keeps the global value instead of replacing it with an empty one.
         */
        $effective = Ar_Model_Viewer_For_Woocommerce_Product_Model::resolve_effective($product);

        if ('yes' === $effective['ar_enabled']) {
            $settings['ar'] = true;
        } elseif ('no' === $effective['ar_enabled']) {
            $settings['ar'] = false;
        }

        if ('' !== $effective['ar_scale']) {
            $settings['scale'] = $effective['ar_scale'];
        }

        if ('' !== $effective['ar_placement']) {
            $settings['placement'] = $effective['ar_placement'];
        }

        return $settings;
    }
}
