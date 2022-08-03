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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ar-model-viewer-for-woocommerce-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_scripts($hook_suffix)
    {

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ar-model-viewer-for-woocommerce-admin.js', array('jquery'), $this->version, false);

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

        $cmb->add_field(array(
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-3d-object-18(-ldpi).png' . '" class="icon-in-field"></img> 3D Model',
            'desc' => 'Add the files of 3D model to this product. Only glTF/GLB models are supported.',
            'type' => 'title',
            'id' => 'ar_model_viewer_for_woocommerce_title_3d_model',
        ));

        // Regular File field - Android .glb
        $cmb->add_field(array(
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-android-os-18(-ldpi).png' . '" class="icon-in-field"></img> File for Android',
            'desc' => 'Upload or enter an URL to 3D object (with .glb extension).',
            'id' => 'ar_model_viewer_for_woocommerce_file_android',
            'type' => 'file',
            // Optional:
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'text' => array(
                'add_upload_file_text' => 'Add File', // Change upload button text. Default: "Add or Upload File"
            ),
            // query_args are passed to wp.media's library query.
            'query_args' => array(
                'type' => 'model/gltf-binary', // Make library only display .glb files.
            ),
        ));

        // Regular File field - IOS .usdz
        $cmb->add_field(array(
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-mac-client-18(-ldpi).png' . '" class="icon-in-field"></img> File for IOS',
            'desc' => 'Upload or enter an URL to 3D object (with .usdz extension).',
            'id' => 'ar_model_viewer_for_woocommerce_file_ios',
            'type' => 'file',
            // Optional:
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'text' => array(
                'add_upload_file_text' => 'Add File', // Change upload button text. Default: "Add or Upload File"
            ),
            // query_args are passed to wp.media's library query.
            'query_args' => array(
                'type' => 'model/vnd.usdz+zip', // Make library only display .glb files.
            ),
        ));

        //Regular File Field to Poster
        $cmb->add_field(array(
            'name' => 'Poster',
            'desc' => 'Upload an image or enter an URL. If the image field (alt) is left empty, the photo of the product is taken. This field displays an image instead of the model, useful for showing the user something before a model is loaded and ready to render.',
            'id' => 'ar_model_viewer_for_woocommerce_file_poster',
            'type' => 'file',
            // Optional:
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'text' => array(
                'add_upload_file_text' => 'Add File', // Change upload button text. Default: "Add or Upload File"
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
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-web-accessibility-18(-ldpi).png' . '" class="icon-in-field"></img> alt',
            'desc' => 'Insert a text. if the text field is left empty, the name of the product is taken. Configures the model with custom text that will be used to describe the model to viewers who use a screen reader or otherwise depend on additional semantic context to understand what they are viewing.',
            'default' => '',
            'id' => 'ar_model_viewer_for_woocommerce_file_alt',
            'type' => 'text',
        ));

        // Add other metaboxes as needed
        do_action('ar_model_viewer_for_woocommerce_custom_fields');

        /**
         * Registers options page menu item and form.
         */
        $cmb = new_cmb2_box(array(
            'id' => 'ar_model_viewer_for_woocommerce_settings',
            //'title' => esc_html__('AR Model Viewer for WooCommerce', 'cmb2'),
            'object_types' => array('options-page'),

            /*
             * The following parameters are specific to the options-page box
             * Several of these parameters are passed along to add_menu_page()/add_submenu_page().
             */

            'option_key' => 'ar_model_viewer_for_woocommerce_settings', // The option key and admin menu page slug.
            // 'icon_url'        => '', // Menu icon. Only applicable if 'parent_slug' is left empty.
            'menu_title' => esc_html__('AR Model Viewer for WooCommerce', 'cmb2'), // Falls back to 'title' (above).
            'parent_slug' => 'options-general.php', // Make options page a submenu item of the themes menu.
            'capability' => 'manage_options', // Cap required to view options-page.
            // 'position'        => 1, // Menu position. Only applicable if 'parent_slug' is left empty.
            // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
            // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
            'save_button' => esc_html__('Save', 'cmb2'), // The text for the options-page save button. Defaults to 'Save'.
            // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
            // 'message_cb'      => 'yourprefix_options_page_message_callback',
        ));

        /** NOTE FOR DEVELOPMENT - PENDING */
        $cmb->add_field(array(
            'name' => 'View Options',
            'desc' => '',
            'type' => 'title',
            'id' => 'wiki_test_title',
            'before_row' => array('Ar_Model_Viewer_For_Woocommerce_Admin', 'ar_model_viewer_for_woocommerce_cmb2_after_row'),
        ));

        $cmb->add_field(array(
            'name' => 'Show AR Model Viewer button in',
            'id' => 'ar_model_viewer_for_woocommerce_btn',
            'type' => 'radio_inline',
            'show_option_none' => false,
            'options' => array(
                '0' => __('None', 'cmb2'),
                '1' => __('woocommerce_before_single_product_summary', 'cmb2'),
                '2' => __('woocommerce_after_single_product_summary', 'cmb2'),
                '3' => __('woocommerce_before_single_product', 'cmb2'),
                '4' => __('woocommerce_after_single_product', 'cmb2'),
                '5' => __('woocommerce_after_add_to_cart_form', 'cmb2'),
                '6' => __('woocommerce_before_add_to_cart_form', 'cmb2'),
            ),
            'default' => 'standard',
            'classes' => 'switch-field',
        ));

        $cmb->add_field(array(
            'name' => 'Show in Product Tabs',
            'id' => 'ar_model_viewer_for_woocommerce_single_product_tabs',
            'type' => 'radio_inline',
            'show_option_none' => false,
            'options' => array(
                'yes' => __('Yes', 'cmb2'),
                'no' => __('No', 'cmb2'),
            ),
            'default' => 'standard',
            'classes' => 'switch-field',
        ));
    }

    public static function ar_model_viewer_for_woocommerce_cmb2_after_row($field_args, $field)
    {
        include_once 'partials/ar-model-viewer-for-woocommerce-admin-display.php';
    }
}
