<?php
/**
 * The WooCommerce integration of the plugin.
 *
 * The plugin is a WooCommerce extension, so most of what it does only makes sense while
 * WooCommerce is loaded. This class is the seam: it owns the product data tab, the variation
 * fields and the two save routines, and it is only instantiated when WooCommerce is available.
 * The viewer itself, the settings screen and the shortcode keep working without it, which is what
 * keeps the plugin from breaking a site where WooCommerce was deactivated but not deleted.
 *
 * Everything it writes goes through `Ar_Model_Viewer_For_Woocommerce_Product_Model`, so the values
 * saved from the editor, from a CSV and from the REST endpoint are validated by the same code.
 *
 * @link       https://racmanuel.dev
 * @since      3.1.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Registers the viewer with the product editor of WooCommerce.
 *
 * @since      3.1.0
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 */
class Ar_Model_Viewer_For_Woocommerce_Admin_WooCommerce
{
    /**
     * The ID of this plugin.
     *
     * @since    3.1.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The unique prefix of this plugin.
     *
     * @since    3.1.0
     * @access   private
     * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
     */
    private $plugin_prefix;

    /**
     * The version of this plugin.
     *
     * @since    3.1.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The variable to call WooCommerce class logger.
     *
     * @since    3.1.0
     * @access   private
     * @var      object    $logger    The variable to call WooCommerce class logger.
     */
    private $logger;

    /**
     * Action shared by the product panel and the variation fields.
     *
     * @since 3.1.0
     * @var   string
     */
    const NONCE_ACTION = 'armvw_save_viewer_options';

    /**
     * Name of the nonce field printed inside the product form.
     *
     * @since 3.1.0
     * @var   string
     */
    const NONCE_NAME = 'armvw_viewer_nonce';

    /**
     * Initialize the class and set its properties.
     *
     * @since    3.1.0
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
     * Print the viewer fields inside the General tab of Product data.
     *
     * The fields are appended to the panel WooCommerce already uses for the price and the tax
     * status, and they are built with its own markup: an `options_group`, `form-field` rows and
     * `description` lines. Offering them in a tab of this plugin meant a store had to learn a
     * second design, and a product page was configured in two different languages.
     *
     * The General tab shows for simple, variable and external products, so a variable product
     * keeps its base configuration here while every variation can override its three files.
     *
     * @since 3.2.0
     * @return void
     */
    public function render_product_fields()
    {
        global $post;

        $product = $post && isset($post->ID) ? wc_get_product($post->ID) : null;

        if (!$product) {
            return;
        }

        include plugin_dir_path(__FILE__) . 'partials/ar-model-viewer-for-woocommerce-admin-product-fields.php';
    }

    /**
     * Save the panel when WooCommerce saves the product.
     *
     * A store that submits the editor without ever opening the tab does not send the fields, and
     * that is on purpose: absent means "do not touch", which is what stops a quick edit from
     * wiping a model the store never saw.
     *
     * @since 3.1.0
     * @param WC_Product $product Product being saved.
     * @return void
     */
    public function save_product($product)
    {
        if (!$product instanceof WC_Product) {
            return;
        }

        if (!$this->verify_nonce()) {
            return;
        }

        $product_id = $product->get_id();

        if (!$product_id || !current_user_can('edit_post', $product_id)) {
            return;
        }

        $files = $this->posted_array('armvw_files');
        $options = $this->posted_array('armvw_product');
        $switches = $this->posted_array('armvw_switches');

        // The order matters: the files first, because the switch of the viewer is read together
        // with the model that is about to exist.
        Ar_Model_Viewer_For_Woocommerce_Product_Model::save_files($product_id, $files);
        Ar_Model_Viewer_For_Woocommerce_Product_Model::save($product_id, $options);
        Ar_Model_Viewer_For_Woocommerce_Product_Model::save_switches($product_id, $switches);

        $this->logger->log_to_woocommerce(
            sprintf('Viewer options updated for product ID: %d', $product_id),
            'info'
        );
    }

    /**
     * Print the viewer fields of one variation.
     *
     * @since 3.1.0
     * @param int                  $loop           Index of the variation in the list.
     * @param array                $variation_data Raw variation data.
     * @param WC_Product_Variation $variation      Variation being edited.
     * @return void
     */
    public function render_variation_fields($loop, $variation_data, $variation)
    {
        include plugin_dir_path(__FILE__) . 'partials/ar-model-viewer-for-woocommerce-admin-variation.php';
    }

    /**
     * Save the viewer fields of one variation.
     *
     * WooCommerce verifies its own nonce and the capability of the store before this method runs,
     * but both checks are repeated here: this is the boundary where data from a form becomes
     * post meta, and a boundary is not a place to trust the caller.
     *
     * @since 3.1.0
     * @param int $variation_id Variation being saved.
     * @param int $loop         Index of the variation in the list.
     * @return void
     */
    public function save_variation($variation_id, $loop)
    {
        $variation_id = absint($variation_id);
        $loop = absint($loop);

        if (!$variation_id || !$this->verify_nonce()) {
            return;
        }

        if (!current_user_can('edit_post', $variation_id)) {
            return;
        }

        if (!isset($_POST['armvw_variation'][$loop]) || !is_array($_POST['armvw_variation'][$loop])) {
            return;
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every value is validated by type in the model class.
        $input = wp_unslash($_POST['armvw_variation'][$loop]);

        Ar_Model_Viewer_For_Woocommerce_Product_Model::save_variation($variation_id, $input);
    }

    /**
     * Whether the form carries a valid nonce of this plugin.
     *
     * @since 3.1.0
     * @return bool True when the request came from the product editor.
     */
    private function verify_nonce()
    {
        if (!isset($_POST[self::NONCE_NAME])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME]));

        return (bool) wp_verify_nonce($nonce, self::NONCE_ACTION);
    }

    /**
     * Read an array out of the request without sanitizing it yet.
     *
     * The values are sanitized by type inside the model class, which is the only place that knows
     * whether a field is a vector, an angle or a URL.
     *
     * @since 3.1.0
     * @param string $key Name of the field.
     * @return array The submitted values, or an empty array.
     */
    private function posted_array($key)
    {
        if (!isset($_POST[$key]) || !is_array($_POST[$key])) {
            return array();
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by type in the model class.
        return wp_unslash($_POST[$key]);
    }
}
