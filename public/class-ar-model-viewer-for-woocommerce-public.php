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
        // Obtener el nombre del producto
        $product_name = get_the_title($product_id);

        // Obtener los metadatos del producto
        $model_3d_file = get_post_meta($product_id, 'ar_model_viewer_for_woocommerce_file_object', true);
        $model_alt = get_post_meta($product_id, 'ar_model_viewer_for_woocommerce_file_alt', true);
        $model_poster = get_post_meta($product_id, 'ar_model_viewer_for_woocommerce_file_poster', true);

        // Comprobar que el archivo 3D existe
        if (!$model_3d_file) {
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
            'product_name' => $product_name,
            'model_3d_file' => $model_3d_file,
            'model_alt' => $model_alt,
            'model_poster' => $model_poster,
        );

        // Enviar la respuesta en formato JSON
        wp_send_json_success($data);
        wp_die();
    }
}
