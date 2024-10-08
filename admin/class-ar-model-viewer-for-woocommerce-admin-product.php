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
 * As you add hooks and methods, update this description.
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
     * The variable to call woocommerce class logger.
     *
     * @since    1.0.0
     * @access   public
     * @var      string    $logger    The variable to call woocommerce class logger.
     */
    public $logger;

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
        $this->logger = new Ar_Model_Viewer_For_Woocommerce_Logger($plugin_name, $plugin_prefix, $version);
    }

    public function ar_model_viewer_for_woocommerce_get_model_and_settings()
    {
        // Verificar si la petición AJAX incluye el ID del producto
        if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
            $this->logger->log_to_woocommerce('Invalid Product ID in AJAX request.', 'error'); // Log de error
            wp_send_json_error('Invalid Product ID.');
            wp_die();
        }

        // Obtener la ID del producto desde la petición AJAX
        $product_id = intval($_POST['product_id']); // Asegúrate de convertir a entero
        if (!$product_id) {
            $this->logger->log_to_woocommerce('Invalid Product ID after intval conversion.', 'error'); // Log de error
            wp_send_json_error('Invalid Product ID.');
            wp_die();
        }

        // Obtener las configuraciones globales
        $ar_model_viewer_settings = get_option('ar_model_viewer_for_woocommerce_settings');

        // Registrar que las configuraciones han sido recuperadas correctamente
        $this->logger->log_to_woocommerce('Global settings retrieved successfully.', 'info'); // Log informativo

        // Recuperar los campos configurados en la página de opciones usando CMB2
        $loading = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_loading', 'auto');
        $reveal = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_reveal', 'auto');
        $with_credentials = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_with_credentials', 'false');
        $poster_color = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_poster_color', 'rgba(255,255,255,0)');
        $ar = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar', 'active');
        $scale = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_scale', 'auto');
        $placement = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_placement', 'floor');
        $xr_environment = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_xr_environment', 'deactive');
        $ar_modes = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_modes', ['webxr', 'scene-viewer']);

        // Obtener el nombre del producto
        $product_name = get_the_title($product_id);

        // Log de recuperación del producto
        $this->logger->log_to_woocommerce("Product retrieved: $product_name (ID: $product_id)", 'info'); // Log informativo

        // Obtener los metadatos del producto
        $model_alt = self::get_model_alt_or_fallback($product_id);
        $model_poster = self::get_model_poster_or_fallback($product_id);
        $model_3d_file = self::get_model_3d_file_or_fallback($product_id);

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

        // Enviar la respuesta en formato JSON y loguear el éxito
        $this->logger->log_to_woocommerce("Successfully prepared 3D model data for product: $product_name (ID: $product_id)", 'info'); // Log de éxito
        wp_send_json_success($data);
        wp_die();
    }

    /**
     * Retrieves the alt text for the 3D model or falls back to the product name or short description.
     *
     * This function checks if the 3D model alt text is set. If not, it will try to return the product name.
     * If the product name is also not set, it will return the short description.
     * If none of these fields are available, it will log an error using the WooCommerce logger.
     *
     * @param int $product_id The product ID.
     * @return string The alt text for the 3D model, or the product name, or the short description.
     */
    private function get_model_alt_or_fallback($product_id)
    {
        // Retrieve the WooCommerce product object
        $product = wc_get_product($product_id);

        // If the product doesn't exist, log an error and return an empty string
        if (!$product) {
            $this->logger->log_to_woocommerce('Product not found for ID ' . $product_id, 'error'); // Log an error
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
                    $this->logger->log_to_woocommerce('No alt, product name, or short description found for product ID ' . $product_id, 'error'); // Log an error
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
     * @param int $product_id The product ID.
     * @return string The URL of the poster image or the product's main image.
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
     * @param int $product_id The product ID.
     * @return string The URL of the 3D model file.
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

        // Comprobar que el archivo 3D existe
        if (!$model_3d_file) {
            $this->logger->log_to_woocommerce("3D model file missing for product: {$product->get_name} (ID: $product_id)", 'error'); // Log de error
            wp_send_json_error('3D model file missing for product. Try save the product before view a preview.');
            wp_die();
        }

        // Log de éxito si el archivo 3D se encuentra
        $this->logger->log_to_woocommerce("3D model file found for product: {$product->get_name} (ID: $product_id)", 'info'); // Log informativo

        // Return the 3D model file URL
        return $model_3d_file;
    }
}
