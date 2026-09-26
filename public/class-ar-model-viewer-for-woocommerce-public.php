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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ar-model-viewer-for-woocommerce-public.css', array(), $this->version, 'all');
        wp_enqueue_style('jquery-ui-theme', plugin_dir_url(__FILE__) . 'css/jquery-ui.min.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ar-model-viewer-for-woocommerce-public-dist.js', array('jquery'), $this->version, true);
        wp_localize_script($this->plugin_name, 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

        // The render scale, the power preference, the cache size and the decoder locations are
        // static properties of the element, not attributes, so they cannot travel in the markup.
        // They are assigned right after the library is evaluated and before any viewer is
        // created, which is the only moment the library reads them.
        $this->add_static_properties_script($this->plugin_name);
    }

    /**
     * Print the script that applies the static properties of the viewer element.
     *
     * @since 3.0.0
     * @param string $handle Handle of the script that loads the library.
     * @return void
     */
    private function add_static_properties_script($handle)
    {
        $properties = Ar_Model_Viewer_For_Woocommerce_Settings::static_properties();

        if (array() === $properties) {
            return;
        }

        $script = sprintf(
            "customElements.whenDefined('model-viewer').then(function(){var viewer=customElements.get('model-viewer');var values=%s;Object.keys(values).forEach(function(name){viewer[name]=values[name];});});",
            wp_json_encode($properties)
        );

        wp_add_inline_script($handle, $script, 'after');
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
        // Include the HTML and PHP logic for displaying the AR model viewer button modal.
        include_once 'partials/ar-model-viewer-for-woocommerce-public-display-button.php';
    }

    public function ar_model_viewer_for_woocommerce_get_model_and_settings()
    {
        // Verificar si la petición AJAX incluye el ID del producto
        if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
            wp_send_json_error('Invalid Product ID.');
            wp_die();
        }

        // Obtener la ID del producto desde la petición AJAX
        $product_id = intval($_POST['product_id']); // Asegúrate de convertir a entero
        if (!$product_id) {
            wp_send_json_error('Invalid Product ID.');
            wp_die();
        }

        // Retrieve the global settings and expand them into the local variables used below.
        $viewer = Ar_Model_Viewer_For_Woocommerce_Settings::viewer_options();
        $loading = $viewer['loading'];
        $reveal = $viewer['reveal'];
        $with_credentials = $viewer['with_credentials'];
        $poster_color = $viewer['poster_color'];
        $ar = $viewer['ar'];
        $scale = $viewer['scale'];
        $placement = $viewer['placement'];
        $xr_environment = $viewer['xr_environment'];
        $ar_modes = $viewer['ar_modes'];
        $product = wc_get_product($product_id);

        if (!$product) {
            wp_send_json_error('Product not found.');
            wp_die();
        }

        // The same resolver the shortcode and the product tab use. This endpoint used to read the
        // meta values on its own, so it returned an empty poster for a product that had no poster
        // of its own but did have a featured image.
        $model = Ar_Model_Viewer_For_Woocommerce_Product_Model::resolve($product);

        if ('' === trim($model['source'])) {
            wp_send_json_error('3D model file is missing.');
            wp_die();
        }

        // Preparar los datos para el retorno
        $data = array(
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
            'model_3d_file' => $model['source'],
            'model_alt' => $model['alt'],
            'model_poster' => $model['poster'],
            // Lighting and appearance settings travel as a ready to print list of attributes, so
            // the front-end modal does not have to know which settings map to which attribute.
            'attributes' => Ar_Model_Viewer_For_Woocommerce_Settings::shared_attributes(),
            // Overrides of this product, which the modal also receives instead of having to
            // reach for the meta values on its own.
            'product_attributes' => Ar_Model_Viewer_For_Woocommerce_Product_Model::attributes($product),
        );

        // Enviar la respuesta en formato JSON
        wp_send_json_success($data);
        wp_die();
    }
}
