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
        // For debug the $hook_suffix echo '<h1 style="color: crimson;">' . esc_html( $hook_suffix ) . '</h1>';
        if ($hook_suffix == 'settings_page_ar_model_viewer_for_woocommerce_settings') {
            wp_enqueue_style($this->plugin_name . '-settings', plugin_dir_url(__FILE__) . 'css/ar-model-viewer-for-woocommerce-admin.css', array(), $this->version, 'all');
        }
        if ($hook_suffix == 'edit.php?post_type=product' || 'post-new.php?post_type=product') {
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
            'desc' => 'Upload or enter an URL to 3D object (with .usdz extension). <br><hr>The url to a USDZ model which will be used on supported iOS 12+ devices via AR Quick Look on Safari. The presence of this attribute will automatically enable the quick-look ar-mode, however it is no longer necessary. If instead the quick-look ar-mode is specified and ios-src is not specified, then we will generate a USDZ on the fly when the AR button is pressed. This means modifications via the scene-graph API will now be reflected in Quick Look. Hoowever, USDZ generation is not perfect, for instance animations are not yet supported, so in some cases supplying ios-src may give better results.',
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
            'name' => '<span class="dashicons dashicons-admin-generic"></span> View Settings',
            'desc' => '',
            'type' => 'title',
            'id' => 'wiki_test_title',
            'before_row' => array('Ar_Model_Viewer_For_Woocommerce_Admin', 'ar_model_viewer_for_woocommerce_cmb2_after_row'),
        ));

        $cmb->add_field(array(
            'name' => 'Show button in',
            'id' => 'ar_model_viewer_for_woocommerce_btn',
            'type' => 'select',
            'default' => '6',
            'classes' => 'switch-field',
            'show_option_none' => true,
            'options' => array(
                '1' => __('woocommerce_before_single_product_summary', 'cmb2'),
                '2' => __('woocommerce_after_single_product_summary', 'cmb2'),
                '3' => __('woocommerce_before_single_product', 'cmb2'),
                '4' => __('woocommerce_after_single_product', 'cmb2'),
                '5' => __('woocommerce_after_add_to_cart_form', 'cmb2'),
                '6' => __('woocommerce_before_add_to_cart_form', 'cmb2'),
            ),
        ));

        $cmb->add_field(array(
            'name' => 'Show in Product Tabs',
            'id' => 'ar_model_viewer_for_woocommerce_single_product_tabs',
            'type' => 'radio_inline',
            'default' => 'no',
            'classes' => 'switch-field',
            'show_option_none' => false,
            'options' => array(
                'yes' => __('Yes', 'cmb2'),
                'no' => __('No', 'cmb2'),
            ),
        ));

        $cmb->add_field(array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Loading : Attributes',
            'desc' => '',
            'type' => 'title',
            'id' => 'ar_model_viewer_for_woocommerce_loading_title',
        ));

        $cmb->add_field(array(
            'name' => 'Loading',
            'id' => 'ar_model_viewer_for_woocommerce_loading',
            'type' => 'radio_inline',
            'desc' => 'An enumerable attribute describing under what conditions the model should be preloaded. The supported values are "auto", "lazy" and "eager". Auto is equivalent to lazy, which loads the model when it is near the viewport for reveal="auto", and when interacted with for reveal="interaction". Eager loads the model immediately.',
            'default' => '1',
            'classes' => 'switch-field',
            'show_option_none' => false,
            'options' => array(
                '1' => __('Auto', 'cmb2'),
                '2' => __('Lazy', 'cmb2'),
                '3' => __('Eager', 'cmb2'),
            ),
        ));

        $cmb->add_field(array(
            'name' => 'Reveal',
            'id' => 'ar_model_viewer_for_woocommerce_reveal',
            'type' => 'radio_inline',
            'desc' => 'This attribute controls when the model should be revealed. It currently supports three values: "auto", "interaction", and "manual". If reveal is set to "interaction", <model-viewer> will wait until the user interacts with the poster before loading and revealing the model. If reveal is set to "auto", the model will be revealed as soon as it is done loading and rendering. If reveal is set to "manual", the model will remain hidden until dismissPoster() is called.',
            'default' => '1',
            'classes' => 'switch-field',
            'show_option_none' => false,
            'options' => array(
                '1' => __('Auto', 'cmb2'),
                '2' => __('Interaction', 'cmb2'),
                '3' => __('Manual', 'cmb2'),
            ),
        ));

        $cmb->add_field(array(
            'name' => '--poster-color',
            'id' => 'ar_model_viewer_for_woocommerce_poster_color',
            'desc' => 'Sets the background-color of the poster . You may wish to set this to transparent if you are using a seamless poster with transparency (so that the background color of <model-viewer> shows through).',
            'type' => 'colorpicker',
            'default' => 'rgba(255,255,255,0)',
            'options' => array(
                'alpha' => true, // Make this a rgba color picker.
            ),
        ));

        $cmb->add_field(array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Augmented Reality : Attributes',
            'desc' => '',
            'type' => 'title',
            'id' => 'ar_title_2',
        ));

        $cmb->add_field(array(
            'name' => 'Enable AR',
            'id' => 'ar_model_viewer_for_woocommerce_ar',
            'type' => 'radio_inline',
            'default' => '1',
            'desc' => 'Enable the ability to launch AR experiences on supported devices.',
            'show_option_none' => false,
            'options' => array(
                '1' => __('Active', 'cmb2'),
                '2' => __('Deactivate', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        $cmb->add_field(array(
            'name' => 'AR Modes',
            'id' => 'ar_model_viewer_for_woocommerce_ar_modes',
            'type' => 'multicheck',
            'default' => '1,2',
            'desc' => 'A prioritized list of the types of AR experiences to enable. Allowed values are "webxr", to launch the AR experience in the browser, "scene-viewer", to launch the Scene Viewer app, "quick-look", to launch the iOS Quick Look app. Note that the presence of an ios-src will enable quick-look by itself.',
            'show_option_none' => false,
            'options' => array(
                '1' => __('webxr', 'cmb2'),
                '2' => __('scene-viewer', 'cmb2'),
                '3' => __('quick-look', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        $cmb->add_field(array(
            'name' => 'AR Scale',
            'id' => 'ar_model_viewer_for_woocommerce_ar_scale',
            'type' => 'radio_inline',
            'default' => '1',
            'desc' => 'Controls the scaling behavior in AR mode. Set to "fixed" to disable scaling of the model, which sets it to always be at 100% scale. Defaults to "auto" which allows the model to be resized by pinch.',
            'show_option_none' => false,
            'options' => array(
                '1' => __('Auto', 'cmb2'),
                '2' => __('Fixed', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        $cmb->add_field(array(
            'name' => 'AR Placement',
            'id' => 'ar_model_viewer_for_woocommerce_ar_placement',
            'type' => 'radio_inline',
            'default' => '1',
            'desc' => 'Selects whether to place the object on the floor (horizontal surface) or a wall (vertical surface) in AR. The back (negative Z) of the object´s bounding box will be placed against the wall and the shadow will be put on this surface as well. Note that the different AR modes handle the placement UX differently.',
            'show_option_none' => false,
            'options' => array(
                '1' => __('Floor', 'cmb2'),
                '2' => __('Wall', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        $cmb->add_field(array(
            'name' => 'XR-Environment',
            'id' => 'ar_model_viewer_for_woocommerce_xr_environment',
            'type' => 'radio_inline',
            'default' => '2',
            'desc' => 'Enables AR lighting estimation in WebXR mode; this has a performance cost and replaces the lighting selected with during an AR session. Known issues: sometimes too dark, sudden updates, shiny materials look matte.environment-image',
            'show_option_none' => false,
            'options' => array(
                '1' => __('Active', 'cmb2'),
                '2' => __('Deactive', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        $cmb->add_field(array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Augmented Reality : Slots',
            'desc' => '',
            'type' => 'title',
            'id' => 'ar_title_3',
            'before_row' => '<div id="cmb2-id-ar-model-viewer-for-woocommerce-ar-settings">',
            'after_row' => '</div>',
        ));

        $cmb->add_field(array(
            'name' => 'Custom AR Button',
            'id' => 'ar_model_viewer_for_woocommerce_ar_button',
            'type' => 'radio_inline',
            'desc' => 'By placing a child element under <model-viewer> with slot="ar-button", this element will replace the default "Enter AR" button, which is a <model-viewer> icon in the lower right. This button will be visible if AR is potentially available (we will have some false positives until the user tries).',
            'show_option_none' => false,
            'options' => array(
                '1' => __('Active', 'cmb2'),
                '2' => __('Deactive', 'cmb2'),
            ),
            'default' => '2',
            'classes' => 'switch-field',
        ));

        $cmb->add_field(array(
            'name' => 'Button Text',
            'desc' => '',
            'default' => '👋 Activate AR',
            'id' => 'ar_model_viewer_for_woocommerce_ar_button_text',
            'type' => 'text_medium',
        ));

        $cmb->add_field(array(
            'name' => 'Button Color',
            'id' => 'ar_model_viewer_for_woocommerce_ar_button_background_color',
            'type' => 'colorpicker',
            'default' => '#ffffff',
            // 'options' => array(
            //     'alpha' => true, // Make this a rgba color picker.
            // ),
        ));

        $cmb->add_field(array(
            'name' => 'Text Color',
            'id' => 'ar_model_viewer_for_woocommerce_ar_button_text_color',
            'type' => 'colorpicker',
            'default' => '#000000',
            // 'options' => array(
            //     'alpha' => true, // Make this a rgba color picker.
            // ),
        ));
    }

    public static function ar_model_viewer_for_woocommerce_cmb2_after_row($field_args, $field)
    {
        include_once 'partials/ar-model-viewer-for-woocommerce-admin-display.php';
    }

    public function ar_model_viewer_for_woocommerce_error_notice() {
        echo '<div class="notice notice-error is-dismissible"><p>'.__('AR Model Viewer for WooCommerce is active but not working. You need to install the WooCommerce plugin for the plugin to work properly.', 'datos-de-facturacion-para-mexico').'</p></div>';
    }

    public function ar_model_viewer_for_woocommerce_blocksy_fix($current_value){
        // Use WooCommerce built in gallery
        return true;
    }
}