<?php
/**
 * The public-facing tab functionality of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public
 */

/**
 * The public-facing tab functionality of the plugin.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public
 * @author
 */
class Ar_Model_Viewer_For_Woocommerce_Public_Tab
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
     * Adds a custom tab to the WooCommerce product page for viewing the product in 3D.
     *
     * This function adds a new tab to the product page in WooCommerce. The tab will display the title "View Product on 3D"
     * and will load its content via the specified callback function.
     *
     * @since 1.0.0
     *
     * @param array $tabs An associative array of the existing WooCommerce product tabs.
     *
     * @return array Modified array of product tabs with the new 3D model viewer tab added.
     */
    public function ar_model_viewer_for_woocommerce_tab($tabs)
    {
        // The tab title is configurable in the settings, with a translated fallback.
        $tab_title = (string) Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_tab_title');

        if ('' === trim($tab_title)) {
            $tab_title = __('View Product on 3D', 'ar-model-viewer-for-woocommerce');
        }

        // Adds the new tab to the WooCommerce product page.
        $tabs['ar_model_viewer'] = array(
            'title' => $tab_title, // Title of the tab.
            'priority' => 50, // Priority of the tab (controls the order of appearance).
            'callback' => array($this, 'ar_model_viewer_for_woocommerce_tab_content'), // Callback to render the tab content.
        );

        // Return the modified tabs array with the new tab added.
        return $tabs;
    }

    public function ar_model_viewer_for_woocommerce_tab_content()
    {
        // Global WooCommerce product variable.
        global $product;

        // Ensure that the global product object is valid.
        if (!$product || !is_a($product, 'WC_Product')) {
            error_log('Invalid product object in AR model viewer tab content.');
            echo esc_html__('Invalid product.', 'ar-model-viewer-for-woocommerce');
            return;
        }

        // Retrieve the product ID.
        $product_id = $product->get_id();

        // Log an error and return a message if the product ID is invalid.
        if (empty($product_id)) {
            error_log('Invalid product ID in AR model viewer tab content.');
            echo esc_html__('Invalid product ID.', 'ar-model-viewer-for-woocommerce');
            return;
        }

        // The model of the product, with the poster and the alt text already defaulted. The tab
        // shows a message instead of an empty viewer when the product has no file yet, because
        // the shopper is already inside the product page.
        $model = Ar_Model_Viewer_For_Woocommerce_Product_Model::resolve($product);

        if ('' === trim($model['source'])) {
            echo '<p>' . esc_html__('This product has no 3D model yet.', 'ar-model-viewer-for-woocommerce') . '</p>';

            return;
        }

        // Retrieve the AR settings from the plugin's options.
        $settings = $this->get_ar_model_viewer_settings();

        // Initialize AR attributes based on the retrieved settings.
        $ar_attributes = '';
        if ($settings['ar']) {
            $ar_attributes .= 'ar ';

            // An empty mode list would be rendered as `ar-modes=""`, which the library cannot
            // parse, so the attribute is omitted and the viewer keeps its own default list.
            if (!empty($settings['ar_modes'])) {
                $ar_attributes .= 'ar-modes="' . esc_attr(implode(' ', $settings['ar_modes'])) . '" ';
            }

            if (!empty($settings['scale'])) {
                $ar_attributes .= 'ar-scale="' . esc_attr($settings['scale']) . '" ';
            }
            if (!empty($settings['placement'])) {
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
            '<model-viewer src="%1$s" alt="%2$s" poster="%3$s" loading="%4$s" reveal="%5$s" style="background-color: %6$s;" %7$s%8$s%9$s%10$s>%11$s</model-viewer>',
            esc_url($model['source']),
            esc_attr($model['alt']),
            esc_url($model['poster']),
            esc_attr($settings['loading']),
            esc_attr($settings['reveal']),
            esc_attr($settings['poster_color']),
            $ar_attributes,
            $extra_attributes,
            // Lighting and appearance attributes are shared by every placement, so they are
            // built in one place and appended here instead of being repeated per template.
            Ar_Model_Viewer_For_Woocommerce_Settings::render_attributes(
                Ar_Model_Viewer_For_Woocommerce_Settings::shared_attributes()
            ),
            // These ones belong to the product: where its camera starts, how it is oriented.
            // A product that overrides nothing prints nothing here.
            Ar_Model_Viewer_For_Woocommerce_Settings::render_attributes(
                Ar_Model_Viewer_For_Woocommerce_Product_Model::attributes($product)
            ),
            $ar_button
        );

        // Output the generated model viewer HTML.
        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every part is escaped above.
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
        return Ar_Model_Viewer_For_Woocommerce_Settings::viewer_options();
    }
}
