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
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-model-viewer-for-woocommerce-admin-product.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ar-model-viewer-for-woocommerce-admin-settings.php';

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

        /**
         * The class responsible for defining all actions that occur in the public-facing Shortcode
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-meshy-api.php';

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

        // Instantiate the admin class for the free version of the plugin.
        $plugin_admin = new Ar_Model_Viewer_For_Woocommerce_Admin($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());
        $plugin_admin_product = new Ar_Model_Viewer_For_Woocommerce_Admin_Product($this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version());
        $plugin_admin_settings = new Ar_Model_Viewer_For_Woocommerce_Admin_Settings($this->get_plugin_name(), $this->get_plugin_prefix(), $this->version);

        $this->loader->add_action( 'before_woocommerce_init', $plugin_admin ,'ar_model_viewer_for_woocommerce_hpos_compatibility' );

        // Include the admin styles in the Admin dashboard.
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
        /**
         * Adds `.glb` and `.usdz` to the list of allowed MIME types for file uploads.
         * The function `ar_model_viewer_for_woocommerce_mime_types` ensures that these file types can be uploaded to the WordPress media library.
         * It hooks into the `upload_mimes` filter.
         */

        // Define and initialize custom metaboxes and field configurations.
        $this->loader->add_action('cmb2_admin_init', $plugin_admin_product, 'ar_model_viewer_for_woocommerce_cmb2_metaboxes');
        $this->loader->add_action('cmb2_admin_init', $plugin_admin_settings, 'ar_model_viewer_for_woocommerce_cmb2_settings');
        /**
         * Registers and configures custom metaboxes for the plugin using the CMB2 library.
         * The function `ar_model_viewer_for_woocommerce_cmb2_metaboxes` sets up fields for products where users can input 3D model file URLs and other details.
         * It hooks into the `cmb2_admin_init` action to ensure the fields are initialized when needed.
         */

        // Get the currently active theme.
        $theme_actual = wp_get_theme();
        /**
         * Retrieves the active WordPress theme.
         * This is used to check if specific actions or fixes are needed for certain themes (e.g., Bloksy).
         */

        if ($theme_actual->name === 'Blocksy') {
            // Check if the active theme is Bloksy and apply necessary fixes.
            $this->loader->add_filter('blocksy:woocommerce:product-view:use-default', $plugin_admin, 'ar_model_viewer_for_woocommerce_blocksy_fix');
            /**
             * Adds a filter to fix compatibility issues with the Bloksy theme.
             * The function `ar_model_viewer_for_woocommerce_blocksy_fix` handles specific changes needed for the Bloksy theme’s WooCommerce product view.
             * It hooks into the `blocksy:woocommerce:product-view:use-default` filter, ensuring the plugin works seamlessly with Bloksy.
             */
        }

        $this->loader->add_action('wp_ajax_ar_model_viewer_for_woocommerce_get_model_and_settings', $plugin_admin_product, 'ar_model_viewer_for_woocommerce_get_model_and_settings');
        $this->loader->add_action('wp_ajax_ar_model_viewer_for_woocommerce_get_tasks', $plugin_admin_product, 'ar_model_viewer_for_woocommerce_get_tasks');
        $this->loader->add_action('wp_ajax_ar_model_viewer_for_woocommerce_get_model_preview_with_global_settings', $plugin_admin_settings, 'ar_model_viewer_for_woocommerce_get_model_preview_with_global_settings');
        $this->loader->add_action('wp_ajax_ar_model_viewer_for_woocommerce_createTextTo3DTaskPreview',$plugin_admin_product,'ar_model_viewer_for_woocommerce_createTextTo3DTaskPreview');
        $this->loader->add_action('wp_ajax_ar_model_viewer_for_woocommerce_createTextTo3DTaskRefine', $plugin_admin_product, 'ar_model_viewer_for_woocommerce_createTextTo3DTaskRefine');
        $this->loader->add_action('wp_ajax_ar_model_viewer_for_woocommerce_get_task_and_download', $plugin_admin_product, 'ar_model_viewer_for_woocommerce_get_task_and_download');
        
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

                //Register the CMB2 Fields to Pro Version
                $this->loader->add_action('cmb2_admin_init', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_pro_metaboxes');


                // Register the AR model viewer widget in Elementor.
                $this->loader->add_action('elementor/widgets/register', $plugin_admin_pro, 'register_ar_model_viewer_widget');
                /**
                 * This hook registers the AR model viewer widget with Elementor. It allows the
                 * premium widget to be used in Elementor’s page builder.
                 * The `register_ar_model_viewer_widget` function is called when Elementor widgets are registered.
                 */

                // Hooks for Importer
                $this->loader->add_filter('woocommerce_csv_product_import_mapping_options', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_add_column_to_importer');
                /**
                 * Adds a custom column for 3D model data to the WooCommerce product importer.
                 * The `ar_model_viewer_for_woocommerce_add_column_to_importer` function defines
                 * the custom column names that will be displayed when mapping import fields.
                 */

                $this->loader->add_filter('woocommerce_csv_product_import_mapping_default_columns', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_add_column_to_mapping_screen');
                /**
                 * Automatically maps the custom columns for 3D model data when importing products.
                 * This hook connects the column names with their corresponding WooCommerce meta fields.
                 * The function `ar_model_viewer_for_woocommerce_add_column_to_mapping_screen` handles this mapping.
                 */

                $this->loader->add_filter('woocommerce_product_import_pre_insert_product_object', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_process_import', 10, 2);
                /**
                 * Processes the imported data for each product and saves the custom 3D model meta fields.
                 * The `ar_model_viewer_for_woocommerce_process_import` function takes the product object
                 * and the imported data, and then updates the metadata with the values from the CSV.
                 * This is triggered before the product is inserted into the database.
                 */

                // Hooks for Exporter
                $this->loader->add_filter('woocommerce_product_export_column_names', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_add_export_column');
                /**
                 * Adds custom column names for exporting 3D model data in WooCommerce.
                 * The `ar_model_viewer_for_woocommerce_add_export_column` function adds the custom
                 * columns (like Android and iOS model file URLs) to the list of available export fields.
                 */

                $this->loader->add_filter('woocommerce_product_export_product_default_columns', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_add_export_column');
                /**
                 * Adds the same custom column names for exporting 3D model data, ensuring they are part
                 * of the default export fields in WooCommerce.
                 * The `ar_model_viewer_for_woocommerce_add_export_column` function is reused to achieve this.
                 */

                // Hook for exporting the Android .glb URL column.
                $this->loader->add_filter('woocommerce_product_export_product_column_ar_model_viewer_for_woocommerce_file_android', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_add_export_data_file_android', 10, 2);
                /**
                 * Exports the Android `.glb` file URL from the product metadata during WooCommerce exports.
                 * The function `ar_model_viewer_for_woocommerce_add_export_data_file_android` retrieves
                 * the meta field for the Android file and formats it for export.
                 */

                // Hook for exporting the IOS .usdz URL column.
                $this->loader->add_filter('woocommerce_product_export_product_column_ar_model_viewer_for_woocommerce_file_ios', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_add_export_data_file_ios', 10, 2);
                /**
                 * Exports the iOS `.usdz` file URL from the product metadata during WooCommerce exports.
                 * The function `ar_model_viewer_for_woocommerce_add_export_data_file_ios` retrieves
                 * the meta field for the iOS file and formats it for export.
                 */

                // Hook for exporting the Poster for 3D Model column.
                $this->loader->add_filter('woocommerce_product_export_product_column_ar_model_viewer_for_woocommerce_file_poster', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_add_export_data_file_poster', 10, 2);
                /**
                 * Exports the poster image URL for the 3D model from the product metadata during WooCommerce exports.
                 * The function `ar_model_viewer_for_woocommerce_add_export_data_file_poster` retrieves
                 * the meta field for the poster image and formats it for export.
                 */

                // Hook for exporting the Alt for 3D Model column.
                $this->loader->add_filter('woocommerce_product_export_product_column_ar_model_viewer_for_woocommerce_file_alt', $plugin_admin_pro, 'ar_model_viewer_for_woocommerce_add_export_data_file_alt', 10, 2);
                /**
                 * Exports the alt text for the 3D model from the product metadata during WooCommerce exports.
                 * The function `ar_model_viewer_for_woocommerce_add_export_data_file_alt` retrieves
                 * the meta field for the alt text and formats it for export.
                 */
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
                $this->loader->add_filter('woocommerce_product_tabs', $plugin_public_tab, 'ar_model_viewer_for_woocommerce_tab');
            }
        }

        $this->loader->add_action('wp_ajax_ar_model_viewer_for_woocommerce_get_model_and_settings', $plugin_public, 'ar_model_viewer_for_woocommerce_get_model_and_settings');

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
