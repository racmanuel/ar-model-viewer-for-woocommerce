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
 * The admin-specific functionality in Settings of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the admin-facing stylesheet and JavaScript.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 * @author     Manuel Ramirez Coronel <ra_cm@outlook.com>
 */
class Ar_Model_Viewer_For_Woocommerce_Admin_Settings
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
     * Define the metabox and field configurations.
     */
    public function ar_model_viewer_for_woocommerce_cmb2_settings()
    {
        /**
         * Registers options page menu item and form.
         */
        $options = new_cmb2_box(array(
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
        $options->add_field(array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> View Settings',
            'desc' => '',
            'type' => 'title',
            'id' => 'wiki_test_title',
            'before_row' => array('Ar_Model_Viewer_For_Woocommerce_Admin_Settings', 'ar_model_viewer_for_woocommerce_cmb2_after_row'),
        ));

        $options->add_field(array(
            'name' => 'Show button in',
            'id' => 'ar_model_viewer_for_woocommerce_btn',
            'type' => 'select',
            'default' => '2',
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

        $options->add_field(array(
            'name' => 'Show in Product Tabs',
            'id' => 'ar_model_viewer_for_woocommerce_single_product_tabs',
            'type' => 'radio_inline',
            'default' => 'yes',
            'classes' => 'switch-field',
            'show_option_none' => false,
            'options' => array(
                'yes' => __('Yes', 'cmb2'),
                'no' => __('No', 'cmb2'),
            ),
        ));

        $options->add_field(array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Loading : Attributes',
            'desc' => '',
            'type' => 'title',
            'id' => 'ar_model_viewer_for_woocommerce_loading_title',
        ));

        $options->add_field(array(
            'name' => 'Loading',
            'id' => 'ar_model_viewer_for_woocommerce_loading',
            'type' => 'radio_inline',
            'desc' => 'An enumerable attribute describing under what conditions the model should be preloaded. The supported values are "auto", "lazy" and "eager". Auto is equivalent to lazy, which loads the model when it is near the viewport for reveal="auto", and when interacted with for reveal="interaction". Eager loads the model immediately.',
            'default' => 'auto',
            'classes' => 'switch-field',
            'show_option_none' => false,
            'options' => array(
                'auto' => __('Auto', 'cmb2'),
                'lazy' => __('Lazy', 'cmb2'),
                'eager' => __('Eager', 'cmb2'),
            ),
        ));

        $options->add_field(array(
            'name' => 'Reveal',
            'id' => 'ar_model_viewer_for_woocommerce_reveal',
            'type' => 'radio_inline',
            'desc' => 'This attribute controls when the model should be revealed. It currently supports three values: "auto", "interaction", and "manual". If reveal is set to "interaction", will wait until the user interacts with the poster before loading and revealing the model. If reveal is set to "auto", the model will be revealed as soon as it is done loading and rendering. If reveal is set to "manual", the model will remain hidden until dismissPoster() is called.',
            'default' => 'auto',
            'classes' => 'switch-field',
            'show_option_none' => false,
            'options' => array(
                'auto' => __('Auto', 'cmb2'),
                'manual' => __('Manual', 'cmb2'),
            ),
        ));

        $options->add_field(array(
            'name' => 'With Credentials',
            'id' => 'ar_model_viewer_for_woocommerce_with_credentials',
            'type' => 'radio_inline',
            'desc' => 'This attribute makes the browser include credentials (cookies, authorization headers or TLS client certificates) in the request to fetch the 3D model. Its useful if the 3D model file is stored on another server that require authentication. By default the file will be fetch without credentials. Note that this has no effect if you are loading files locally or from the same domain.',
            'default' => 'false',
            'classes' => 'switch-field',
            'show_option_none' => false,
            'options' => array(
                'false' => __('False', 'cmb2'),
                'true' => __('True', 'cmb2'),
            ),
        ));

        $options->add_field(array(
            'name' => 'Background Color',
            'id' => 'ar_model_viewer_for_woocommerce_poster_color',
            'desc' => 'Sets the background-color of the model 3D . You may wish to set this to transparent if you are using a seamless poster with transparency (so that the background color of model shows through).',
            'type' => 'colorpicker',
            'default' => '#FFFFFF',
            'options' => array(
                'alpha' => true, // Make this a rgba color picker.
            ),
        ));

        $options->add_field(array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Augmented Reality : Attributes',
            'desc' => '',
            'type' => 'title',
            'id' => 'ar_title_2',
        ));

        $options->add_field(array(
            'name' => 'Enable AR',
            'id' => 'ar_model_viewer_for_woocommerce_ar',
            'type' => 'radio_inline',
            'default' => 'active',
            'desc' => 'Enable the ability to launch AR experiences on supported devices.',
            'show_option_none' => false,
            'options' => array(
                'active' => __('Active', 'cmb2'),
                'deactivate' => __('Deactivate', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        $options->add_field(array(
            'name' => 'AR Modes',
            'id' => 'ar_model_viewer_for_woocommerce_ar_modes',
            'type' => 'multicheck',
            'default' => array('webxr', 'scene-viewer', 'quick-look'),
            'desc' => 'A prioritized list of the types of AR experiences to enable. Allowed values are "webxr", to launch the AR experience in the browser, "scene-viewer", to launch the Scene Viewer app, "quick-look", to launch the iOS Quick Look app. Note that the presence of an ios-src will enable quick-look by itself.',
            'show_option_none' => false,
            'options' => array(
                'webxr' => __('webxr', 'cmb2'),
                'scene-viewer' => __('scene-viewer', 'cmb2'),
                'quick-look' => __('quick-look', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        $options->add_field(array(
            'name' => 'AR Scale',
            'id' => 'ar_model_viewer_for_woocommerce_ar_scale',
            'type' => 'radio_inline',
            'default' => 'auto',
            'desc' => 'Controls the scaling behavior in AR mode. Set to "fixed" to disable scaling of the model, which sets it to always be at 100% scale. Defaults to "auto" which allows the model to be resized by pinch.',
            'show_option_none' => false,
            'options' => array(
                'auto' => __('Auto', 'cmb2'),
                'fixed' => __('Fixed', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        function ar_default_placement()
        {
            return 'floor'; // Return the intended default value
        }

        $options->add_field(array(
            'name' => 'AR Placement',
            'id' => 'ar_model_viewer_for_woocommerce_ar_placement',
            'type' => 'radio_inline',
            'default_cb' => 'ar_default_placement', // Use a custom callback
            'desc' => 'Selects whether to place the object on the floor (horizontal surface) or a wall (vertical surface) in AR. The back (negative Z) of the object´s bounding box will be placed against the wall and the shadow will be put on this surface as well. Note that the different AR modes handle the placement UX differently.',
            'show_option_none' => false,
            'options' => array(
                'floor' => __('Floor', 'cmb2'),
                'wall' => __('Wall', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        $options->add_field(array(
            'name' => 'XR-Environment',
            'id' => 'ar_model_viewer_for_woocommerce_xr_environment',
            'type' => 'radio_inline',
            'default' => 'active',
            'desc' => 'Enables AR lighting estimation in WebXR mode; this has a performance cost and replaces the lighting selected with during an AR session. Known issues: sometimes too dark, sudden updates, shiny materials look matte.environment-image',
            'show_option_none' => false,
            'options' => array(
                'active' => __('Active', 'cmb2'),
                'deactive' => __('Deactive', 'cmb2'),
            ),
            'classes' => 'switch-field',
        ));

        $options->add_field(array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Augmented Reality : Slots',
            'desc' => '',
            'type' => 'title',
            'id' => 'ar_title_3',
        ));

        $options->add_field(array(
            'name' => 'Custom AR Button',
            'id' => 'ar_model_viewer_for_woocommerce_ar_button',
            'type' => 'radio_inline',
            'desc' => 'By placing a child element under model-viewer with slot="ar-button", this element will replace the default "Enter AR" button, which is a model-viewer icon in the lower right. This button will be visible if AR is potentially available (we will have some false positives until the user tries).',
            'show_option_none' => false,
            'options' => array(
                'active' => __('Active', 'cmb2'),
                'deactive' => __('Deactive', 'cmb2'),
            ),
            'default' => 'deactive',
            'classes' => 'switch-field',
        ));

        $options->add_field(array(
            'name' => 'Button Text',
            'desc' => '',
            'default' => '👋 Activate AR',
            'id' => 'ar_model_viewer_for_woocommerce_ar_button_text',
            'type' => 'text_medium',
        ));

        $options->add_field(array(
            'name' => 'Button Color',
            'id' => 'ar_model_viewer_for_woocommerce_ar_button_background_color',
            'type' => 'colorpicker',
            'default' => '#ffffff',
            // 'options' => array(
            //     'alpha' => true, // Make this a rgba color picker.
            // ),
        ));

        $options->add_field(array(
            'name' => 'Text Color',
            'id' => 'ar_model_viewer_for_woocommerce_ar_button_text_color',
            'type' => 'colorpicker',
            'default' => '#000000',
            // 'options' => array(
            //     'alpha' => true, // Make this a rgba color picker.
            // ),
        ));

        $options->add_field(array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> API Key to meshy.ai',
            'desc' => '',
            'type' => 'title',
            'id' => 'ar_title_6',
        ));

        $options->add_field(array(
            'name' => 'API Key',
            'desc' => 'Insert your API Key to start generating stunning 3D models effortlessly.',
            'default' => '',
            'id' => 'ar_model_viewer_for_woocommerce_api_key_meshy',
            'type' => 'text',
            'attributes' => array(
                'type' => 'password',
            ),
        ));

        do_action('ar_model_viewer_for_woocommerce_settings', $options);

        $options->add_field(array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Tools',
            'desc' => '',
            'type' => 'title',
            'id' => 'ar_title_5',
        ));

        $options->add_field(array(
            'name' => 'Enable Error Logs',
            'desc' => 'Check this box to enable error logs only for debug. Enabling this option allows you to view error logs in the WooCommerce admin menu by navigating to <strong>WooCommerce &gt; Status &gt; Logs &gt; Sources</strong> and selecting <strong>ar-model-viewer-for-woocommerce</strong>. This is useful for identifying and fixing issues during development or testing.',
            'id' => 'ar_model_viewer_for_woocommerce_logger',
            'type' => 'checkbox',
            'default' => true, // Set the checkbox to be checked by default
        ));
    }

    /**
     * Handles the retrieval of 3D model settings and product details via AJAX request.
     *
     * This function is triggered by an AJAX request to get the 3D model settings and product details.
     * It verifies the validity of the product ID, retrieves global settings, and prepares the data for response.
     *
     * @since    1.0.0
     * @return   void
     */
    public function ar_model_viewer_for_woocommerce_get_model_preview_with_global_settings()
    {

        // Log successful retrieval of settings
        $this->logger->log_to_woocommerce('Global settings retrieved successfully.', 'info'); // Log info

        // Retrieve individual settings
        $settings = $this->get_ar_model_viewer_settings();

        // Retrieve product metadata
        $model_alt = 'AR Model Viewer for WooCommerce';
        $model_poster = $this->get_model_poster_or_fallback();
        $model_3d_file = $this->get_model_3d_file_or_fallback();

        // Prepare data for response
        $data = array_merge($settings, [
            'model_3d_file' => $model_3d_file,
            'model_alt' => $model_alt,
            'model_poster' => $model_poster,
        ]);

        // Send JSON response and log success
        $this->logger->log_to_woocommerce("Successfully prepared 3D model data.", 'info'); // Log success
        wp_send_json_success($data);
        wp_die();
    }

    public function ar_model_viewer_for_woocommerce_get_model_to_preview_in_settings()
    {
        // Log successful retrieval of settings
        $this->logger->log_to_woocommerce('Global settings retrieved successfully.', 'info'); // Log info

        // Retrieve individual settings
        $settings = $this->get_ar_model_viewer_settings();

        // Retrieve product metadata
        $model_alt = 'AR Model Viewer for WooCommerce';
        $model_poster = plugin_dir_url(__DIR__) . '/admin/images/armvw-logo-transparent-original.png';
        $model_3d_file = plugin_dir_url(__DIR__) . '/admin/models/witch_potion.glb';

        // Initialize AR attributes based on the retrieved settings.
        $ar_attributes = '';
        if ($settings['ar'] === 'active') {
            $ar_attributes .= 'ar ar-modes="' . esc_attr(implode(' ', $settings['ar_modes'])) . '" ';
            if ($settings['scale']) {
                $ar_attributes .= 'ar-scale="' . esc_attr($settings['scale']) . '" ';
            }
            if ($settings['placement']) {
                $ar_attributes .= 'ar-placement="' . esc_attr($settings['placement']) . '" ';
            }
            if ($settings['xr_environment'] === 'active') {
                $ar_attributes .= 'xr-environment ';
            }
        }

        // Generate the HTML for the model-viewer element with all attributes and settings.
        $output = sprintf(
            '<model-viewer src="%1$s" alt="%2$s" poster="%3$s" loading="%4$s" reveal="%5$s" style="background-color: %6$s;" camera-controls auto-rotate %7$s></model-viewer>',
            esc_url($model_3d_file),
            esc_attr($model_alt),
            esc_url($model_poster),
            esc_attr($settings['loading']),
            esc_attr($settings['reveal']),
            esc_attr($settings['poster_color']),
            $ar_attributes
        );

        echo $output;
    }

    /**
     * Retrieves AR model viewer settings from the options page.
     *
     * This function gets the AR model viewer settings configured in the options page using CMB2.
     *
     * @since    1.0.0
     * @return   array    An array of AR model viewer settings.
     */
    private function get_ar_model_viewer_settings()
    {
        return [
            // Determines how the model loads (e.g., "auto" means it starts loading immediately)
            'loading' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_loading', 'auto'),

            // Controls when the model should be revealed, such as after a specific action or automatically
            'reveal' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_reveal', 'auto'),

            // Indicates whether the model viewer should send credentials such as cookies when making network requests
            'with_credentials' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_with_credentials', 'false'),

            // Sets the color of the poster background for the model viewer (default is transparent white)
            'poster_color' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_poster_color', 'rgba(255,255,255,0)'),

            // Determines if the AR feature should be enabled or not (e.g., "active" enables AR functionality)
            'ar' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar', 'active'),

            // Defines which AR modes are supported, such as "webxr" or "scene-viewer" (these determine how the AR experience is presented)
            'ar_modes' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_modes', ['webxr', 'scene-viewer', 'quick-look']),

            // Defines the scaling behavior of the model in AR (e.g., "auto" lets the viewer decide the best scale)
            'scale' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_scale', 'auto'),

            // Sets the placement of the model in AR (e.g., "floor" means the model is placed on the floor)
            'placement' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_placement', 'floor'),

            // Indicates whether an XR (Extended Reality) environment is used (e.g., "deactive" means no XR environment)
            'xr_environment' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_xr_environment', 'active'),
        ];
    }

    /**
     * Retrieves the 3D model poster URL or falls back to the default placeholder image.
     *
     * This function is designed to retrieve the URL of the 3D model poster image.
     * If the poster image is not found or doesn't exist, it returns a default
     * placeholder image instead. In case there is an issue retrieving the image,
     * the function logs an error using the WooCommerce logger.
     *
     * @since 1.0.0
     * @param int $product_id The product ID (Currently not used in this function but can be extended later).
     * @return string The URL of the poster image or the fallback placeholder image.
     */
    private function get_model_poster_or_fallback()
    {
        // Define the default 3D model poster image URL (fallback)
        $model_poster = plugin_dir_url(__FILE__) . 'images/armvw-logo-transparent-original.png';

        // If the model poster URL is not set, log an error and return an empty string
        if (!$model_poster) {
            // Logs an error in WooCommerce logs if the poster image cannot be retrieved
            $this->logger->log_to_woocommerce('Poster not found to add to alt attribute.', 'error');
            return ''; // Return an empty string as a fallback if no poster is found
        }

        // If the model poster URL exists, return it
        return $model_poster;
    }

    /**
     * Retrieves the 3D model file for the product or falls back to a default file.
     *
     * This function tries to get the 3D model file URL for a product. If the 3D model file
     * is not available, it returns a default model file located in the 'admin/models/' directory.
     * If the model file cannot be retrieved, it logs an error using the WooCommerce logger.
     *
     * @since 1.0.0
     * @return string The URL of the 3D model file or an empty string if the file is missing.
     */
    public function get_model_3d_file_or_fallback()
    {
        // Define the default 3D model file URL
        $model_3d_file = plugin_dir_url(__FILE__) . 'models/witch_potion.glb';

        // If the model file URL is not valid, log an error and return an empty string
        if (!$model_3d_file) {
            // Log an error in WooCommerce if the 3D model file is not found
            $this->logger->log_to_woocommerce("3D Model File not found.", 'error');
            return ''; // Return an empty string if no file is found
        }

        // Return the 3D model file URL
        return $model_3d_file;
    }

    public static function ar_model_viewer_for_woocommerce_cmb2_after_row($field_args, $field)
    {
        $preview = new Ar_Model_Viewer_For_Woocommerce_Admin_Settings('', '', '');
        include_once 'partials/ar-model-viewer-for-woocommerce-admin-display-settings.php';
    }
}
