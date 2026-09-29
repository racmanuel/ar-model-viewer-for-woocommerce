<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the public-facing stylesheet and JavaScript.
 * As you add hooks and methods, update this description.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public
 * @author     Manuel Ramirez Coronel <ra_cm@outlook.com>
 */
class Ar_Model_Viewer_For_Woocommerce_Public
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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name      The name of the plugin.
     * @param      string $plugin_prefix          The unique prefix of this plugin.
     * @param      string $version          The version of this plugin.
     */
    public function __construct($plugin_name, $plugin_prefix, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->plugin_prefix = $plugin_prefix;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /*
         * The design tokens are the ones the admin screens use, so the palette, the radii and the
         * spacings of the front cannot drift away from the panel that configures them.
         */
        $tokens_handle = $this->plugin_name . '-tokens';
        $tokens_path = plugin_dir_path(dirname(__FILE__)) . 'admin/css/ar-model-viewer-for-woocommerce-admin-tokens.css';
        $public_path = plugin_dir_path(__FILE__) . 'css/ar-model-viewer-for-woocommerce-public.css';

        /*
         * The version is the modification time of the file and not the version of the plugin: with a
         * fixed version the browser keeps the copy it cached, so a stylesheet that changes in an
         * update never reaches the shopper who already visited the site.
         */
        wp_enqueue_style(
            $tokens_handle,
            plugin_dir_url(dirname(__FILE__)) . 'admin/css/ar-model-viewer-for-woocommerce-admin-tokens.css',
            array(),
            file_exists($tokens_path) ? (string) filemtime($tokens_path) : $this->version,
            'all'
        );

        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/ar-model-viewer-for-woocommerce-public.css',
            array($tokens_handle),
            file_exists($public_path) ? (string) filemtime($public_path) : $this->version,
            'all'
        );

        /*
         * A jQuery UI theme used to be enqueued here. Nothing in the plugin uses those widgets any
         * more (the modal is built with alertify), and that stylesheet pointed at sprite images the
         * plugin never shipped, so every product page asked for files that do not exist and got a
         * 404 for each one. The stylesheet was deleted in 3.0.0.
         */
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        $script_path = plugin_dir_path(__FILE__) . 'js/ar-model-viewer-for-woocommerce-front.js';

        /*
         * No jQuery: the modal is a `<dialog>` and the request is a `fetch`. What used to be a
         * 880 KB bundle with the library, jQuery and alertify inside is this file plus the library,
         * and the library is requested by the script only when the page has something to render.
         */
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/ar-model-viewer-for-woocommerce-front.js',
            array(),
            file_exists($script_path) ? (string) filemtime($script_path) : $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'armvwFront',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'modelEndpoint' => esc_url_raw(rest_url('armvw/v1/products/')),
                'viewerUrl' => plugin_dir_url(dirname(__FILE__)) . 'assets/vendor/model-viewer.min.js',
                'action' => 'ar_model_viewer_for_woocommerce_get_model_and_settings',
                'buttonId' => 'ar_model_viewer_for_woocommerce_btn',
                'analytics' => array(
                    'enabled' => '1' === (string) Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_analytics'),
                    'showOptOut' => '1' === (string) Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_analytics_opt_out'),
                    'storageKey' => 'armvwAnalyticsOptOut',
                    'endpoint' => esc_url_raw(rest_url('armvw/v1/events')),
                ),
                // Static properties of the element, which cannot travel in the markup.
                'staticProperties' => Ar_Model_Viewer_For_Woocommerce_Settings::static_properties(),
                'i18n' => array(
                    'loading' => __('Loading the 3D model…', 'ar-model-viewer-for-woocommerce'),
                    'error' => __('The 3D model could not be loaded.', 'ar-model-viewer-for-woocommerce'),
                    'close' => __('Close', 'ar-model-viewer-for-woocommerce'),
                    'analyticsOptOut' => __('Do not measure this browser', 'ar-model-viewer-for-woocommerce'),
                    'analyticsOptIn' => __('Allow anonymous viewer analytics', 'ar-model-viewer-for-woocommerce'),
                ),
            )
        );
    }

    /**
     * Outputs the HTML for the AR model viewer button.
     *
     * This function includes a PHP file that contains the HTML and possibly some embedded PHP logic
     * for displaying a button. This button is used to trigger the AR model viewer functionality.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_button()
    {
        /*
         * A product whose viewer is switched off prints no button at all. The button is added to
         * the summary of every product, so without this check the store would hide the tab but
         * keep offering a modal that answers with an error.
         */
        $armvw_product = wc_get_product(get_the_ID());

        if ($armvw_product && !Ar_Model_Viewer_For_Woocommerce_Product_Model::is_enabled($armvw_product)) {
            return;
        }

        /*
         * The position decides between the button printed in the flow of the page and the one laid
         * over the product image, so the template has to know which of the two it is drawing.
         */
        $armvw_position = (string) Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_btn');

        // Include the HTML and PHP logic for displaying the AR model viewer button modal.
        include_once 'partials/ar-model-viewer-for-woocommerce-public-display-button.php';
    }

    public function ar_model_viewer_for_woocommerce_get_model_and_settings()
    {
        if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
            wp_send_json_error('Invalid Product ID.');
        }

        $product_id = absint($_POST['product_id']);

        if (!$product_id) {
            wp_send_json_error('Invalid Product ID.');
        }

        // The legacy action kept no variation, so the parameter is optional on purpose.
        $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;

        $data = $this->get_model_and_settings_data($product_id, $variation_id);

        if (is_wp_error($data)) {
            wp_send_json_error($data->get_error_message());
        }

        wp_send_json_success($data);
    }

    /**
     * Register the public REST route used by the viewer modal.
     *
     * @return void
     */
    public function register_rest_routes()
    {
        register_rest_route(
            'armvw/v1',
            '/products/(?P<product_id>\d+)/model',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_model_and_settings_rest'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'product_id' => array(
                        'validate_callback' => function ($value) {
                            return absint($value) > 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                    /*
                     * The variation is optional: a simple product asks for its model without one,
                     * and a variable product sends the id WooCommerce reports for the options the
                     * shopper is looking at. It is validated against the parent inside the resolver,
                     * so a hand written request cannot read a variation of another product.
                     */
                    'variation_id' => array(
                        'required' => false,
                        'default' => 0,
                        'validate_callback' => function ($value) {
                            return is_numeric($value) && absint($value) >= 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );
    }

    /**
     * Return one product viewer configuration through REST.
     *
     * @param WP_REST_Request $request REST request.
     * @return array|WP_Error
     */
    public function get_model_and_settings_rest(WP_REST_Request $request)
    {
        return $this->get_model_and_settings_data(
            absint($request['product_id']),
            absint($request->get_param('variation_id'))
        );
    }

    /**
     * Build the shared model and viewer response.
     *
     * @param int $product_id   Product identifier.
     * @param int $variation_id Optional variation of that product.
     * @return array|WP_Error
     */
    private function get_model_and_settings_data($product_id, $variation_id = 0)
    {
        $product = wc_get_product($product_id);

        if (!$product) {
            return new WP_Error('armvw_product_not_found', __('Product not found.', 'ar-model-viewer-for-woocommerce'), array('status' => 404));
        }

        if (!Ar_Model_Viewer_For_Woocommerce_Product_Model::is_enabled($product)) {
            return new WP_Error('armvw_viewer_disabled', __('The 3D viewer is switched off for this product.', 'ar-model-viewer-for-woocommerce'), array('status' => 404));
        }

        $viewer = Ar_Model_Viewer_For_Woocommerce_Settings::viewer_options();
        $loading = $viewer['loading'];
        $reveal = $viewer['reveal'];
        $with_credentials = $viewer['with_credentials'];
        $poster_color = $viewer['poster_color'];
        $xr_environment = $viewer['xr_environment'];
        $ar_modes = $viewer['ar_modes'];

        // The custom AR button replaces the default icon of the library, so the modal needs its
        // label and its colours. It is only offered when AR is enabled and the label is not empty.
        $ar_button_text = $viewer['ar_button_text'];
        $ar_button_background_color = $viewer['ar_button_background_color'];
        $ar_button_text_color = $viewer['ar_button_text_color'];

        /*
         * A variation only overrides its own resources, so the resolver returns the values of the
         * parent for everything the variation does not define. This is the same resolver the
         * shortcode and the product tab use, which is what keeps every placement in agreement.
         */
        $variation = Ar_Model_Viewer_For_Woocommerce_Product_Model::get_variation($product_id, $variation_id);
        $effective = Ar_Model_Viewer_For_Woocommerce_Product_Model::resolve_effective($product, $variation);

        if ('' === trim($effective['source'])) {
            return new WP_Error('armvw_model_missing', __('3D model file is missing.', 'ar-model-viewer-for-woocommerce'), array('status' => 404));
        }

        /*
         * The three AR switches fall back to the settings screen on their own, so a product that
         * does not override them keeps behaving exactly as it did before they existed.
         */
        $ar = $viewer['ar'];

        if ('yes' === $effective['ar_enabled']) {
            $ar = true;
        } elseif ('no' === $effective['ar_enabled']) {
            $ar = false;
        }

        $scale = '' !== $effective['ar_scale'] ? $effective['ar_scale'] : $viewer['scale'];
        $placement = '' !== $effective['ar_placement'] ? $effective['ar_placement'] : $viewer['placement'];
        $ar_button = $ar && $viewer['ar_button'] && '' !== trim((string) $ar_button_text);

        $product_attributes = Ar_Model_Viewer_For_Woocommerce_Product_Model::attributes($product);

        // Preparar los datos para el retorno
        $data = array(
            'product_id' => $effective['product_id'],
            'variation_id' => $effective['variation_id'],
            'loading' => $loading,
            'reveal' => $reveal,
            'with_credentials' => $with_credentials,
            'poster_color' => $poster_color,
            'ar' => $ar,
            'scale' => $scale,
            'placement' => $placement,
            'xr_environment' => $xr_environment,
            'ar_modes' => $ar_modes,
            'product_name' => $product->get_name(),
            'model_3d_file' => $effective['source'],
            'model_alt' => $effective['alt'],
            'model_poster' => $effective['poster'],
            'model_ios_src' => $effective['ios_src'],
            // Which layer answered for each resource: the store needs it to explain an inherited
            // model, and the shopper never sees it.
            'origins' => array(
                'model' => $effective['source_origin'],
                'poster' => $effective['poster_origin'],
                'ios_src' => $effective['ios_src_origin'],
            ),
            // Lighting and appearance settings travel as a ready to print list of attributes, so
            // the front-end modal does not have to know which settings map to which attribute.
            // The product switches are merged here and not appended, because a duplicated
            // attribute would leave the global value in force without any visible sign of it.
            'attributes' => Ar_Model_Viewer_For_Woocommerce_Product_Model::merge_shared_attributes(
                Ar_Model_Viewer_For_Woocommerce_Settings::shared_attributes(),
                $product
            ),
            // Overrides of this product, which the modal also receives instead of having to
            // reach for the meta values on its own.
            'product_attributes' => $product_attributes,
            'ar_button' => $ar_button,
            'ar_button_text' => $ar_button_text,
            'ar_button_background_color' => $ar_button_background_color,
            'ar_button_text_color' => $ar_button_text_color,
        );

        return $data;
    }
}
