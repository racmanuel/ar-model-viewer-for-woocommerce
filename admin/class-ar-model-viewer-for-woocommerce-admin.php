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
     * Build a cache busting version for an asset.
     *
     * The file modification time is used so browsers keep the file cached until it really
     * changes. Paths are resolved both relative to this file and to the plugin root, and the
     * plugin version is used as a fallback when the file cannot be inspected.
     *
     * @since 3.0.0
     * @param string $relative_path Path of the asset relative to this file or to the plugin root.
     * @return string Version string, safe for a URL query argument.
     */
    private function asset_version($relative_path)
    {
        $paths = array(
            plugin_dir_path(__FILE__) . $relative_path,
            plugin_dir_path(dirname(__FILE__)) . $relative_path,
        );

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return (string) filemtime($path);
            }
        }

        return $this->version;
    }

    /**
     * Return the URL of every third party library available to the scripts.
     *
     * The files live in `assets/vendor` and are refreshed with `npm run vendors`. Libraries
     * that are missing are simply not published, so no screen breaks because of them.
     *
     * @since 3.0.0
     * @return array<string, string> Library URLs keyed by vendor name.
     */
    private function vendor_files()
    {
        $files = array();

        foreach (array('model-viewer', 'alertify', 'driver', 'tabulator') as $vendor) {
            $url = $this->vendor_url($vendor . '.min.js');

            if ($url) {
                $files[$vendor] = $url;
            }
        }

        return $files;
    }

    /**
     * Return the URL of a bundled third party library.
     *
     * Returns an empty string when the file is missing, so every screen keeps working
     * without it.
     *
     * @since 3.0.0
     * @param string $file File name inside the `assets/vendor` folder.
     * @return string Asset URL, or an empty string.
     */
    private function vendor_url($file)
    {
        $relative = 'assets/vendor/' . $file;

        if (!file_exists(plugin_dir_path(dirname(__FILE__)) . $relative)) {
            return '';
        }

        return plugin_dir_url(dirname(__FILE__)) . $relative;
    }

    /**
     * Return the CSS that loads the bundled DM Sans variable font.
     *
     * The files are the latin subsets published by Fontsource and copied by
     * `npm run vendors`. Each face declares its unicode range, so a browser only downloads
     * the subset the page needs, and the pair is four times smaller than the full font.
     *
     * @since 3.0.0
     * @return string The `@font-face` rules.
     */
    private function font_face_css()
    {
        $base = plugin_dir_url(dirname(__FILE__)) . 'assets/vendor/fonts/';

        $faces = array(
            array(
                'file' => 'dm-sans-latin.woff2',
                'range' => 'U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD',
            ),
            array(
                'file' => 'dm-sans-latin-ext.woff2',
                'range' => 'U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF',
            ),
        );

        $css = '';

        foreach ($faces as $face) {
            // Single quotes on purpose: inside a double quoted string PHP would interpolate
            // the `$s` of the `%1$s` placeholders and break the format.
            $css .= sprintf(
                '@font-face{font-family:\'DM Sans Variable\';font-style:normal;font-display:swap;font-weight:100 1000;src:url(\'%1$s\') format(\'woff2-variations\');unicode-range:%2$s;}',
                esc_url_raw($base . $face['file']),
                $face['range']
            );
        }

        return $css;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_styles($hook_suffix)
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $is_settings_screen = 'settings_page_' . Ar_Model_Viewer_For_Woocommerce_Settings::PAGE_SLUG === $hook_suffix;
        // `base` is `post` on both the product editor and the "Add new product" screen,
        // which keeps the product list screen free of plugin assets.
        $is_product_editor = $screen && 'product' === $screen->post_type && 'post' === $screen->base;

        if (!$is_settings_screen && !$is_product_editor) {
            return;
        }

        // The design tokens are shared by both screens.
        $tokens_handle = $this->plugin_name . '-tokens';

        wp_enqueue_style(
            $tokens_handle,
            plugin_dir_url(__FILE__) . 'css/ar-model-viewer-for-woocommerce-admin-tokens.css',
            array(),
            $this->asset_version('css/ar-model-viewer-for-woocommerce-admin-tokens.css'),
            'all'
        );

        if ($is_settings_screen) {
            /*
             * DM Sans is only used on the settings screen, and only the subset the page needs
             * is downloaded: the font files live in assets/vendor/fonts.
             */
            wp_add_inline_style($tokens_handle, $this->font_face_css());

            wp_enqueue_style(
                $this->plugin_name . '-settings',
                plugin_dir_url(__FILE__) . 'css/ar-model-viewer-for-woocommerce-admin-settings.css',
                array($tokens_handle),
                $this->asset_version('css/ar-model-viewer-for-woocommerce-admin-settings.css'),
                'all'
            );
        }

        if ($is_product_editor) {
            wp_enqueue_style(
                $this->plugin_name . '-product',
                plugin_dir_url(__FILE__) . 'css/ar-model-viewer-for-woocommerce-admin-product.css',
                array($tokens_handle),
                $this->asset_version('css/ar-model-viewer-for-woocommerce-admin-product.css'),
                'all'
            );
        }
    }

    /**
     * Add a body class on the screens owned by the plugin.
     *
     * The admin styles are scoped with these classes, so the plugin never leaks styles
     * into other admin pages.
     *
     * @since 3.0.0
     * @param string $classes Space separated list of body classes.
     * @return string The filtered list of body classes.
     */
    public function admin_body_class($classes)
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (!$screen) {
            return $classes;
        }

        if ('settings_page_' . Ar_Model_Viewer_For_Woocommerce_Settings::PAGE_SLUG === $screen->id) {
            $classes .= ' armvw-settings-page';
        }

        if ('product' === $screen->post_type && 'post' === $screen->base) {
            $classes .= ' armvw-product-editor';
        }

        return $classes;
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
        if ('settings_page_' . Ar_Model_Viewer_For_Woocommerce_Settings::PAGE_SLUG === $hook_suffix) {
            /*
             * The model-viewer library weighs around 1 MB, so it is not enqueued here: the
             * preview card shows the poster and `-settings.js` injects the library when the
             * visitor asks for the demo. The `<model-viewer>` element upgrades itself as soon
             * as the library is loaded.
             */
            wp_enqueue_script(
                $this->plugin_name . '-settings',
                plugin_dir_url(__FILE__) . 'js/ar-model-viewer-for-woocommerce-settings.js',
                array(),
                $this->asset_version('js/ar-model-viewer-for-woocommerce-settings.js'),
                array(
                    'in_footer' => true,
                    'strategy' => 'defer',
                )
            );

            wp_localize_script(
                $this->plugin_name . '-settings',
                'armvwSettings',
                array(
                    'viewer_url' => $this->vendor_url('model-viewer.min.js'),
                    // The render scale, the power preference, the cache size and the decoder
                    // locations are static properties of the element, so the script has to apply
                    // them instead of printing them in the markup.
                    'static_properties' => Ar_Model_Viewer_For_Woocommerce_Settings::static_properties(),
                )
            );

            // Extends the WordPress color picker with alpha channel support.
            wp_register_script(
                'wp-color-picker-alpha',
                plugin_dir_url(__FILE__) . 'js/wp-color-picker-alpha.js',
                array('wp-color-picker'),
                $this->asset_version('js/wp-color-picker-alpha.js'),
                true
            );

            wp_add_inline_script(
                'wp-color-picker-alpha',
                'jQuery( function() { jQuery( ".color-picker" ).wpColorPicker(); } );'
            );

            wp_enqueue_script('wp-color-picker-alpha');
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if ($screen && 'product' === $screen->post_type && 'post' === $screen->base) {
            // The file fields of the metabox open the WordPress media library, so the scripts
            // that build the modal are needed on this screen.
            wp_enqueue_media();

            wp_enqueue_script(
                $this->plugin_name . '-product',
                plugin_dir_url(__FILE__) . 'js/ar-model-viewer-for-woocommerce-product.js',
                array('jquery', 'wp-i18n'),
                $this->asset_version('js/ar-model-viewer-for-woocommerce-product.js'),
                true
            );

            wp_localize_script($this->plugin_name . '-product', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'mode_preview_icon' => plugin_dir_url(__FILE__) . 'images/icons8-object-94.png',
                'mode_refine_icon' => plugin_dir_url(__FILE__) . 'images/icons8-3d-printer-94.png',
                'status_succeeded_icon' => plugin_dir_url(__FILE__) . 'images/icons8-check-94.png',
                'api_key_set' => Ar_Model_Viewer_For_Woocommerce_Settings::has_api_key(),
                // The script injects these libraries on demand, only when a feature needs them.
                'vendor_files' => $this->vendor_files(),
                // Static properties of the viewer element, applied by the script once the library
                // is loaded: they cannot be printed as attributes.
                'static_properties' => Ar_Model_Viewer_For_Woocommerce_Settings::static_properties(),
            ));

            // The alertify and driver.js stylesheets used to be bundled inside the JavaScript.
            $vendor_style = $this->vendor_url('vendor.css');

            if ($vendor_style) {
                wp_enqueue_style(
                    $this->plugin_name . '-vendor',
                    $vendor_style,
                    array(),
                    $this->asset_version('assets/vendor/vendor.css'),
                    'all'
                );
            }
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

    public function ar_model_viewer_for_woocommerce_blocksy_fix($current_value)
    {
        // Use WooCommerce built in gallery
        return true;
    }
}
