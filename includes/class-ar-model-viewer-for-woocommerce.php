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
         * The class that owns the settings: defaults, validation and storage.
         *
         * It is loaded first because the logger, the AI client and both the admin and
         * the public side read their configuration from it.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ar-model-viewer-for-woocommerce-settings.php';

        // Stored values are migrated before anything reads the settings.
        Ar_Model_Viewer_For_Woocommerce_Settings::maybe_upgrade();

        /**
         * The class that reads the 3D model of a product and its viewer overrides.
         *
         * It validates its values through the settings class, so it is loaded after it.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ar-model-viewer-for-woocommerce-product-model.php';

        /**
         * The class responsible for integrating product model fields with WooCommerce CSV tools.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ar-model-viewer-for-woocommerce-product-csv.php';

        /**
         * The class responsible for anonymous first-party viewer analytics.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ar-model-viewer-for-woocommerce-analytics.php';
        Ar_Model_Viewer_For_Woocommerce_Analytics::maybe_upgrade();

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ar-model-viewer-for-woocommerce-logger.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-model-viewer-for-woocommerce-admin.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-model-viewer-for-woocommerce-admin-settings.php';

        /**
         * The class that registers the viewer with the product editor of WooCommerce.
         *
         * The file only declares the class, so requiring it is harmless on a site where
         * WooCommerce is not loaded: the hooks it registers simply never fire.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-model-viewer-for-woocommerce-admin-woocommerce.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-ar-model-viewer-for-woocommerce-public.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing Shortcode
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-ar-model-viewer-for-woocommerce-public-shortcode.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing Shortcode
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-ar-model-viewer-for-woocommerce-public-tab.php';

        if (ar_model_viewer_for_woocommerce_fs()->is__premium_only()) {
            /**
             * Check if the user has access to premium features. This condition ensures that
             * the following code block is executed only for users who have purchased the premium version.
             */

            // Check if the user is on the 'pro' plan and has an active subscription.
            if (ar_model_viewer_for_woocommerce_fs()->is_plan('pro', true) && ar_model_viewer_for_woocommerce_fs()->can_use_premium_code()) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-model-viewer-for-woocommerce-admin-pro.php';
            }
        }

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

        // Admin controller: styles, scripts, MIME types and shared admin classes.
        $plugin_admin = new Ar_Model_Viewer_For_Woocommerce_Admin($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());

        // Admin hooks: styles, scripts and shared WordPress admin behavior.
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        /**
         * Enqueues the admin styles for the WordPress admin dashboard.
         * The function `enqueue_styles` in `$plugin_admin` will include the necessary CSS files for the plugin.
         */

        // Include the admin scripts in the Admin dashboard.
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        /**
         * Enqueues the admin scripts for the WordPress admin dashboard.
         * The function `enqueue_scripts` in `$plugin_admin` will include the necessary JavaScript files for the plugin.
         */

        // Set the extension and mime type for Android (.glb) and iOS (.usdz) files.
        $this->loader->add_filter('wp_check_filetype_and_ext', $plugin_admin, 'ar_model_viewer_for_woocommerce_file_and_ext', 10, 4);
        /**
         * Adds support for Android `.glb` and iOS `.usdz` file types by defining their extensions and mime types.
         * The function `ar_model_viewer_for_woocommerce_file_and_ext` checks the file type during upload and processing.
         * This ensures that the file types are correctly identified in WordPress.
         * It hooks into the `wp_check_filetype_and_ext` filter, with a priority of 10 and passes 4 arguments.
         */

        // Allow Android (.glb) and iOS (.usdz) files to be uploaded by adding them to the allowed MIME types.
        $this->loader->add_filter('upload_mimes', $plugin_admin, 'ar_model_viewer_for_woocommerce_mime_types');
        $this->loader->add_filter('admin_body_class', $plugin_admin, 'admin_body_class');

        /*
         * WooCommerce integration: the viewer fields inside Product data and the fields of every
         * variation.
         *
         * The product fields are appended to the General tab that WooCommerce already draws, so
         * they look and behave like the rest of the product. There is no tab of this plugin and no
         * metabox of this plugin: one product is configured in one place, with one design.
         */
        $plugin_admin_woocommerce = new Ar_Model_Viewer_For_Woocommerce_Admin_WooCommerce($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());

        $this->loader->add_action('woocommerce_product_options_general_product_data', $plugin_admin_woocommerce, 'render_product_fields');
        $this->loader->add_action('woocommerce_admin_process_product_object', $plugin_admin_woocommerce, 'save_product');

        /*
         * The variation block is printed once per variation, so it receives the index it needs to
         * build unique field names, and the same index comes back when WooCommerce saves.
         */
        $this->loader->add_action('woocommerce_product_after_variable_attributes', $plugin_admin_woocommerce, 'render_variation_fields', 10, 3);
        $this->loader->add_action('woocommerce_save_product_variation', $plugin_admin_woocommerce, 'save_variation', 10, 2);

        // Settings controller: Settings API page, reset action and screen classes.
        $plugin_admin_settings = new Ar_Model_Viewer_For_Woocommerce_Admin_Settings($this->get_plugin_name(), $this->get_plugin_prefix(), $this->version);

        $this->loader->add_action('admin_menu', $plugin_admin_settings, 'register_settings_page');
        $this->loader->add_action('admin_init', $plugin_admin_settings, 'register_settings');
        $this->loader->add_action('admin_init', $plugin_admin_settings, 'handle_reset');
        $this->loader->add_action('admin_init', $plugin_admin_settings, 'handle_clear_analytics');
        /**
         * The settings screen is a native options page: `add_options_page()` reproduces the
         * same `settings_page_ar_model_viewer_for_woocommerce_settings` hook suffix the previous
         * implementation generated, so bookmarks, the Freemius menu entry and the enqueued assets
         * keep working after the migration.
         */

        // Premium controller: CSV import/export and Elementor integration.
        if (ar_model_viewer_for_woocommerce_fs()->is__premium_only()) {
            if (ar_model_viewer_for_woocommerce_fs()->is_plan('pro', true) && ar_model_viewer_for_woocommerce_fs()->can_use_premium_code()) {
                $plugin_product_csv = new Ar_Model_Viewer_For_Woocommerce_Product_CSV();

                $this->loader->add_filter('woocommerce_csv_product_import_mapping_options', $plugin_product_csv, 'add_import_columns');
                $this->loader->add_filter('woocommerce_csv_product_import_mapping_default_columns', $plugin_product_csv, 'add_default_mappings');
                $this->loader->add_action('woocommerce_product_import_inserted_product_object', $plugin_product_csv, 'process_import', 10, 2);
                $this->loader->add_filter('woocommerce_product_export_column_names', $plugin_product_csv, 'add_export_columns');
                $this->loader->add_filter('woocommerce_product_export_product_default_columns', $plugin_product_csv, 'add_export_columns');

                foreach (Ar_Model_Viewer_For_Woocommerce_Product_CSV::column_keys() as $column) {
                    $this->loader->add_filter('woocommerce_product_export_product_column_' . $column, $plugin_product_csv, 'export_value', 10, 2);
                }
            }
        }

        // Premium controller: Elementor integration.
        if (ar_model_viewer_for_woocommerce_fs()->is__premium_only()) {
            /**
             * Check if the user has access to premium features. This condition ensures that
             * the following code block is executed only for users who have purchased the premium version.
             */

            // Check if the user is on the 'pro' plan and has an active subscription.
            if (ar_model_viewer_for_woocommerce_fs()->is_plan('pro', true) && ar_model_viewer_for_woocommerce_fs()->can_use_premium_code()) {
                /**
                 * Check if the user is on the "Pro" plan and has an active subscription.
                 * `is_plan('pro', true)` checks if the user has access to the 'Pro' plan.
                 * `can_use_premium_code()` ensures that the user has an active subscription.
                 * If both conditions are met, the premium functionality is executed.
                 */

                // Instantiate the admin class for the pro version of the plugin.
                $plugin_admin_pro = new Ar_Model_Viewer_For_Woocommerce_Admin_Pro($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());

                // Register the AR model viewer widget in Elementor.
                $this->loader->add_action('elementor/widgets/register', $plugin_admin_pro, 'register_ar_model_viewer_widget');
            }
        }
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
        $plugin_public_shortcode = new Ar_Model_Viewer_For_Woocommerce_Public_Shortcode($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());
        $plugin_public_tab = new Ar_Model_Viewer_For_Woocommerce_Public_Tab($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());
        // Include the styles for public web
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        // Include the scripts for public web
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('rest_api_init', $plugin_public, 'register_rest_routes');
        $this->loader->add_action('rest_api_init', 'Ar_Model_Viewer_For_Woocommerce_Analytics', 'register_rest_routes');

        // Placement of the 3D button. An empty value means the button is not printed at all.
        $button_position = Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_btn');

        /*
         * Every position is the hook that prints the button plus the priority it uses. The priority
         * matters in `woocommerce_single_product_summary`, where 35 lands right below the button
         * that adds the product to the cart instead of above its title.
         */
        $button_hooks = array(
            '1' => array('woocommerce_before_single_product_summary', 10),
            '2' => array('woocommerce_after_single_product_summary', 10),
            '3' => array('woocommerce_before_single_product', 10),
            '4' => array('woocommerce_after_single_product', 10),
            '5' => array('woocommerce_after_add_to_cart_form', 10),
            '6' => array('woocommerce_before_add_to_cart_form', 10),
            '7' => array('woocommerce_after_add_to_cart_button', 10),
            '8' => array('woocommerce_product_meta_start', 10),
            '9' => array('woocommerce_product_meta_end', 10),
            '10' => array('woocommerce_single_product_summary', 35),
            /*
             * `woocommerce_product_thumbnails` runs inside the gallery container in every theme that
             * follows the WooCommerce templates, which is what lets the button be printed over the
             * product image by the server instead of being injected by a script.
             */
            '11' => array('woocommerce_product_thumbnails', 10),
        );

        if (isset($button_hooks[$button_position])) {
            $button_hook = $button_hooks[$button_position];

            $this->loader->add_action($button_hook[0], $plugin_public, 'ar_model_viewer_for_woocommerce_button', $button_hook[1]);
        }

        // Check if the product tab is enabled in the settings.
        if ('yes' === Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_single_product_tabs')) {
            $this->loader->add_filter('woocommerce_product_tabs', $plugin_public_tab, 'ar_model_viewer_for_woocommerce_tab');
        }

        $this->loader->add_action('wp_ajax_ar_model_viewer_for_woocommerce_get_model_and_settings', $plugin_public, 'ar_model_viewer_for_woocommerce_get_model_and_settings');
        $this->loader->add_action('wp_ajax_nopriv_ar_model_viewer_for_woocommerce_get_model_and_settings', $plugin_public, 'ar_model_viewer_for_woocommerce_get_model_and_settings');
        
        $this->loader->add_shortcode($this->plugin_prefix . 'shortcode', $plugin_public_shortcode, 'ar_model_viewer_for_woocommerce_shortcode_func', 10, 1);
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
