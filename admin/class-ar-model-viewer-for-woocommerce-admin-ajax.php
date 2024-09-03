<?php
/**
 * The admin-specific functionality of custom post type of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of custom post type of the plugin.
 *
 * This file include functions related with the custom post type for 
 * make models with 3D and AR using a shortcode of the plugin.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 * @author     Manuel Ramirez Coronel <ra_cm@outlook.com>
 */
class Ar_Model_Viewer_For_Woocommerce_Admin_Ajax
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
     * @param      string $plugin_name       The name of this plugin.
     * @param      string $plugin_prefix    The unique prefix of this plugin.
     * @param      string $version    The version of this plugin.
     */
    public function __construct($plugin_name, $plugin_prefix, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->plugin_prefix = $plugin_prefix;
        $this->version = $version;

    }

    public function ar_model_viewer_for_woocommerce_get_model_data()
    {
        // Verificar el nonce
        check_ajax_referer('ar-model-viewer-ajax-nonce', 'nonce');

        // Ahora puedes manejar la solicitud AJAX de forma segura
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        // Realiza las operaciones necesarias con $post_id

        //File url for android
        $android_file = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_android', true);
        //File url for IOS
        $ios_file = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_ios', true);
        //Text alt for web accessibility
        $alt = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_alt', true);
        //File for Poster
        $poster = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_poster', true);

        // Devuelve una respuesta
        wp_send_json_success(
            array(
                'message' => 'Operación exitosa',
                'post_id' => $post_id,
                'android_file' => $android_file,
                'ios_file' => $ios_file,
                'alt' => $alt,
                'poster' => $poster,
            ));
    }

}
