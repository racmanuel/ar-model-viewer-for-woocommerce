<?php
/**
 * The admin-specific functionality of the settings screen.
 *
 * The screen is built with the WordPress Settings API instead of the previous metabox
 * library, so the plugin controls the markup, the validation and the saved array.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Registers, validates and renders the plugin settings screen.
 *
 * The screen keeps the original page slug and option name, so existing installs,
 * bookmarks, the Freemius menu entry and the assets enqueued for this screen keep
 * working without a data migration.
 *
 * @since      3.0.0
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
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
     * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of the plugin.
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
     * @access   private
     * @var      object    $logger    The variable to call WooCommerce class logger.
     */
    private $logger;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name      The name of this plugin.
     * @param    string    $plugin_prefix    The unique prefix of this plugin.
     * @param    string    $version          The version of this plugin.
     */
    public function __construct($plugin_name, $plugin_prefix, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_prefix = $plugin_prefix;
        $this->version = $version;
        $this->logger = new Ar_Model_Viewer_For_Woocommerce_Logger($plugin_name, $plugin_prefix, $version);
    }

    /* ---------------------------------------------------------------------
     * Hooks
     * ------------------------------------------------------------------ */

    /**
     * Register the settings page under the WordPress "Settings" menu.
     *
     * `add_options_page()` produces the `settings_page_{slug}` hook suffix, which is the
     * same suffix the previous implementation generated, so the admin assets keep loading.
     *
     * @since 3.0.0
     * @return void
     */
    public function register_settings_page()
    {
        add_options_page(
            esc_html__('AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce'),
            esc_html__('AR Model Viewer', 'ar-model-viewer-for-woocommerce'),
            Ar_Model_Viewer_For_Woocommerce_Settings::CAPABILITY,
            Ar_Model_Viewer_For_Woocommerce_Settings::PAGE_SLUG,
            array($this, 'render_page')
        );
    }

    /**
     * Register the option with the Settings API.
     *
     * The whole settings screen is stored as one array under the historical option name.
     * `show_in_rest` stays disabled on purpose: a REST endpoint for the settings is added
     * by the plugin itself, which keeps the API key out of the generic `/wp/v2/settings`
     * response.
     *
     * @since 3.0.0
     * @return void
     */
    public function register_settings()
    {
        register_setting(
            Ar_Model_Viewer_For_Woocommerce_Settings::GROUP,
            Ar_Model_Viewer_For_Woocommerce_Settings::OPTION_KEY,
            array(
                'type' => 'array',
                'description' => esc_html__('AR Model Viewer for WooCommerce settings.', 'ar-model-viewer-for-woocommerce'),
                'sanitize_callback' => array('Ar_Model_Viewer_For_Woocommerce_Settings', 'sanitize'),
                'default' => Ar_Model_Viewer_For_Woocommerce_Settings::defaults(),
                'show_in_rest' => false,
            )
        );
    }

    /**
     * Restore the default settings when the reset link is used.
     *
     * @since 3.0.0
     * @return void
     */
    public function handle_reset()
    {
        if (!isset($_GET['page'], $_GET['armvw-reset']) || !is_string($_GET['page'])) {
            return;
        }

        $page = sanitize_key(wp_unslash($_GET['page']));

        if (Ar_Model_Viewer_For_Woocommerce_Settings::PAGE_SLUG !== $page) {
            return;
        }

        if (!current_user_can(Ar_Model_Viewer_For_Woocommerce_Settings::CAPABILITY)) {
            return;
        }

        check_admin_referer('armvw_reset_settings');

        Ar_Model_Viewer_For_Woocommerce_Settings::reset();

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page' => Ar_Model_Viewer_For_Woocommerce_Settings::PAGE_SLUG,
                    'armvw-reset' => 'done',
                ),
                admin_url('options-general.php')
            )
        );
        exit;
    }

    /* ---------------------------------------------------------------------
     * Screen definition
     * ------------------------------------------------------------------ */

    /**
     * Describe the tabs of the settings screen and the fields they contain.
     *
     * @since 3.0.0
     * @return array<string, array<string, mixed>> Tabs keyed by tab slug.
     */
    public function tabs()
    {
        return array(
            'general' => array(
                'label' => esc_html__('General', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-admin-generic',
                'intro' => esc_html__('Choose where the 3D and AR experience appears inside your product pages.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('Placement', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-align-pull-left',
                        'desc' => esc_html__('The button and the product tab are independent: you can enable both, one of them, or none and use the shortcode instead.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_btn',
                            'ar_model_viewer_for_woocommerce_single_product_tabs',
                        ),
                    ),
                    array(
                        'title' => esc_html__('Labels', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-editor-textcolor',
                        'desc' => esc_html__('Texts shown to the shopper on the product page. They are translated by default and can be renamed per store.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_button_text',
                            'ar_model_viewer_for_woocommerce_tab_title',
                        ),
                    ),
                ),
            ),
            'loading' => array(
                'label' => esc_html__('Loading', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-performance',
                'intro' => esc_html__('Control when the model file is downloaded. Loading less data on first paint is the fastest way to speed up a product page.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('Loading and reveal', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-download',
                        'desc' => esc_html__('Leaving the loading condition in `auto` is the recommended default: the file is requested when the viewer approaches the viewport, so nothing is downloaded on pages the shopper never scrolls to.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_loading',
                            'ar_model_viewer_for_woocommerce_reveal',
                            'ar_model_viewer_for_woocommerce_with_credentials',
                        ),
                    ),
                ),
            ),
            'lighting' => array(
                'label' => esc_html__('Lighting', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-sun',
                'intro' => esc_html__('Decide how the model is lit and how it sits on the page. These values are the difference between a model that looks flat and one that looks like a product photo.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('Tone mapping and exposure', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-art',
                        'desc' => esc_html__('Tone mapping compresses the HDR render into the image a screen can show. The library default, `neutral`, is the one designed for accurate colours in shops; `aces` and `agx` come from film and tend to shift the hue of the product. Exposure brightens or darkens the model and the background together.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_tone_mapping',
                            'ar_model_viewer_for_woocommerce_exposure',
                        ),
                    ),
                    array(
                        'title' => esc_html__('Shadow', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-format-image',
                        'desc' => esc_html__('The viewer draws no shadow by default, which makes objects look like they are floating. Raising the intensity anchors the product to the page. Softer shadows are cheaper to render than hard ones.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_shadow_intensity',
                            'ar_model_viewer_for_woocommerce_shadow_softness',
                        ),
                    ),
                    array(
                        'title' => esc_html__('Environment', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-admin-site-alt3',
                        'desc' => esc_html__('The environment image is what the model reflects and the skybox image is what the shopper sees behind it. Without an environment the model is lit by a neutral light. HDR images (`.hdr`) give the best reflections, and a skybox is also used as the background.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_environment_image',
                            'ar_model_viewer_for_woocommerce_skybox_image',
                        ),
                    ),
                ),
            ),
            'interaction' => array(
                'label' => esc_html__('Interaction', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-move',
                'intro' => esc_html__('Control how the shopper moves the model. These settings have the largest impact on how the viewer feels on a phone, where a gesture that fights the page scroll ruins the experience.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('Camera controls', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-image-rotate',
                        'desc' => esc_html__('The camera controls are what let the shopper rotate the model with a finger or the mouse. Turning them off leaves a still image, which saves battery but also removes the reason to have a 3D viewer.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_camera_controls',
                            'ar_model_viewer_for_woocommerce_disable_zoom',
                            'ar_model_viewer_for_woocommerce_disable_pan',
                            'ar_model_viewer_for_woocommerce_disable_tap',
                            'ar_model_viewer_for_woocommerce_touch_action',
                            'ar_model_viewer_for_woocommerce_orbit_sensitivity',
                            'ar_model_viewer_for_woocommerce_zoom_sensitivity',
                            'ar_model_viewer_for_woocommerce_pan_sensitivity',
                            'ar_model_viewer_for_woocommerce_interpolation_decay',
                        ),
                    ),
                    array(
                        'title' => esc_html__('Auto rotate', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-update',
                        'desc' => esc_html__('The model turns on its own until the shopper touches it. It is a good way to show that the image can be moved, but it costs battery and draws attention on a page with several viewers.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_auto_rotate',
                            'ar_model_viewer_for_woocommerce_auto_rotate_delay',
                            'ar_model_viewer_for_woocommerce_rotation_per_second',
                        ),
                    ),
                    array(
                        'title' => esc_html__('Interaction prompt', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-info',
                        'desc' => esc_html__('The prompt is the hand icon the viewer shows over the model to hint that it can be moved. It appears only while the camera controls are enabled.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_interaction_prompt',
                            'ar_model_viewer_for_woocommerce_interaction_prompt_style',
                            'ar_model_viewer_for_woocommerce_interaction_prompt_threshold',
                        ),
                    ),
                ),
            ),
            'performance' => array(
                'label' => esc_html__('Performance', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-performance',
                'intro' => esc_html__('How the viewer spends the battery and the bandwidth of your visitor. These values are not printed in the HTML of the page: they configure the library itself, which is why they apply to every viewer on the site.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('Rendering', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-dashboard',
                        'desc' => esc_html__('Rendering a 3D model in real time is expensive on a phone, so the library measures the frame rate and lowers the resolution on its own when it drops. These options set those limits.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_minimum_render_scale',
                            'ar_model_viewer_for_woocommerce_power_preference',
                            'ar_model_viewer_for_woocommerce_model_cache_size',
                        ),
                    ),
                    array(
                        'title' => esc_html__('Decoders', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-download',
                        'desc' => esc_html__('Draco, KTX2 and Meshopt are the three ways a model can arrive compressed, and each one needs a decoder that the viewer only downloads when a model actually uses it. By default the library fetches them from a Google CDN, which means a request from the browser of your shopper to a third party that your privacy policy may need to declare. Leaving these fields empty keeps that default; filling them with files hosted on your own server removes the third party.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_draco_decoder_location',
                            'ar_model_viewer_for_woocommerce_ktx2_transcoder_location',
                            'ar_model_viewer_for_woocommerce_meshopt_decoder_location',
                        ),
                    ),
                ),
            ),
            'ar' => array(
                'label' => esc_html__('AR and XR', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-smartphone',
                'intro' => esc_html__('Configure the augmented reality experience available on Android and iOS devices.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('Augmented reality', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-visibility',
                        'desc' => esc_html__('These values are passed to the viewer as the matching `ar`, `ar-modes`, `ar-scale`, `ar-placement` and `xr-environment` attributes.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_ar',
                            'ar_model_viewer_for_woocommerce_ar_modes',
                            'ar_model_viewer_for_woocommerce_ar_scale',
                            'ar_model_viewer_for_woocommerce_ar_placement',
                            'ar_model_viewer_for_woocommerce_xr_environment',
                        ),
                    ),
                ),
            ),
            'appearance' => array(
                'label' => esc_html__('Appearance', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-art',
                'intro' => esc_html__('Match the viewer with the look and feel of your theme.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('Model background', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-format-image',
                        'desc' => esc_html__('Use a transparent background when the poster image has transparency.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_poster_color',
                        ),
                    ),
                    array(
                        'title' => esc_html__('Custom AR button', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-button',
                        'desc' => esc_html__('Replaces the default "Enter AR" icon with your own button. It is rendered inside the viewer with the `ar-button` slot.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_ar_button',
                            'ar_model_viewer_for_woocommerce_ar_button_text',
                            'ar_model_viewer_for_woocommerce_ar_button_background_color',
                            'ar_model_viewer_for_woocommerce_ar_button_text_color',
                        ),
                    ),
                ),
            ),
            'accessibility' => array(
                'label' => esc_html__('Accessibility', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-universal-access',
                'intro' => esc_html__('A 3D viewer is a canvas, and a canvas says nothing to a screen reader. These options give the model a description in words, so the experience is not lost for someone who cannot see it.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('Screen readers', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-visibility',
                        'desc' => esc_html__('When the shopper moves the model with the keyboard, the viewer announces which side of the product they are looking at, and how to move it. The library ships those sentences in English only: here they are generated from the translations of the plugin, so they follow the language of your site instead.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_a11y',
                        ),
                    ),
                ),
            ),
            'ai' => array(
                'label' => esc_html__('AI models', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-superhero',
                'intro' => esc_html__('Generate 3D models from a text prompt or a product image using meshy.ai.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('meshy.ai API key', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-admin-network',
                        'desc' => esc_html__('The key is stored in your database and only used by the plugin to call the meshy.ai API from your server. It is never printed on the public side of the site.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_api_key_meshy',
                        ),
                    ),
                ),
            ),
            'tools' => array(
                'label' => esc_html__('Tools', 'ar-model-viewer-for-woocommerce'),
                'icon' => 'dashicons-admin-tools',
                'intro' => esc_html__('Diagnostics and maintenance options.', 'ar-model-viewer-for-woocommerce'),
                'groups' => array(
                    array(
                        'title' => esc_html__('Diagnostics', 'ar-model-viewer-for-woocommerce'),
                        'icon' => 'dashicons-editor-code',
                        'desc' => esc_html__('Enable logging only while you are debugging: it writes one entry per viewer render, which is not desirable on a production site.', 'ar-model-viewer-for-woocommerce'),
                        'fields' => array(
                            'ar_model_viewer_for_woocommerce_logger',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Describe the label and help text of every setting.
     *
     * @since 3.0.0
     * @return array<string, array<string, mixed>> Field metadata keyed by option key.
     */
    public function fields()
    {
        return array(
            'ar_model_viewer_for_woocommerce_btn' => array(
                'label' => esc_html__('Show the 3D button in', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('WooCommerce hook where the "View in 3D" button is printed on a single product page. Leave it empty to hide the button completely.', 'ar-model-viewer-for-woocommerce'),
                'empty_label' => esc_html__('Do not show the button', 'ar-model-viewer-for-woocommerce'),
            ),
            'ar_model_viewer_for_woocommerce_single_product_tabs' => array(
                'label' => esc_html__('Product tab', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Adds a "View Product on 3D" tab to the product tabs list. Some themes hide custom tabs, so verify it after switching themes or page builders.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Enabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_button_text' => array(
                'label' => esc_html__('Button label', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Text of the button that opens the 3D viewer. Use something specific for your catalogue, like “See it in your room”.', 'ar-model-viewer-for-woocommerce'),
            ),
            'ar_model_viewer_for_woocommerce_tab_title' => array(
                'label' => esc_html__('Tab title', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Title of the product tab that contains the viewer.', 'ar-model-viewer-for-woocommerce'),
            ),
            'ar_model_viewer_for_woocommerce_loading' => array(
                'label' => esc_html__('Loading', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Condition used to preload the model file. The library documents `auto` and `lazy` as the same value, so only `auto` is offered: the file is requested once the viewer is near the viewport. `eager` downloads it immediately.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'auto' => esc_html__('Auto', 'ar-model-viewer-for-woocommerce'),
                    'eager' => esc_html__('Eager', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_reveal' => array(
                'label' => esc_html__('Reveal', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('When the model is revealed. `auto` shows it as soon as it is rendered. `manual` keeps the poster until your own code calls `dismissPoster()`, and with this value the file is only downloaded when that method runs, which is the cheapest option for product pages with several viewers. The shopper can always dismiss the poster by interacting with it.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'auto' => esc_html__('Auto', 'ar-model-viewer-for-woocommerce'),
                    'manual' => esc_html__('Manual (dismissPoster)', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_with_credentials' => array(
                'label' => esc_html__('With credentials', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Sends cookies and authorization headers when the model is fetched. Needed when the file lives on a server that requires authentication. It has no effect for local files.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Yes', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('No', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_poster_color' => array(
                'label' => esc_html__('Background color', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Background of the viewer canvas. Set it to transparent when the poster image uses transparency. Accepts hexadecimal and rgba() values.', 'ar-model-viewer-for-woocommerce'),
            ),
            'ar_model_viewer_for_woocommerce_tone_mapping' => array(
                'label' => esc_html__('Tone mapping', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Function used to convert the HDR render into the colours of the screen. Leave it on the library default unless you are matching an existing catalogue: `neutral` is the function designed for accurate product colours, `aces` and `agx` are film looks that change how colour is compressed, and `none` disables the conversion altogether.', 'ar-model-viewer-for-woocommerce'),
                'empty_label' => esc_html__('Library default (neutral)', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'aces' => esc_html__('ACES (film)', 'ar-model-viewer-for-woocommerce'),
                    'agx' => esc_html__('AgX (film)', 'ar-model-viewer-for-woocommerce'),
                    'reinhard' => esc_html__('Reinhard', 'ar-model-viewer-for-woocommerce'),
                    'cineon' => esc_html__('Cineon', 'ar-model-viewer-for-woocommerce'),
                    'linear' => esc_html__('Linear', 'ar-model-viewer-for-woocommerce'),
                    'none' => esc_html__('No tone mapping', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_exposure' => array(
                'label' => esc_html__('Exposure', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Brightness of the model and the background. Values below 1 darken the scene and above 1 brighten it, which is the quickest fix for a model that looks too dark compared to the product photos.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '1',
            ),
            'ar_model_viewer_for_woocommerce_shadow_intensity' => array(
                'label' => esc_html__('Shadow intensity', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Opacity of the shadow under the model, between 0 and 1. The viewer draws no shadow by default, so a value around 0.5 is usually what makes the product look grounded instead of floating.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '0',
            ),
            'ar_model_viewer_for_woocommerce_shadow_softness' => array(
                'label' => esc_html__('Shadow softness', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Blurriness of the shadow, between 0 and 1. Use 0 for a hard shadow and 1 for a diffuse one. Soft shadows render faster, so raising this value also helps the frame rate.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '1',
            ),
            'ar_model_viewer_for_woocommerce_environment_image' => array(
                'label' => esc_html__('Environment image', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Image the model reflects. Accepts the keywords `neutral` (plain studio light) and `legacy` (the environment of model-viewer v3), or a URL to an .hdr or .jpg file. Because it is an equirectangular image, a product on a white background is what gives the most predictable result.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => 'neutral',
            ),
            'ar_model_viewer_for_woocommerce_skybox_image' => array(
                'label' => esc_html__('Skybox image', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Background image of the scene, also used as the environment. Leave it empty to keep the plain background of the canvas: a skybox is only worth it when the model is shown in context, and it adds the weight of the image to every product page that shows it.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => 'https://example.com/studio.hdr',
            ),
            'ar_model_viewer_for_woocommerce_camera_controls' => array(
                'label' => esc_html__('Camera controls', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Enables mouse and touch interaction with the model. This is what turns a static image into something the shopper can inspect, so it is enabled by default. Turn it off for a decorative model that should not capture the scroll gesture.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Enabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_disable_zoom' => array(
                'label' => esc_html__('Disable zoom', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Blocks zooming with the mouse wheel and with a pinch gesture. The wheel then scrolls the page again, which is what a shopper usually expects on a product page.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Zoom disabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Zoom allowed', 'ar-model-viewer-for-woocommerce'),
                ),
                'depends' => 'camera_controls=yes',
            ),
            'ar_model_viewer_for_woocommerce_disable_pan' => array(
                'label' => esc_html__('Disable pan', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Blocks moving the model with a two finger drag, or with a right click drag on desktop. Useful when the model has to stay centred in its frame.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Panning disabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Panning allowed', 'ar-model-viewer-for-woocommerce'),
                ),
                'depends' => 'camera_controls=yes',
            ),
            'ar_model_viewer_for_woocommerce_disable_tap' => array(
                'label' => esc_html__('Disable tap to recentre', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Blocks the tap that re-centres the model on the tapped point. It has no effect while panning is disabled, because recentring belongs to the pan interaction. Once panning is available, a shopper cannot easily return to the starting view, so leaving this off is usually safer.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Recentring disabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Recentring allowed', 'ar-model-viewer-for-woocommerce'),
                ),
                'depends' => 'camera_controls=yes',
            ),
            'ar_model_viewer_for_woocommerce_touch_action' => array(
                'label' => esc_html__('Touch behaviour', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('How touch gestures over the viewer interact with page scrolling. `pan-y` lets the shopper scroll the page by dragging vertically and rotate the model by dragging horizontally, `pan-x` does the opposite and `none` blocks scrolling while the finger is over the model. The CSS value `auto` is not accepted here, and leaving the field empty is the same as `pan-y`.', 'ar-model-viewer-for-woocommerce'),
                'empty_label' => esc_html__('Library default (pan-y)', 'ar-model-viewer-for-woocommerce'),
                'depends' => 'camera_controls=yes',
            ),
            'ar_model_viewer_for_woocommerce_orbit_sensitivity' => array(
                'label' => esc_html__('Orbit sensitivity', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Multiplier for the rotation gesture, 1 by default. Negative values reverse the direction of the rotation, which is the documented way of looking at the inside of a model.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '1',
                'depends' => 'camera_controls=yes',
            ),
            'ar_model_viewer_for_woocommerce_zoom_sensitivity' => array(
                'label' => esc_html__('Zoom sensitivity', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Multiplier for the zoom gesture, 1 by default.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '1',
                'depends' => 'camera_controls=yes',
            ),
            'ar_model_viewer_for_woocommerce_pan_sensitivity' => array(
                'label' => esc_html__('Pan sensitivity', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Multiplier for the movement gesture, 1 by default.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '1',
                'depends' => 'camera_controls=yes',
            ),
            'ar_model_viewer_for_woocommerce_interpolation_decay' => array(
                'label' => esc_html__('Movement smoothing', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Milliseconds over which the camera and the model travel to their new position, 50 by default. Raising it makes the movement more cinematic and less responsive, lowering it makes it feel more immediate.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '50',
                'depends' => 'camera_controls=yes',
            ),
            'ar_model_viewer_for_woocommerce_a11y' => array(
                'label' => esc_html__('Describe the model to screen readers', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Adds the twelve orientations of the model and the text of the interaction prompt to the `a11y` attribute, so an assistive technology can name the side of the product the shopper is looking at. Without this attribute the viewer announces its directions in English whatever the language of the store, which is the reason it is enabled by default. The sentences come from the translation files of the plugin, so they improve as those are completed.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Enabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_minimum_render_scale' => array(
                'label' => esc_html__('Minimum render scale', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Floor of the resolution the library may drop to when the frame rate falls, 0.5 by default, and it climbs back on its own when the device recovers. Raise it towards 1 to keep every model sharp at the cost of battery, lower it when the model has to stay fluid on a low end phone. This is a limit, not a fixed value: the viewer still renders above it when it can.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '0.5',
            ),
            'ar_model_viewer_for_woocommerce_power_preference' => array(
                'label' => esc_html__('Power preference', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Which graphics processor the browser is asked for. `high-performance` is what the library requests by default and gives the smoothest movement while costing more battery, `low-power` prefers the integrated chip and is the polite choice for a store selling to laptops, and `default` lets the browser decide.', 'ar-model-viewer-for-woocommerce'),
                'empty_label' => esc_html__('Library default (high-performance)', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'high-performance' => esc_html__('High performance', 'ar-model-viewer-for-woocommerce'),
                    'low-power' => esc_html__('Low power', 'ar-model-viewer-for-woocommerce'),
                    'default' => esc_html__('Browser default', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_model_cache_size' => array(
                'label' => esc_html__('Models kept in cache', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('How many models the library keeps in memory at the same time, 5 by default. It matters on a page that shows several viewers: a higher value spares the shopper from downloading and parsing a model again when they open it twice. The cache is counted in models, not in megabytes, and a large model counts exactly like a small one.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '5',
            ),
            'ar_model_viewer_for_woocommerce_draco_decoder_location' => array(
                'label' => esc_html__('Draco decoder URL', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Draco is the most common way of compressing the geometry of a model, and the viewer needs its decoder only when a model uses it. Left empty the decoder is downloaded from a Google CDN; point this field to a copy on your own server to avoid the third party request. The URL is the folder that contains the decoder files, not the file itself, and it has no effect on models that are not compressed with Draco.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => 'https://example.com/decoders/draco/',
            ),
            'ar_model_viewer_for_woocommerce_ktx2_transcoder_location' => array(
                'label' => esc_html__('KTX2 transcoder URL', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('KTX2 is the compressed texture format of glTF, and it also needs a helper file that is downloaded from a Google CDN by default. Filling this field is the same idea as the Draco decoder: host the transcoder yourself and the browser of your shopper never talks to a third party to see your product.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => 'https://example.com/decoders/basis/',
            ),
            'ar_model_viewer_for_woocommerce_meshopt_decoder_location' => array(
                'label' => esc_html__('Meshopt decoder URL', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Meshopt is the third compression format the viewer understands, and it is the only one that is not enabled by default: an empty field means a model compressed with Meshopt will not open. Fill it only if your models come in that format.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => 'https://example.com/decoders/meshopt/',
            ),
            'ar_model_viewer_for_woocommerce_auto_rotate' => array(
                'label' => esc_html__('Auto rotate', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Makes the model turn on its own, which signals that the viewer is interactive. It stops as soon as the shopper interacts with it.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Enabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_auto_rotate_delay' => array(
                'label' => esc_html__('Auto rotate delay', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Milliseconds to wait before the rotation starts, 3000 by default. A short delay makes the movement obvious, a long one keeps the product still while the page is being read.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '3000',
                'depends' => 'auto_rotate=yes',
            ),
            'ar_model_viewer_for_woocommerce_rotation_per_second' => array(
                'label' => esc_html__('Rotation speed', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Speed of the rotation. Accepts degrees (`30deg`), radians (`0.5rad`) or a percentage of the default speed (`-100%`, where a negative value turns the other way). Leaving it empty keeps the library default of roughly one turn every 32 seconds.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '30deg',
                'depends' => 'auto_rotate=yes',
            ),
            'ar_model_viewer_for_woocommerce_interaction_prompt' => array(
                'label' => esc_html__('Interaction prompt', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('The prompt is the hand icon that hints the model can be moved. Set it to `none` to hide it completely.', 'ar-model-viewer-for-woocommerce'),
                'empty_label' => esc_html__('Library default (shown)', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'auto' => esc_html__('Show the prompt', 'ar-model-viewer-for-woocommerce'),
                    'none' => esc_html__('Hide the prompt', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_interaction_prompt_style' => array(
                'label' => esc_html__('Prompt style', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('`wiggle` animates the prompt and rocks the model with it, `basic` shows a static icon until the shopper interacts.', 'ar-model-viewer-for-woocommerce'),
                'empty_label' => esc_html__('Library default (wiggle)', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'wiggle' => esc_html__('Animated (wiggle)', 'ar-model-viewer-for-woocommerce'),
                    'basic' => esc_html__('Static (basic)', 'ar-model-viewer-for-woocommerce'),
                ),
                'depends' => 'interaction_prompt=auto',
            ),
            'ar_model_viewer_for_woocommerce_interaction_prompt_threshold' => array(
                'label' => esc_html__('Prompt delay', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Milliseconds the viewer waits before showing the prompt, 3000 by default. A shorter value gets the hint on screen sooner, a longer one keeps the canvas clean.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '3000',
                'depends' => 'interaction_prompt=auto',
            ),
            'ar_model_viewer_for_woocommerce_ar' => array(
                'label' => esc_html__('Augmented reality', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Enables the AR experience on supported devices. When disabled, the AR fields below stop applying.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Enabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_ar_modes' => array(
                'label' => esc_html__('AR modes', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Prioritized list of AR experiences: `webxr` launches AR in the browser, `scene-viewer` opens the Android app and `quick-look` opens the iOS app. At least one mode is always kept: deselecting every option restores the default list, because an empty `ar-modes` attribute is not valid. Note that `quick-look` without an .usdz file makes the device generate one on the fly, which adds a delay on iOS.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'webxr' => 'webxr',
                    'scene-viewer' => 'scene-viewer',
                    'quick-look' => 'quick-look',
                ),
                'depends' => 'ar=yes',
            ),
            'ar_model_viewer_for_woocommerce_ar_scale' => array(
                'label' => esc_html__('AR scale', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('`auto` lets the shopper resize the model with a pinch gesture; `fixed` keeps it at 100% of its real size.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'auto' => esc_html__('Auto', 'ar-model-viewer-for-woocommerce'),
                    'fixed' => esc_html__('Fixed', 'ar-model-viewer-for-woocommerce'),
                ),
                'depends' => 'ar=yes',
            ),
            'ar_model_viewer_for_woocommerce_ar_placement' => array(
                'label' => esc_html__('AR placement', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('`floor` places the object on a horizontal surface, `wall` places it on a vertical surface with its shadow.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'floor' => esc_html__('Floor', 'ar-model-viewer-for-woocommerce'),
                    'wall' => esc_html__('Wall', 'ar-model-viewer-for-woocommerce'),
                ),
                'depends' => 'ar=yes',
            ),
            'ar_model_viewer_for_woocommerce_xr_environment' => array(
                'label' => esc_html__('XR environment', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Lighting estimation in WebXR mode. It has a rendering cost, replaces the lighting of the environment image during the AR session and can make shiny materials look matte, so it is off by default. Enable it only when realism inside AR matters more than frame rate.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Enabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
                ),
                'depends' => 'ar=yes',
            ),
            'ar_model_viewer_for_woocommerce_ar_button' => array(
                'label' => esc_html__('Custom AR button', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Replaces the default AR icon of the viewer with a button you can style. It stays visible while AR is potentially available, which may include false positives until the shopper tries it.', 'ar-model-viewer-for-woocommerce'),
                'labels' => array(
                    'yes' => esc_html__('Enabled', 'ar-model-viewer-for-woocommerce'),
                    'no' => esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_model_viewer_for_woocommerce_ar_button_text' => array(
                'label' => esc_html__('Button text', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Label of the custom AR button. Emoji are allowed.', 'ar-model-viewer-for-woocommerce'),
                'depends' => 'ar_button=yes',
            ),
            'ar_model_viewer_for_woocommerce_ar_button_background_color' => array(
                'label' => esc_html__('Button background', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Background color of the custom AR button.', 'ar-model-viewer-for-woocommerce'),
                'depends' => 'ar_button=yes',
            ),
            'ar_model_viewer_for_woocommerce_ar_button_text_color' => array(
                'label' => esc_html__('Button text color', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Color of the label of the custom AR button.', 'ar-model-viewer-for-woocommerce'),
                'depends' => 'ar_button=yes',
            ),
            'ar_model_viewer_for_woocommerce_api_key_meshy' => array(
                'label' => esc_html__('API key', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Create your key in the meshy.ai dashboard and paste it here. Without it, the AI boxes are not added to the product editor.', 'ar-model-viewer-for-woocommerce'),
            ),
            'ar_model_viewer_for_woocommerce_logger' => array(
                'label' => esc_html__('Enable error logs', 'ar-model-viewer-for-woocommerce'),
                'desc' => esc_html__('Writes plugin events to WooCommerce > Status > Logs, using the source "ar-model-viewer-for-woocommerce". Use it only while troubleshooting.', 'ar-model-viewer-for-woocommerce'),
            ),
        );
    }

    /* ---------------------------------------------------------------------
     * Rendering
     * ------------------------------------------------------------------ */

    /**
     * Render the settings screen.
     *
     * @since 3.0.0
     * @return void
     */
    public function render_page()
    {
        if (!current_user_can(Ar_Model_Viewer_For_Woocommerce_Settings::CAPABILITY)) {
            wp_die(esc_html__('You do not have permission to access this page.', 'ar-model-viewer-for-woocommerce'));
        }

        $settings = $this;
        $fields = $this->fields();
        $tabs = $this->tabs();

        /**
         * Filters the tabs rendered on the settings screen.
         *
         * @since 3.0.0
         * @param array $tabs Tab definitions keyed by tab slug.
         */
        $tabs = apply_filters('armvw_settings_tabs', $tabs);

        include plugin_dir_path(__FILE__) . 'partials/ar-model-viewer-for-woocommerce-admin-display-settings.php';
    }

    /**
     * Render the tab list.
     *
     * @since 3.0.0
     * @param array $tabs Tab definitions keyed by tab slug.
     * @return void
     */
    public function render_tab_nav($tabs)
    {
        $first = true;
        ?>
        <nav class="armvw-tabs" role="tablist" aria-label="<?php echo esc_attr__('Settings sections', 'ar-model-viewer-for-woocommerce'); ?>">
            <?php foreach ($tabs as $slug => $tab) : ?>
                <button
                    type="button"
                    class="armvw-tab<?php echo $first ? ' is-active' : ''; ?>"
                    id="armvw-tab-<?php echo esc_attr($slug); ?>"
                    data-armvw-tab="<?php echo esc_attr($slug); ?>"
                    role="tab"
                    aria-controls="armvw-panel-<?php echo esc_attr($slug); ?>"
                    aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
                    tabindex="<?php echo $first ? '0' : '-1'; ?>"
                >
                    <span class="dashicons <?php echo esc_attr($tab['icon']); ?>" aria-hidden="true"></span>
                    <?php echo esc_html($tab['label']); ?>
                </button>
                <?php
                $first = false;
            endforeach;
            ?>
        </nav>
        <?php
    }

    /**
     * Render a single tab panel with its groups of fields.
     *
     * @since 3.0.0
     * @param string $slug  Tab slug.
     * @param array  $tab   Tab definition.
     * @param array  $fields Field metadata keyed by option key.
     * @param bool   $first Whether this is the initially visible panel.
     * @return void
     */
    public function render_panel($slug, $tab, $fields, $first)
    {
        ?>
        <section
            class="armvw-panel"
            id="armvw-panel-<?php echo esc_attr($slug); ?>"
            role="tabpanel"
            aria-labelledby="armvw-tab-<?php echo esc_attr($slug); ?>"
            tabindex="0"
            <?php echo $first ? '' : 'hidden'; ?>
        >
            <?php if (!empty($tab['intro'])) : ?>
                <p class="armvw-panel__intro"><?php echo esc_html($tab['intro']); ?></p>
            <?php endif; ?>

            <?php foreach ($tab['groups'] as $group) : ?>
                <div class="armvw-card">
                    <div class="armvw-card__header">
                        <?php if (!empty($group['icon'])) : ?>
                            <span class="dashicons <?php echo esc_attr($group['icon']); ?>" aria-hidden="true"></span>
                        <?php endif; ?>
                        <div>
                            <h2 class="armvw-card__title"><?php echo esc_html($group['title']); ?></h2>
                            <?php if (!empty($group['desc'])) : ?>
                                <p class="armvw-card__desc"><?php echo wp_kses_post($this->format_help($group['desc'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php
                    foreach ($group['fields'] as $key) {
                        if (isset($fields[$key])) {
                            $this->render_field($key, $fields[$key]);
                        }
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </section>
        <?php
    }

    /**
     * Render one setting row.
     *
     * @since 3.0.0
     * @param string $key  Option key.
     * @param array  $meta Field metadata.
     * @return void
     */
    public function render_field($key, array $meta)
    {
        $definitions = Ar_Model_Viewer_For_Woocommerce_Settings::definitions();

        if (!isset($definitions[$key])) {
            return;
        }

        $definition = $definitions[$key];
        $value = Ar_Model_Viewer_For_Woocommerce_Settings::get($key);
        $short = str_replace('ar_model_viewer_for_woocommerce_', '', $key);
        $id = 'armvw-' . str_replace('_', '-', $short);
        $label_id = $id . '-label';
        $desc_id = $id . '-desc';
        $name = Ar_Model_Viewer_For_Woocommerce_Settings::OPTION_KEY . '[' . $key . ']';

        // Radio groups and check lists are labelled through `aria-labelledby`, because their
        // DOM id belongs to the options, not to the group itself.
        $is_group = in_array($definition['type'], array('radio', 'checklist'), true);
        $label_for = $is_group ? '' : ' for="' . esc_attr($id) . '"';
        $depends = $this->dependency_attribute($meta);
        ?>
        <div class="armvw-field"<?php echo $depends; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attribute is escaped in the method. ?>>
            <div class="armvw-field__main">
                <label class="armvw-field__label" id="<?php echo esc_attr($label_id); ?>"<?php echo $label_for; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attribute is escaped above. ?>>
                    <?php echo esc_html($meta['label']); ?>
                </label>
                <?php if (!empty($meta['desc'])) : ?>
                    <p class="armvw-field__desc" id="<?php echo esc_attr($desc_id); ?>"><?php echo wp_kses_post($this->format_help($meta['desc'])); ?></p>
                <?php endif; ?>
            </div>
            <div class="armvw-field__control">
                <?php $this->render_control($key, $definition, $meta, $value, $id, $label_id, $desc_id, $name); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render the input that belongs to a field type.
     *
     * @since 3.0.0
     * @param string $key        Option key.
     * @param array  $definition Field definition.
     * @param array  $meta       Field metadata.
     * @param mixed  $value      Current value.
     * @param string $id         Base DOM id.
     * @param string $label_id   DOM id of the label.
     * @param string $desc_id    DOM id of the help text.
     * @param string $name       Value of the name attribute.
     * @return void
     */
    private function render_control($key, array $definition, array $meta, $value, $id, $label_id, $desc_id, $name)
    {
        $labels = isset($meta['labels']) ? $meta['labels'] : array();
        $described = ' aria-describedby="' . esc_attr($desc_id) . '"';

        switch ($definition['type']) {
            case 'select':
                ?>
                <select class="armvw-select" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>"<?php echo $described; ?>>
                    <?php if (!empty($definition['allow_empty'])) : ?>
                        <option value="" <?php selected($value, ''); ?>>
                            <?php echo esc_html(isset($meta['empty_label']) ? $meta['empty_label'] : esc_html__('None', 'ar-model-viewer-for-woocommerce')); ?>
                        </option>
                    <?php endif; ?>
                    <?php foreach ($definition['choices'] as $choice) : ?>
                        <option value="<?php echo esc_attr($choice); ?>" <?php selected($value, $choice); ?>>
                            <?php echo esc_html(isset($labels[$choice]) ? $labels[$choice] : $choice); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php
                break;

            case 'radio':
                ?>
                <fieldset class="armvw-switch" role="radiogroup" aria-labelledby="<?php echo esc_attr($label_id); ?>"<?php echo $described; ?>>
                    <legend class="armvw-visually-hidden"><?php echo esc_html($meta['label']); ?></legend>
                    <?php foreach ($definition['choices'] as $choice) : ?>
                        <label class="armvw-switch__item">
                            <input
                                type="radio"
                                id="<?php echo esc_attr($id . '-' . $choice); ?>"
                                name="<?php echo esc_attr($name); ?>"
                                value="<?php echo esc_attr($choice); ?>"
                                <?php checked($value, $choice); ?>
                            />
                            <span><?php echo esc_html(isset($labels[$choice]) ? $labels[$choice] : $choice); ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <?php
                break;

            case 'checklist':
                $selected = is_array($value) ? $value : array();
                ?>
                <fieldset class="armvw-chips" aria-labelledby="<?php echo esc_attr($label_id); ?>"<?php echo $described; ?>>
                    <legend class="armvw-visually-hidden"><?php echo esc_html($meta['label']); ?></legend>
                    <?php foreach ($definition['choices'] as $choice) : ?>
                        <label class="armvw-chips__item">
                            <input
                                type="checkbox"
                                id="<?php echo esc_attr($id . '-' . $choice); ?>"
                                name="<?php echo esc_attr($name); ?>[]"
                                value="<?php echo esc_attr($choice); ?>"
                                <?php checked(in_array($choice, $selected, true)); ?>
                            />
                            <span><?php echo esc_html(isset($labels[$choice]) ? $labels[$choice] : $choice); ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <?php
                break;

            case 'color':
                $alpha = !empty($definition['alpha']);
                ?>
                <div class="armvw-color-row">
                    <input
                        type="text"
                        class="color-picker armvw-input"
                        id="<?php echo esc_attr($id); ?>"
                        name="<?php echo esc_attr($name); ?>"
                        value="<?php echo esc_attr(is_string($value) ? $value : ''); ?>"
                        data-type="full"
                        data-alpha-enabled="<?php echo $alpha ? 'true' : 'false'; ?>"
                        data-alpha-color-type="rgba"
                        <?php echo $described; ?>
                    />
                </div>
                <?php
                break;

            case 'password':
                ?>
                <div class="armvw-secret">
                    <input
                        type="password"
                        class="armvw-input"
                        id="<?php echo esc_attr($id); ?>"
                        name="<?php echo esc_attr($name); ?>"
                        value="<?php echo esc_attr(is_string($value) ? $value : ''); ?>"
                        autocomplete="off"
                        spellcheck="false"
                        <?php echo $described; ?>
                    />
                    <button
                        type="button"
                        class="button armvw-reveal"
                        data-armvw-target="<?php echo esc_attr($id); ?>"
                        data-armvw-show-label="<?php echo esc_attr__('Show the API key', 'ar-model-viewer-for-woocommerce'); ?>"
                        data-armvw-hide-label="<?php echo esc_attr__('Hide the API key', 'ar-model-viewer-for-woocommerce'); ?>"
                        aria-pressed="false"
                        aria-label="<?php echo esc_attr__('Show the API key', 'ar-model-viewer-for-woocommerce'); ?>"
                    >
                        <span class="dashicons dashicons-visibility armvw-reveal__show" aria-hidden="true"></span>
                        <span class="dashicons dashicons-hidden armvw-reveal__hide" aria-hidden="true"></span>
                    </button>
                </div>
                <?php
                break;

            case 'checkbox':
                ?>
                <label class="armvw-checkbox" for="<?php echo esc_attr($id); ?>">
                    <input
                        type="checkbox"
                        id="<?php echo esc_attr($id); ?>"
                        name="<?php echo esc_attr($name); ?>"
                        value="1"
                        <?php checked(!empty($value)); ?>
                        <?php echo $described; ?>
                    />
                    <span><?php echo esc_html__('Write events to the WooCommerce log while debugging.', 'ar-model-viewer-for-woocommerce'); ?></span>
                </label>
                <?php
                break;

            case 'number':
                $placeholder = isset($meta['placeholder']) ? $meta['placeholder'] : '';
                $bounds = '';

                foreach (array('min', 'max', 'step') as $bound) {
                    if (isset($definition[$bound])) {
                        $bounds .= ' ' . $bound . '="' . esc_attr($definition[$bound]) . '"';
                    }
                }
                ?>
                <input
                    type="number"
                    class="armvw-input armvw-input--number"
                    id="<?php echo esc_attr($id); ?>"
                    name="<?php echo esc_attr($name); ?>"
                    value="<?php echo esc_attr(is_string($value) ? $value : ''); ?>"
                    inputmode="decimal"
                    <?php echo $bounds; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the loop above. ?>
                    <?php echo '' !== $placeholder ? ' placeholder="' . esc_attr($placeholder) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the expression. ?>
                    <?php echo $described; ?>
                />
                <?php
                break;

            case 'text':
            case 'resource':
            default:
                $placeholder = isset($meta['placeholder']) ? $meta['placeholder'] : '';
                ?>
                <input
                    type="text"
                    class="armvw-input"
                    id="<?php echo esc_attr($id); ?>"
                    name="<?php echo esc_attr($name); ?>"
                    value="<?php echo esc_attr(is_string($value) ? $value : ''); ?>"
                    <?php echo '' !== $placeholder ? ' placeholder="' . esc_attr($placeholder) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the expression. ?>
                    <?php echo $described; ?>
                />
                <?php
                break;
        }
    }

    /**
     * Render the preview card that sits next to the hero.
     *
     * @since 3.0.0
     * @return void
     */
    public function render_preview_card()
    {
        ?>
        <div class="armvw-card armvw-card--preview">
            <div class="armvw-card__header">
                <span class="dashicons dashicons-welcome-view-site" aria-hidden="true"></span>
                <div>
                    <h2 class="armvw-card__title"><?php echo esc_html__('Preview', 'ar-model-viewer-for-woocommerce'); ?></h2>
                    <p class="armvw-card__desc"><?php echo esc_html__('Click the poster to load the demo model. It is rendered with the AR attributes selected above; save your changes to refresh it.', 'ar-model-viewer-for-woocommerce'); ?></p>
                </div>
            </div>
            <div class="armvw-preview">
                <?php echo $this->preview_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is escaped in the method. ?>
                <button type="button" class="armvw-preview__load" id="armvw-preview-load">
                    <img
                        class="armvw-preview__poster"
                        src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'admin/images/armvw-logo-400.png'); ?>"
                        alt="<?php echo esc_attr__('Preview of the 3D model viewer', 'ar-model-viewer-for-woocommerce'); ?>"
                    />
                    <span class="armvw-preview__hint">
                        <?php echo esc_html__('Load the 3D preview', 'ar-model-viewer-for-woocommerce'); ?>
                    </span>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render the status and resource cards shown below the form.
     *
     * @since 3.0.0
     * @return void
     */
    public function render_sidebar()
    {
        $links = $this->resources();

        /**
         * Filters the resource links shown below the settings form.
         *
         * @since 3.0.0
         * @param array $links Resource links.
         */
        $links = apply_filters('armvw_settings_resources', $links);
        ?>
        <div class="armvw-card">
            <div class="armvw-card__header">
                <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                <div>
                    <h2 class="armvw-card__title"><?php echo esc_html__('Status', 'ar-model-viewer-for-woocommerce'); ?></h2>
                </div>
            </div>
            <ul class="armvw-status">
                <?php foreach ($this->status_items() as $item) : ?>
                    <li>
                        <span class="armvw-status__label"><?php echo esc_html($item['label']); ?></span>
                        <span class="armvw-status__value">
                            <span class="armvw-chip armvw-chip--<?php echo esc_attr($item['state']); ?>">
                                <?php echo esc_html($item['value']); ?>
                            </span>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="armvw-card">
            <div class="armvw-card__header">
                <span class="dashicons dashicons-sos" aria-hidden="true"></span>
                <div>
                    <h2 class="armvw-card__title"><?php echo esc_html__('Help and resources', 'ar-model-viewer-for-woocommerce'); ?></h2>
                </div>
            </div>
            <ul class="armvw-links">
                <?php foreach ($links as $link) : ?>
                    <li>
                        <a class="armvw-link" href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer">
                            <span class="dashicons <?php echo esc_attr($link['icon']); ?>" aria-hidden="true"></span>
                            <?php echo esc_html($link['label']); ?>
                            <span class="dashicons dashicons-external armvw-link__external" aria-hidden="true"></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="armvw-card armvw-pro">
            <div class="armvw-card__header">
                <span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
                <div>
                    <h2 class="armvw-card__title"><?php echo esc_html__('Go further with Pro', 'ar-model-viewer-for-woocommerce'); ?></h2>
                </div>
            </div>
            <ul class="armvw-feature-list">
                <li><?php echo esc_html__('Bulk import and export of 3D models with the native WooCommerce importer.', 'ar-model-viewer-for-woocommerce'); ?></li>
                <li><?php echo esc_html__('Generate a model from a product image with meshy.ai.', 'ar-model-viewer-for-woocommerce'); ?></li>
                <li><?php echo esc_html__('Elementor widget to place the viewer anywhere.', 'ar-model-viewer-for-woocommerce'); ?></li>
                <li><?php echo esc_html__('Priority support and early access to new features.', 'ar-model-viewer-for-woocommerce'); ?></li>
            </ul>
            <a class="button button-primary" href="<?php echo esc_url(admin_url('options-general.php?page=' . Ar_Model_Viewer_For_Woocommerce_Settings::PAGE_SLUG . '-pricing')); ?>">
                <?php echo esc_html__('See the plans', 'ar-model-viewer-for-woocommerce'); ?>
            </a>
        </div>

        <div class="armvw-card armvw-card--custom">
            <div class="armvw-card__header">
                <span class="dashicons dashicons-businessman" aria-hidden="true"></span>
                <div>
                    <h2 class="armvw-card__title"><?php echo esc_html__('Need a custom development?', 'ar-model-viewer-for-woocommerce'); ?></h2>
                </div>
            </div>
            <p class="armvw-card__desc">
                <?php echo esc_html__('I am Manuel, a WordPress developer. If you need something the plugin does not do yet, tell me about your project and I will send you a personalised quote.', 'ar-model-viewer-for-woocommerce'); ?>
            </p>
            <ul class="armvw-feature-list">
                <li><?php echo esc_html__('Custom viewer behaviour and theme integration.', 'ar-model-viewer-for-woocommerce'); ?></li>
                <li><?php echo esc_html__('Bulk workflows for large catalogues.', 'ar-model-viewer-for-woocommerce'); ?></li>
                <li><?php echo esc_html__('Integrations with your 3D, PIM or ERP stack.', 'ar-model-viewer-for-woocommerce'); ?></li>
            </ul>
            <a class="button button-primary" href="https://racmanuel.dev" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html__('Request a quote', 'ar-model-viewer-for-woocommerce'); ?>
            </a>
        </div>
        <?php
    }

    /**
     * Build the list of status entries shown in the sidebar.
     *
     * @since 3.0.0
     * @return array<int, array<string, string>> Status entries.
     */
    public function status_items()
    {
        $settings = Ar_Model_Viewer_For_Woocommerce_Settings::viewer_options();
        $items = array();

        $items[] = array(
            'label' => esc_html__('meshy.ai API key', 'ar-model-viewer-for-woocommerce'),
            'value' => Ar_Model_Viewer_For_Woocommerce_Settings::has_api_key()
                ? esc_html__('Configured', 'ar-model-viewer-for-woocommerce')
                : esc_html__('Missing', 'ar-model-viewer-for-woocommerce'),
            'state' => Ar_Model_Viewer_For_Woocommerce_Settings::has_api_key() ? 'ok' : 'off',
        );

        $items[] = array(
            'label' => esc_html__('Augmented reality', 'ar-model-viewer-for-woocommerce'),
            'value' => $settings['ar']
                ? esc_html__('Enabled', 'ar-model-viewer-for-woocommerce')
                : esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
            'state' => $settings['ar'] ? 'ok' : 'off',
        );

        $items[] = array(
            'label' => esc_html__('AR modes', 'ar-model-viewer-for-woocommerce'),
            'value' => implode(', ', is_array($settings['ar_modes']) ? $settings['ar_modes'] : array()),
            'state' => !empty($settings['ar_modes']) ? 'ok' : 'warn',
        );

        $items[] = array(
            'label' => esc_html__('3D button', 'ar-model-viewer-for-woocommerce'),
            'value' => $this->button_position_label(),
            'state' => Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_btn') ? 'ok' : 'off',
        );

        $items[] = array(
            'label' => esc_html__('Product tab', 'ar-model-viewer-for-woocommerce'),
            'value' => 'yes' === Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_single_product_tabs')
                ? esc_html__('Enabled', 'ar-model-viewer-for-woocommerce')
                : esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
            'state' => 'yes' === Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_single_product_tabs') ? 'ok' : 'off',
        );

        $items[] = array(
            'label' => esc_html__('Debug log', 'ar-model-viewer-for-woocommerce'),
            'value' => Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_logger')
                ? esc_html__('Enabled', 'ar-model-viewer-for-woocommerce')
                : esc_html__('Disabled', 'ar-model-viewer-for-woocommerce'),
            'state' => Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_logger') ? 'warn' : 'off',
        );

        return $items;
    }

    /**
     * Human readable label of the selected button position.
     *
     * @since 3.0.0
     * @return string Position label.
     */
    private function button_position_label()
    {
        $positions = array(
            '' => esc_html__('Hidden', 'ar-model-viewer-for-woocommerce'),
            '1' => 'woocommerce_before_single_product_summary',
            '2' => 'woocommerce_after_single_product_summary',
            '3' => 'woocommerce_before_single_product',
            '4' => 'woocommerce_after_single_product',
            '5' => 'woocommerce_after_add_to_cart_form',
            '6' => 'woocommerce_before_add_to_cart_form',
        );

        $current = Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_btn');

        return isset($positions[$current]) ? $positions[$current] : $positions[''];
    }

    /**
     * Build the markup of the demo viewer shown in the sidebar.
     *
     * @since 3.0.0
     * @return string The escaped `model-viewer` markup.
     */
    private function preview_markup()
    {
        $settings = Ar_Model_Viewer_For_Woocommerce_Settings::viewer_options();
        $attributes = '';

        if ($settings['ar']) {
            $attributes .= ' ar ar-modes="' . esc_attr(implode(' ', (array) $settings['ar_modes'])) . '"';
            $attributes .= ' ar-scale="' . esc_attr($settings['scale']) . '"';
            $attributes .= ' ar-placement="' . esc_attr($settings['placement']) . '"';

            if ($settings['xr_environment']) {
                $attributes .= ' xr-environment';
            }
        }

        $button = '';

        if ($settings['ar_button'] && '' !== trim((string) $settings['ar_button_text'])) {
            $button = sprintf(
                '<button slot="ar-button" style="background-color:%1$s;color:%2$s;border:none;border-radius:999px;padding:8px 14px;cursor:pointer;">%3$s</button>',
                esc_attr($settings['ar_button_background_color']),
                esc_attr($settings['ar_button_text_color']),
                esc_html($settings['ar_button_text'])
            );
        }

        /*
         * The library and the demo model are only requested after the visitor asks for the
         * preview, so this one loads eagerly: with a lazy condition the click would look like it
         * did nothing until the file arrives.
         */
        return sprintf(
            '<model-viewer src="%1$s" poster="%2$s" alt="%3$s" loading="eager" reveal="auto" style="background-color:%4$s;"%5$s%6$s>%7$s</model-viewer>',
            esc_url(plugin_dir_url(dirname(__FILE__)) . 'admin/models/witch_potion.glb'),
            esc_url(plugin_dir_url(dirname(__FILE__)) . 'admin/images/armvw-logo-400.png'),
            esc_attr__('Preview of the 3D model viewer', 'ar-model-viewer-for-woocommerce'),
            esc_attr($settings['poster_color']),
            $attributes,
            // The preview shows what the storefront renders, so it reads the shared attributes
            // from the same method the public templates use.
            Ar_Model_Viewer_For_Woocommerce_Settings::render_attributes(
                Ar_Model_Viewer_For_Woocommerce_Settings::shared_attributes()
            ),
            $button
        );
    }

    /**
     * Build the resource links shown in the sidebar.
     *
     * @since 3.0.0
     * @return array<int, array<string, string>> Resource links.
     */
    private function resources()
    {
        return array(
            array(
                'label' => esc_html__('Documentation', 'ar-model-viewer-for-woocommerce'),
                'url' => 'https://racmanuel.dev/plugins-wordpress/ar-model-viewer-for-woocommerce/',
                'icon' => 'dashicons-book-alt',
            ),
            array(
                'label' => esc_html__('Support forum', 'ar-model-viewer-for-woocommerce'),
                'url' => 'https://wordpress.org/support/plugin/ar-model-viewer-for-woocommerce/',
                'icon' => 'dashicons-format-chat',
            ),
            array(
                'label' => esc_html__('Rate the plugin', 'ar-model-viewer-for-woocommerce'),
                'url' => 'https://wordpress.org/support/plugin/ar-model-viewer-for-woocommerce/reviews/?rate=5#new-post',
                'icon' => 'dashicons-star-half',
            ),
            array(
                'label' => esc_html__('meshy.ai dashboard', 'ar-model-viewer-for-woocommerce'),
                'url' => 'https://app.meshy.ai/?via=racmanuel',
                'icon' => 'dashicons-admin-network',
            ),
            array(
                'label' => esc_html__('Request a custom quote', 'ar-model-viewer-for-woocommerce'),
                'url' => 'https://racmanuel.dev',
                'icon' => 'dashicons-editor-help',
            ),
        );
    }

    /**
     * Allow a limited subset of HTML inside the help texts.
     *
     * Help texts are written by the plugin, but they are rendered through
     * `wp_kses_post()` anyway. Backticks are converted into `<code>` so the accepted
     * attribute values stay readable without hand written markup.
     *
     * @since 3.0.0
     * @param string $text Help text.
     * @return string Text with the allowed markup.
     */
    private function format_help($text)
    {
        return preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    }

    /**
     * Build the `data-armvw-depends` attribute of a field.
     *
     * Field definitions use the short key for readability, but the inputs are named after
     * the full option key, so the key is expanded here. `admin-settings-ui.js` reads the
     * attribute to hide the fields whose condition is not met.
     *
     * @since 3.0.0
     * @param array $meta Field metadata.
     * @return string The attribute, or an empty string when the field has no condition.
     */
    private function dependency_attribute(array $meta)
    {
        if (empty($meta['depends'])) {
            return '';
        }

        $parts = array_pad(explode('=', $meta['depends'], 2), 2, '');

        return ' data-armvw-depends="' . esc_attr($this->long_key($parts[0]) . '=' . $parts[1]) . '"';
    }

    /**
     * Expand a short field key into the full option key.
     *
     * @since 3.0.0
     * @param string $short Short field key, for example `ar_button`.
     * @return string The full option key, for example `ar_model_viewer_for_woocommerce_ar_button`.
     */
    private function long_key($short)
    {
        return 'ar_model_viewer_for_woocommerce_' . $short;
    }

    /* ---------------------------------------------------------------------
     * Legacy handlers
     * ------------------------------------------------------------------ */

    /**
     * Legacy AJAX handler kept until the REST controllers replace it.
     *
     * @since 1.0.0
     * @deprecated 3.0.0 Use the REST endpoint of the plugin instead.
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_get_model_preview_with_global_settings()
    {
        if (!current_user_can(Ar_Model_Viewer_For_Woocommerce_Settings::CAPABILITY)) {
            wp_send_json_error('Insufficient permissions.', 403);
        }

        check_ajax_referer('armvw_admin', 'nonce', false);

        $settings = Ar_Model_Viewer_For_Woocommerce_Settings::viewer_options();

        $this->logger->log_to_woocommerce('Global settings retrieved successfully.', 'info');

        wp_send_json_success(
            array_merge(
                $settings,
                array(
                    'model_3d_file' => esc_url(plugin_dir_url(dirname(__FILE__)) . 'admin/models/witch_potion.glb'),
                    'model_alt' => 'AR Model Viewer for WooCommerce',
                    'model_poster' => esc_url(plugin_dir_url(dirname(__FILE__)) . 'admin/images/armvw-logo-400.png'),
                )
            )
        );
    }
}
