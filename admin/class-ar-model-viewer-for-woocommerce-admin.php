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
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the admin-facing stylesheet and JavaScript.
 * As you add hooks and methods, update this description.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 * @author     Manuel Ramirez Coronel <ra_cm@outlook.com>
 */
class Ar_Model_Viewer_For_Woocommerce_Admin
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

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_styles($hook_suffix)
    {
        // For debugging the $hook_suffix
       // echo '<h1 style="color: crimson;">' . esc_html($hook_suffix) . '</h1>';

        if ($hook_suffix == 'settings_page_ar_model_viewer_for_woocommerce_settings') {
            wp_enqueue_style($this->plugin_name . '-settings', plugin_dir_url(__FILE__) . 'css/ar-model-viewer-for-woocommerce-admin-settings.css', array(), $this->version, 'all');
        }

        if ($hook_suffix == 'post.php' || get_post_type( get_the_ID()) == 'product') {
            wp_enqueue_style($this->plugin_name . '-product', plugin_dir_url(__FILE__) . 'css/ar-model-viewer-for-woocommerce-admin-product.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_scripts($hook_suffix)
    {

        // For debug the $hook_suffix echo '<h1 style="color: crimson;">' . esc_html( $hook_suffix ) . '</h1>';
        if ($hook_suffix === 'settings_page_ar_model_viewer_for_woocommerce_settings') {
            wp_enqueue_script($this->plugin_name . '-settings', plugin_dir_url(__FILE__) . 'js/ar-model-viewer-for-woocommerce-admin-settings-dist.js', array('jquery', 'wp-i18n'), $this->version, false);
            wp_localize_script($this->plugin_name . '-settings', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

            // Overwrite Automattic's Iris color picker to enable alpha channel (transparency) support in the WordPress color picker.
            // This is done to enhance the color picker functionality to handle RGBA colors, not just RGB.

            // Overwrite WordPress's default color picker to improve implementation and integration of the Iris color picker with alpha channel support.
            // This is necessary to ensure that the extended functionality of the color picker (alpha channel) works seamlessly with WordPress.

            // Register the 'wp-color-picker-alpha' script in WordPress.
            // The script is dependent on the existing 'wp-color-picker' script, ensuring that it integrates properly.
            // The script is located in the 'js' directory of the plugin, and the URL is constructed using 'plugin_dir_url(__FILE__)'.
            // '$this->version' specifies the version of the script, which is useful for cache busting.
            // 'false' as the last parameter indicates that the script should not be loaded in the footer.
            wp_register_script('wp-color-picker-alpha', plugin_dir_url(__FILE__) . 'js/wp-color-picker-alpha.min.js', array('wp-color-picker'), $this->version, false);

            // Add inline script to initialize the color picker on elements with the class 'color-picker'.
            // The jQuery function is used to ensure compatibility and proper initialization.
            // 'wpColorPicker()' is called on elements with the class 'color-picker', initializing the enhanced color picker with alpha channel support.
            wp_add_inline_script(
                'wp-color-picker-alpha',
                'jQuery( function() { jQuery( ".color-picker" ).wpColorPicker(); } );'
            );

            // Enqueue the 'wp-color-picker-alpha' script to ensure it is loaded and executed on the WordPress site.
            // This step is crucial for the script to take effect and enhance the color picker functionality on the site.
            wp_enqueue_script('wp-color-picker-alpha');

        }
        if ($hook_suffix === 'post.php' || get_post_type( get_the_ID()) == 'product') {
            wp_enqueue_script($this->plugin_name . '-product', plugin_dir_url(__FILE__) . 'js/ar-model-viewer-for-woocommerce-admin-product-dist.js', array('jquery', 'wp-i18n'), $this->version, false);
            wp_localize_script($this->plugin_name . '-product', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        }
    }

    /**
     * Sets the extension and mime type for Android - .gbl and IOS - .usdz files.
     * @param array  $wp_check_filetype_and_ext File data array containing 'ext', 'type', and 'proper_filename' keys.
     * @param string $file                      Full path to the file.
     * @param string $filename                  The name of the file (may differ from $file due to $file being in a tmp directory).
     * @param array  $mimes                     Key is the file extension with value as the mime type.
     */
    public function ar_model_viewer_for_woocommerce_file_and_ext($types, $file, $filename, $mimes)
    {
        if (false !== strpos($filename, '.glb')) {
            $types['ext'] = 'glb';
            $types['type'] = 'model/gltf-binary';
        }
        if (false !== strpos($filename, '.usdz')) {
            $types['ext'] = 'usdz';
            $types['type'] = 'model/vnd.usdz+zip';
        }
        return $types;
    }

    /**
     * Adds Android - .gbl and IOS - .usdz filetype to allowed mimes
     * @see https://codex.wordpress.org/Plugin_API/Filter_Reference/upload_mimes
     * @param array $mimes Mime types keyed by the file extension regex corresponding tothose types. 'swf' and 'exe' removed from full list. 'htm|html' also removed depending on '$user' capabilities.
     * @return array
     */
    public function ar_model_viewer_for_woocommerce_mime_types($mimes)
    {
        $mimes['glb'] = 'model/gltf-binary'; //Adding gbl extension
        $mimes['usdz'] = 'model/vnd.usdz+zip'; //Adding usdz extension
        return $mimes;
    }

    /**
     * Define the metabox and field configurations.
     */
    public function ar_model_viewer_for_woocommerce_cmb2_metaboxes()
    {
        /**
         * Initiate the metabox
         */
        $cmb = new_cmb2_box(array(
            'id' => 'ar_model_viewer_for_woocommerce_metaboxes',
            'title' => __('AR Model Viewer for WooCommerce', 'cmb2'),
            'object_types' => array('product'), // Post type
            'context' => 'normal',
            'priority' => 'low',
            'show_names' => true, // Show field names on the left
            'cmb_styles' => true, // false to disable the CMB stylesheet
            'closed' => false, // Keep the metabox closed by default
        ));

        // Regular File field - Android .glb
        $cmb->add_field(array(
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-3d-94.png' . '" class="icon-in-field"></img> 3D Object File',
            'desc' => 'Upload or enter an URL to 3D object (with .glb  or .glTF extension).',
            'id' => 'ar_model_viewer_for_woocommerce_file_object',
            'type' => 'file',
            // Optional:
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'text' => array(
                'add_upload_file_text' => 'Add URL or File', // Change upload button text. Default: "Add or Upload File"
            ),
            // query_args are passed to wp.media's library query.
            'query_args' => array(
                'type' => 'model/gltf-binary', // Make library only display .glb files.
            ),
            'before' => array(__CLASS__, 'ar_model_viewer_for_woocommerce_before_title_row'),
        ));

        //Regular File Field to Poster
        $cmb->add_field(array(
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-photo-gallery-94.png' . '" class="icon-in-field"></img> Poster',
            'desc' => 'Upload an image or enter an URL. If the image field (alt) is left empty, the photo of the product is taken. This field displays an image instead of the model, useful for showing the user something before a model is loaded and ready to render.',
            'id' => 'ar_model_viewer_for_woocommerce_file_poster',
            'type' => 'file',
            // Optional:
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'text' => array(
                'add_upload_file_text' => 'Add Image', // Change upload button text. Default: "Add or Upload File"
            ),
            // query_args are passed to wp.media's library query.
            'query_args' => array(
                // Or only allow gif, jpg, or png images
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ),
            ),
            'preview_size' => 'thumbnail', // Image size to use when previewing in the admin.
        ));

        // Regular Text field - alt for models
        $cmb->add_field(array(
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-info-94.png' . '" class="icon-in-field"></img> alt',
            'desc' => 'Insert a text. if the text field is left empty, the name of the product is taken. Configures the model with custom text that will be used to describe the model to viewers who use a screen reader or otherwise depend on additional semantic context to understand what they are viewing.',
            'id' => 'ar_model_viewer_for_woocommerce_file_alt',
            'type' => 'text',
            'after_row' => array(__CLASS__, 'ar_model_viewer_for_woocommerce_after_title_row'),
        ));
    }

    public function ar_model_viewer_for_woocommerce_error_notice()
    {
        echo '<div class="notice notice-error is-dismissible"><p>' . __('AR Model Viewer for WooCommerce is active but not working. You need to install the WooCommerce plugin for the plugin to work properly.', 'datos-de-facturacion-para-mexico') . '</p></div>';
    }

    public function ar_model_viewer_for_woocommerce_blocksy_fix($current_value)
    {
        // Use WooCommerce built in gallery
        return true;
    }

    public static function ar_model_viewer_for_woocommerce_before_title_row()
    {
        include_once 'partials/ar-model-viewer-for-woocommerce-admin-display-product-header.php';
    }

    public static function ar_model_viewer_for_woocommerce_after_title_row()
    {
        include_once 'partials/ar-model-viewer-for-woocommerce-admin-display-product-footer.php';
    }
}
