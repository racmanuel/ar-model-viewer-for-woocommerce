<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/includes
 * @author     Manuel Ramirez Coronel <ra_cm@outlook.com>
 */
class Ar_Model_Viewer_For_Woocommerce
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Ar_Model_Viewer_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The unique prefix of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
     */
    protected $plugin_prefix;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {

        if (defined('AR_MODEL_VIEWER_FOR_WOOCOMMERCE_VERSION')) {

            $this->version = AR_MODEL_VIEWER_FOR_WOOCOMMERCE_VERSION;

        } else {

            $this->version = '1.0.0';

        }

        $this->plugin_name = 'ar-model-viewer-for-woocommerce';
        $this->plugin_prefix = 'ar_model_viewer_for_woocommerce_';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Ar_Model_Viewer_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
     * - Ar_Model_Viewer_For_Woocommerce_i18n. Defines internationalization functionality.
     * - Ar_Model_Viewer_For_Woocommerce_Admin. Defines all hooks for the admin area.
     * - Ar_Model_Viewer_For_Woocommerce_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ar-model-viewer-for-woocommerce-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ar-model-viewer-for-woocommerce-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-model-viewer-for-woocommerce-admin.php';

        // This IF block will be auto removed from the Free version.
        if (ar_model_viewer_for_woocommerce_fs()->is__premium_only()) {
            /**
             * The class responsible for defining all actions that occur in the admin area.
             */
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-model-viewer-for-woocommerce-admin-custom-post-type.php';

            /**
             * The class responsible for defining all actions that occur in the admin area.
             */
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-model-viewer-for-woocommerce-admin-ajax.php';

            /**
             * The class responsible for defining all actions that occur in the admin area.
             */
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-mode-viewer-for-woocommerce-admin-widget-elementor.php';
        }

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-ar-model-viewer-for-woocommerce-public.php';

        $this->loader = new Ar_Model_Viewer_For_Woocommerce_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Ar_Model_Viewer_For_Woocommerce_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Ar_Model_Viewer_For_Woocommerce_I18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Ar_Model_Viewer_For_Woocommerce_Admin($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());
        // This IF block will be auto removed from the Free version.
        if (ar_model_viewer_for_woocommerce_fs()->is__premium_only()) {
            $plugin_custom_post_type = new Ar_Model_Viewer_For_Woocommerce_Admin_Custom_Post_Type($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());
            $plugin_admin_ajax = new Ar_Model_Viewer_For_Woocommerce_Admin_Ajax($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());
            $plugin_admin_widget = new Ar_Model_Viewer_For_Woocommerce_Admin_Widget($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());
        }

        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $this->loader->add_action('admin_notices', $plugin_admin, 'ar_model_viewer_for_woocommerce_error_notice');
        }
        // Include the admin styles in the Admin
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        // Include the admin scripts in the Admin
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        //Sets the extension and mime type for Android - .gbl and IOS - .usdz files.
        $this->loader->add_filter('wp_check_filetype_and_ext', $plugin_admin, 'ar_model_viewer_for_woocommerce_file_and_ext', 10, 4);
        //Adds Android - .gbl and IOS - .usdz filetype to allowed mimes
        $this->loader->add_filter('upload_mimes', $plugin_admin, 'ar_model_viewer_for_woocommerce_mime_types');
        //Define the metabox and field configurations.
        $this->loader->add_action('cmb2_admin_init', $plugin_admin, 'ar_model_viewer_for_woocommerce_cmb2_metaboxes');

        $theme_actual = wp_get_theme();

        if ($theme_actual->name === 'Blocksy') {
            // El tema Bloksy está activo
            $this->loader->add_filter('blocksy:woocommerce:product-view:use-default', $plugin_admin, 'ar_model_viewer_for_woocommerce_blocksy_fix');
        }

        // This IF block will be auto removed from the Free version.
        if (ar_model_viewer_for_woocommerce_fs()->is__premium_only()) {
            // This block execute code if the user has the plan 'Personal'
            if (ar_model_viewer_for_woocommerce_fs()->is_plan('Personal', true)) {
                // ... premium only logic ...
                $this->loader->add_action('init', $plugin_custom_post_type, 'ar_model_viewer_for_woocommerce_add_custom_post_type__premium_only');
                // Añadir nuevas columnas al CPT 'ar_model'
                $this->loader->add_filter('manage_edit-ar_model_columns', $plugin_custom_post_type, 'ar_model_viewer_for_woocommerce_add_custom_columns_in_custom_post_type__premium_only');
                // Rellenar las nuevas columnas con datos
                $this->loader->add_action('manage_ar_model_posts_custom_column', $plugin_custom_post_type, 'ar_model_viewer_for_woocommerce_add_content_to_custom_columns_in_custom_post_type__premium_only', 10, 2);
                // Permite obtener los datos del modelo 3D para una vista previa.
                $this->loader->add_action('wp_ajax_ar_model_viewer_for_woocommerce_get_model_data', $plugin_admin_ajax, 'ar_model_viewer_for_woocommerce_get_model_data__premium_only');
                $this->loader->add_action( 'elementor/widgets/widgets_registered', $plugin_admin_widget ,'my_elementor_load_widget' );
                $this->loader->add_action( 'elementor/widgets/register', $plugin_admin_widget  ,'register_ar_model_viewer_widget' );

            }
        }

        $this->loader->add_action('admin_notices', $plugin_admin, 'ar_model_viewer_for_woocommerce_admin_notice');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Ar_Model_Viewer_For_Woocommerce_Public($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());

        // Include the styles for public web
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        // Include the scripts for public web
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Check options of the plugin
        $ar_model_viewer_settings = get_option('ar_model_viewer_for_woocommerce_settings');

        // Check the option where the button is avaible
        switch (isset($ar_model_viewer_settings['ar_model_viewer_for_woocommerce_btn'])) {
            case 1:
                $this->loader->add_action('woocommerce_before_single_product_summary', $plugin_public, 'ar_model_viewer_for_woocommerce_button');
                break;
            case 2:
                $this->loader->add_action('woocommerce_after_single_product_summary', $plugin_public, 'ar_model_viewer_for_woocommerce_button');
                break;
            case 3:
                $this->loader->add_action('woocommerce_before_single_product', $plugin_public, 'ar_model_viewer_for_woocommerce_button');
                break;
            case 4:
                $this->loader->add_action('woocommerce_after_single_product', $plugin_public, 'ar_model_viewer_for_woocommerce_button');
                break;
            case 5:
                $this->loader->add_action('woocommerce_after_add_to_cart_form', $plugin_public, 'ar_model_viewer_for_woocommerce_button');
                break;
            case 6:
                $this->loader->add_action('woocommerce_before_add_to_cart_form', $plugin_public, 'ar_model_viewer_for_woocommerce_button');
                break;
        }

        // Check if in settings show in tabs is active
        if (isset($ar_model_viewer_settings['ar_model_viewer_for_woocommerce_single_product_tabs'])) {
            if ($ar_model_viewer_settings['ar_model_viewer_for_woocommerce_single_product_tabs'] == 'yes') {
                // Show a button before single_product
                $this->loader->add_filter('woocommerce_product_tabs', $plugin_public, 'ar_model_viewer_for_woocommerce_tab');
            }
        }

        /***
         * Code for use a Shortcode for the plugin.
         * NOTE: Maybe remove for prodcution.
         * Shortcode name must be the same as in shortcode_atts() third parameter.
         */
        $this->loader->add_shortcode('ar-model-viewer', $plugin_public, 'ar_model_viewer_for_woocommerce_shortcode_func');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The unique prefix of the plugin used to uniquely prefix technical functions.
     *
     * @since     1.0.0
     * @return    string    The prefix of the plugin.
     */
    public function get_plugin_prefix()
    {
        return $this->plugin_prefix;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Ar_Model_Viewer_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}
