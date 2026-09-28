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
     * Register the metabox with the options of the viewer for a single product.
     *
     * It is a native metabox on purpose: these fields belong to the viewer, not to the metabox
     * Everything the viewer needs for one product lives in a single metabox, so the store does not
     * have to fill two forms to configure one viewer.
     *
     * @since 3.0.0
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_register_viewer_metabox()
    {
        add_meta_box(
            'ar-model-viewer-for-woocommerce-viewer',
            __('3D and AR viewer', 'ar-model-viewer-for-woocommerce'),
            array($this, 'ar_model_viewer_for_woocommerce_render_viewer_metabox'),
            'product',
            'normal',
            'high'
        );
    }

    /**
     * Render the single metabox of the viewer.
     *
     * @since 3.0.0
     * @param WP_Post $post Product being edited.
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_render_viewer_metabox($post)
    {
        $product = wc_get_product($post->ID);

        if (!$product) {
            return;
        }

        include plugin_dir_path(__FILE__) . 'partials/ar-model-viewer-for-woocommerce-admin-display-product-model.php';
    }

    /**
     * Save every viewer option of a product.
     *
     * The file fields and the viewer options are saved together because they share one nonce and
     * one form: a single metabox means a single round trip. An emptied field deletes its meta, so
     * a product that inherits every value carries nothing in the database, and the values are
     * validated by the model class that also reads them, so what is stored and what is printed can
     * never disagree.
     *
     * @since 3.0.0
     * @param int $post_id Product id.
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_save_viewer_options($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['armvw_viewer_nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['armvw_viewer_nonce']));

        if (!wp_verify_nonce($nonce, 'armvw_save_viewer_options')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $files = array();

        if (isset($_POST['armvw_files']) && is_array($_POST['armvw_files'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every value is sanitized in the model class.
            $files = wp_unslash($_POST['armvw_files']);
        }

        $options = array();

        if (isset($_POST['armvw_product']) && is_array($_POST['armvw_product'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every value is validated by type in the model class.
            $options = wp_unslash($_POST['armvw_product']);
        }

        Ar_Model_Viewer_For_Woocommerce_Product_Model::save_files($post_id, $files);
        Ar_Model_Viewer_For_Woocommerce_Product_Model::save($post_id, $options);

        $this->logger->log_to_woocommerce(
            sprintf('Viewer options updated for product ID: %d', $post_id),
            'info'
        );
    }
}
