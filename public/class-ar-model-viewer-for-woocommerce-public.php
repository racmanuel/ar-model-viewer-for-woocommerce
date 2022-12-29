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
        wp_enqueue_script('jquery-ui-dialog');
    }

    /**
     * Example of Shortcode processing function.
     *
     * Shortcode can take attributes like [ar-model-viewer-for-woocommerce-shortcode attribute='123']
     * Shortcodes can be enclosing content [ar-model-viewer-for-woocommerce-shortcode attribute='123']custom content[/ar-model-viewer-for-woocommerce-shortcode].
     *
     * @see https://developer.wordpress.org/plugins/shortcodes/enclosing-shortcodes/
     *
     * @since    1.0.0
     * @param    array  $atts    ShortCode Attributes.
     * @param    mixed  $content ShortCode enclosed content.
     * @param    string $tag    The Shortcode tag.
     */
    public function ar_model_viewer_for_woocommerce_shortcode_func($atts, $content = null, $tag)
    {

        /**
         * Combine user attributes with known attributes.
         *
         * @see https://developer.wordpress.org/reference/functions/shortcode_atts/
         *
         * Pass third paramter $shortcode to enable ShortCode Attribute Filtering.
         * @see https://developer.wordpress.org/reference/hooks/shortcode_atts_shortcode/
         */
        $atts = shortcode_atts(
            array(
                'attribute' => 123,
            ),
            $atts,
            $this->plugin_prefix . 'shortcode'
        );

        /**
         * Build our ShortCode output.
         * Remember to sanitize all user input.
         * In this case, we expect a integer value to be passed to the ShortCode attribute.
         *
         * @see https://developer.wordpress.org/themes/theme-security/data-sanitization-escaping/
         */
        $out = intval($atts['attribute']);

        /**
         * If the shortcode is enclosing, we may want to do something with $content
         */
        if (!is_null($content) && !empty($content)) {
            $out = do_shortcode($content); // We can parse shortcodes inside $content.
            $out = intval($atts['attribute']) . ' ' . sanitize_text_field($out); // Remember to sanitize your user input.
        }

        // ShortCodes are filters and should always return, never echo.

        return $out;

    }
    public function ar_model_viewer_for_woocommerce_button()
    {
        // Global product variable
        global $product;

        //Get the file url for android
        $get_android_file = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_android', true);
        //Get the fiel url for IOS
        $get_ios_file = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_ios', true);
        //Get the alt for web accessibility
        $get_alt = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_alt', true);
        //Get the Poster
        $get_poster = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_poster', true);

        // Check if the customs fields has a value.
        if (!empty($get_android_file)) {
            $android_file_url = $get_android_file;
        }
        if (!empty($get_ios_file)) {
            $ios_file_url = $get_ios_file;
        }
        if (!empty($get_alt)) {
            $alt_description = sanitize_text_field($get_alt);
        } else {
            $alt_description = $product->get_name();
        }
        if (!empty($get_poster)) {
            $poster_file_url = $get_poster;
        } else {
            $poster_file_url = wp_get_attachment_url($product->get_image_id());
        }

        /**
         * If product not have a 3D Model - Hide the button
         */
        if (!empty($android_file_url) & !empty($ios_file_url)) {
            /**
             * Get the CMB2 Options or plugin options
             */
            $ar_model_viewer_settings = get_option('ar_model_viewer_for_woocommerce_settings');

            /**
             * Get the Loading Type from plugin settings
             * @see: https://modelviewer.dev/docs/#entrydocs-loading-attributes-loading
             */
            $loading_type = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_loading'];
            /**
             * Check the value of $loading_type and return the $loading_type
             * @param string $loading_type
             */
            $this->ar_model_viewer_for_woocommerce_loading_type($loading_type);

            /**
             * Get th Reveal Type from plugin settings
             * @see: https://modelviewer.dev/docs/#entrydocs-loading-attributes-reveal
             */
            $reveal_type = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_reveal'];
            /**
             * Check the value of $reveal_type and return the $reveal_type
             * @param string $reveal_type
             */
            $this->ar_model_viewer_for_woocommerce_reveal_type($reveal_type);

            /**
             * Get the --poster-color from plugin settings
             * @see: https://modelviewer.dev/docs/#entrydocs-loading-attributes-reveal
             */
            $poster_color_type = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_poster_color'];
            /**
             * Check the value of $poster_color_type and return the $poster_color_type
             * @param string $poster_color_type
             */
            $this->ar_model_viewer_for_woocommerce_poster_color($poster_color_type);

            /**
             * AR Settings
             */

            /**
             * Get the --poster-color from plugin settings
             * @see: https://modelviewer.dev/docs/#entrydocs-augmentedreality-attributes-ar
             */
            $ar_active = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_ar'];
            /**
             * Check the value of $ar_active and return the $ar_active
             * @param string $ar_active
             */
            $this->ar_model_viewer_for_woocommerce_ar($ar_active);

            /**
             * Get the ar-modes from plugin settings
             * @see: https://modelviewer.dev/docs/#entrydocs-augmentedreality-attributes-arModes
             */
            $ar_mode = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_ar_modes'];
            /**
             * Check the value of $ar_active and return the $ar_active
             * @param string $ar_active
             */
            $this->ar_model_viewer_for_woocommerce_ar_modes($ar_mode);

            /**
             * Get the ar scale from plugin settings
             * @see: https://modelviewer.dev/docs/index.html#entrydocs-augmentedreality-attributes-arScale
             */
            $ar_scale = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_ar_scale'];
            /**
             * Check the value of $ar_scale and return the $ar_scale
             * @param string $ar_scale
             */
            $this->ar_model_viewer_for_woocommerce_ar_scale($ar_scale);

            /**
             * Get the ar placement from plugin settings
             * @see: https://modelviewer.dev/docs/index.html#entrydocs-augmentedreality-attributes-arPlacement
             */
            $ar_placement = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_ar_placement'];
            /**
             * Check the value of $ar_placement and return the $ar_placement
             * @param string $ar_placement
             */
            $this->ar_model_viewer_for_woocommerce_ar_placement($ar_placement);

            /**
             * Get the xr_enviroment from plugin settings
             * @see: https://modelviewer.dev/docs/index.html#entrydocs-augmentedreality-attributes-xrEnvironment
             */
            $xr_enviroment = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_xr_environment'];
            /**
             * Check the value of xr_enviroment and return the $xr_enviroment
             * @param string $xr_enviroment
             */
            $this->ar_model_viewer_for_woocommerce_ar_xr_environment($xr_enviroment);

            /**
             * AR Button Settings
             */

            /**
             * Get the custom btn option from plugin settings
             * @see: https://modelviewer.dev/docs/#entrydocs-augmentedreality-slots-arButton
             */
            $ar_btn_custom = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_ar_button'];
            /**
             * Check ar button custom is active
             */
            $this->ar_model_viewer_for_woocommerce_ar_btn_custom($ar_btn_custom);

            // Get the custom text btn
            $ar_btn_custom_text = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_ar_button_text'];
            // Get the custom backgrund btn
            $ar_btn_custom_background = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_ar_button_background_color'];
            // Get the custom text color btn
            $ar_btn_custom_text_color = $ar_model_viewer_settings['ar_model_viewer_for_woocommerce_ar_button_text_color'];

            //Include the HTML for display the modal and the HTML content with a lilte bit PHP
            include_once 'partials/ar-model-viewer-for-woocommerce-public-display-button.php';
        }
    }

    public function ar_model_viewer_for_woocommerce_tab($tabs)
    {
        // Adds the new tab
        $tabs['ar_model_viewer'] = array(
            'title' => __('View Product on 3D', 'woocommerce'),
            'priority' => 50,
            'callback' => array('Ar_Model_Viewer_For_Woocommerce_Public', 'ar_model_viewer_for_woocommerce_tab_content'),
        );
        return $tabs;
    }

    public static function ar_model_viewer_for_woocommerce_tab_content()
    {
        // Global product variable
        global $product;

        //Get the file url for android
        $get_android_file = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_android', true);
        //Get the fiel url for IOS
        $get_ios_file = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_ios', true);
        //Get the alt for web accessibility
        $get_alt = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_alt', true);
        //Get the Poster
        $get_poster = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_poster', true);

        // Check if the customs fields has a value.
        if (!empty($get_android_file)) {
            $android_file_url = $get_android_file;
        }
        if (!empty($get_ios_file)) {
            $ios_file_url = $get_ios_file;
        }
        if (!empty($get_alt)) {
            $alt_description = sanitize_text_field($get_alt);
        } else {
            $alt_description = $product->get_name();
        }
        if (!empty($get_poster)) {
            $poster_file_url = $get_poster;
        } else {
            $poster_file_url = wp_get_attachment_url($product->get_image_id());
        }
        include 'partials/ar-model-viewer-for-woocommerce-public-display.php';
    }

    public function ar_model_viewer_for_woocommerce_loading_type($loading)
    {
        switch ($loading) {
            case 1:
                # code...
                return 'loading="auto"';
                break;
            case 2:
                # code...
                return 'loading="lazy"';
                break;
            case 3:
                # code...
                return 'loading="eager"';
                break;
            default:
                # code...
                $loading = '';
                return $loading;
                break;
        }
    }

    public function ar_model_viewer_for_woocommerce_reveal_type($reveal)
    {
        switch ($reveal) {
            case 1:
                # code...
                return 'reveal="auto"';
                break;
            case 2:
                # code...
                return 'reveal="interaction"';
                break;
            case 3:
                # code...
                return 'reveal="manual"';
                break;
            default:
                # code...
                return $reveal;
                break;
        }
    }

    public function ar_model_viewer_for_woocommerce_poster_color($poster_color)
    {
        if (isset($poster_color)) {
            return $poster_color;
        } else {
            $poster_color = 'transparent';
            return $poster_color;
        }
    }

    public function ar_model_viewer_for_woocommerce_ar($ar)
    {
        if (isset($ar) & $ar == 1) {
            return 'ar';
        } else {
            return '';
        }
    }

    public function ar_model_viewer_for_woocommerce_ar_modes($ar_mode)
    {
        foreach ($ar_mode as $mode_for_ar) {
            $mode = $mode_for_ar;
            if($mode == 1){
                $mode_webxr = 'webxr';
            }
            if($mode == 2){
                $mode_scene = 'scene-viewer';
            }
            if($mode == 3){
                $mode_quick = 'quick-look';
            }
        }
        $ar_mode = $mode_webxr . ' ' . $mode_scene . ' ' . $mode_quick;

        return $ar_mode;
    }

    public function ar_model_viewer_for_woocommerce_ar_scale($scale)
    {
        switch ($scale) {
            case 1:
                # code...
                return 'ar-scale="auto"';
                break;
            case 2:
                # code...
                return 'ar-scale="fixed"';
                break;
            default:
                # code...
                return $scale;
                break;
        }
    }

    public function ar_model_viewer_for_woocommerce_ar_placement($placement)
    {
        switch ($placement) {
            case 1:
                # code...
                return 'ar-placement="floor"';
                break;
            case 2:
                return 'ar-placement="wall"';
                break;
            default:
                # code...
                return $placement;
                break;
        }
    }

    public function ar_model_viewer_for_woocommerce_ar_xr_environment($xr)
    {
        switch ($xr) {
            case 1:
                # code...
                return 'xr-environment';
                break;
            case 2:
                # code...
                return '';
                break;
            default:
                # code...
                return $xr;
                break;
        }
    }

    public function ar_model_viewer_for_woocommerce_ar_btn_custom($btn_custom)
    {
        if ($btn_custom == 1) {
            return true;
        } else {
            return false;
        }
    }
}
