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
class Ar_Model_Viewer_For_Woocommerce_Admin_Pro
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
     * Register the columns in the importer.
     *
     * Registers custom columns for the WooCommerce product importer.
     * These columns allow adding URLs and other configurations specific to 3D models
     * used in the AR (Augmented Reality) viewer.
     *
     * @param array $options Array containing the options for columns to be registered in the importer.
     * @return array $options Returns the array of options with the new columns added.
     */
    public function ar_model_viewer_for_woocommerce_add_column_to_importer($options)
    {
        // Adds a new column for the .glb file URL for Android.
        $options['ar_model_viewer_for_woocommerce_file_android'] = 'Android .glb URL';

        // Adds a new column for the .usdz file URL for iOS.
        $options['ar_model_viewer_for_woocommerce_file_ios'] = 'IOS .usdz URL';

        // Adds a column for the 3D model poster, which is shown before loading the model.
        $options['ar_model_viewer_for_woocommerce_file_poster'] = 'Poster for 3D Model';

        // Adds a column for the 3D model's alt text, useful for accessibility and SEO.
        $options['ar_model_viewer_for_woocommerce_file_alt'] = 'Alt for 3D Model';

        // Returns the $options array with the newly added columns.
        return $options;
    }

    /**
     * Add automatic mapping support for Custom Columns.
     *
     * This function adds support for automatically mapping custom columns
     * in the product importer mapping screen in WooCommerce. It links potential
     * column names displayed to the user with their corresponding database slugs.
     *
     * @param array $columns Array containing the current column mappings in the importer screen.
     * @return array $columns Returns the array of columns with new mappings for custom fields.
     */
    public function ar_model_viewer_for_woocommerce_add_column_to_mapping_screen($columns)
    {
        // Maps the displayed column name to the corresponding column slug for the .glb file URL for Android.
        $columns['Android .glb URL'] = 'ar_model_viewer_for_woocommerce_file_android';

        // Maps the displayed column name to the corresponding column slug for the .usdz file URL for iOS.
        $columns['IOS .usdz URL'] = 'ar_model_viewer_for_woocommerce_file_ios';

        // Maps the displayed column name to the corresponding column slug for the 3D model poster.
        $columns['Poster for 3D Model'] = 'ar_model_viewer_for_woocommerce_file_poster';

        // Maps the displayed column name to the corresponding column slug for the alt text of the 3D model.
        $columns['Alt for 3D Model'] = 'ar_model_viewer_for_woocommerce_file_alt';

        // Returns the updated $columns array with the new mappings added.
        return $columns;
    }

    /**
     * Process the data read from the CSV file.
     *
     * This function processes the custom data imported from the CSV file for each product.
     * It saves the imported values as metadata for the WooCommerce product.
     * You can extend this to handle more fields or perform other actions with the data.
     *
     * @param WC_Product $object - Product being imported or updated.
     * @param array $data - CSV data read for the product.
     * @return WC_Product $object - Returns the updated product object with custom meta data saved.
     */
    public function ar_model_viewer_for_woocommerce_process_import($object, $data)
    {
        // Checks if the 'Android .glb URL' data is not empty and updates the product's meta data.
        if (!empty($data['ar_model_viewer_for_woocommerce_file_android'])) {
            $object->update_meta_data('ar_model_viewer_for_woocommerce_file_android', $data['ar_model_viewer_for_woocommerce_file_android']);
        }

        // Checks if the 'IOS .usdz URL' data is not empty and updates the product's meta data.
        if (!empty($data['ar_model_viewer_for_woocommerce_file_ios'])) {
            $object->update_meta_data('ar_model_viewer_for_woocommerce_file_ios', $data['ar_model_viewer_for_woocommerce_file_ios']);
        }

        // Checks if the 'Poster for 3D Model' data is not empty and updates the product's meta data.
        if (!empty($data['ar_model_viewer_for_woocommerce_file_poster'])) {
            $object->update_meta_data('ar_model_viewer_for_woocommerce_file_poster', $data['ar_model_viewer_for_woocommerce_file_poster']);
        }

        // Checks if the 'Alt for 3D Model' data is not empty and updates the product's meta data.
        if (!empty($data['ar_model_viewer_for_woocommerce_file_alt'])) {
            $object->update_meta_data('ar_model_viewer_for_woocommerce_file_alt', $data['ar_model_viewer_for_woocommerce_file_alt']);
        }

        // Returns the updated product object with all the meta data added.
        return $object;
    }

    /**
     * Add the custom column to the exporter and the exporter column menu.
     *
     * This function registers custom columns in the WooCommerce product exporter.
     * These columns will allow exporting specific meta data related to 3D model files
     * for Android, iOS, and additional model properties like the poster image and alt text.
     * These will be selectable in the export options.
     *
     * @param array $columns Array of available columns for export.
     * @return array $columns Returns the updated array of columns with the new custom fields.
     */
    public function ar_model_viewer_for_woocommerce_add_export_column($columns)
    {
        // Adds a new column for the Android .glb file URL to the exporter.
        $columns['ar_model_viewer_for_woocommerce_file_android'] = 'Android .glb URL';

        // Adds a new column for the IOS .usdz file URL to the exporter.
        $columns['ar_model_viewer_for_woocommerce_file_ios'] = 'IOS .usdz URL';

        // Adds a new column for the poster image of the 3D model to the exporter.
        $columns['ar_model_viewer_for_woocommerce_file_poster'] = 'Poster for 3D Model';

        // Adds a new column for the alt text of the 3D model to the exporter.
        $columns['ar_model_viewer_for_woocommerce_file_alt'] = 'Alt for 3D Model';

        // Returns the updated columns array with the new export fields included.
        return $columns;
    }

    /**
     * Provide the data to be exported for the Android .glb URL column.
     *
     * This function retrieves the value for the Android .glb URL from the product meta data
     * and formats it for export.
     *
     * @param mixed $value (default: '') The current value of the export column.
     * @param WC_Product $product The product being exported.
     * @return mixed $value The value that will be exported, should be in a format compatible with text files.
     */
    public function ar_model_viewer_for_woocommerce_add_export_data_file_android($value, $product)
    {
        // Retrieve the 'Android .glb URL' meta data and set it as the export value.
        $value = $product->get_meta('ar_model_viewer_for_woocommerce_file_android', true, 'edit');
        return $value;
    }

    /**
     * Provide the data to be exported for the IOS .usdz URL column.
     *
     * This function retrieves the value for the IOS .usdz URL from the product meta data
     * and formats it for export.
     *
     * @param mixed $value (default: '') The current value of the export column.
     * @param WC_Product $product The product being exported.
     * @return mixed $value The value that will be exported, should be in a format compatible with text files.
     */
    public function ar_model_viewer_for_woocommerce_add_export_data_file_ios($value, $product)
    {
        // Retrieve the 'IOS .usdz URL' meta data and set it as the export value.
        $value = $product->get_meta('ar_model_viewer_for_woocommerce_file_ios', true, 'edit');
        return $value;
    }

    /**
     * Provide the data to be exported for the Poster for 3D Model column.
     *
     * This function retrieves the value for the Poster for 3D Model from the product meta data
     * and formats it for export.
     *
     * @param mixed $value (default: '') The current value of the export column.
     * @param WC_Product $product The product being exported.
     * @return mixed $value The value that will be exported, should be in a format compatible with text files.
     */
    public function ar_model_viewer_for_woocommerce_add_export_data_file_poster($value, $product)
    {
        // Retrieve the 'Poster for 3D Model' meta data and set it as the export value.
        $value = $product->get_meta('ar_model_viewer_for_woocommerce_file_poster', true, 'edit');
        return $value;
    }

    /**
     * Provide the data to be exported for the Alt for 3D Model column.
     *
     * This function retrieves the value for the Alt text of the 3D model from the product meta data
     * and formats it for export.
     *
     * @param mixed $value (default: '') The current value of the export column.
     * @param WC_Product $product The product being exported.
     * @return mixed $value The value that will be exported, should be in a format compatible with text files.
     */
    public function ar_model_viewer_for_woocommerce_add_export_data_file_alt($value, $product)
    {
        // Retrieve the 'Alt for 3D Model' meta data and set it as the export value.
        $value = $product->get_meta('ar_model_viewer_for_woocommerce_file_alt', true, 'edit');
        return $value;
    }

    // Registrar el widget de Elementor
    public function register_ar_model_viewer_widget($widgets_manager)
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/widgets/ar-model-viewer-for-woocommerce-widget.php';
        $widgets_manager->register(new \Ar_Model_Viewer_For_Woocommerce_Widget());
    }

    public function ar_model_viewer_for_woocommerce_pro_metaboxes()
    {
        $meshyAi = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_api_key_meshy');

        if (!empty($meshyAi)) {
            $image_to_3d = new_cmb2_box(array(
                'id' => 'ar_model_viewer_for_woocommerce_metabox_image_to_3d',
                'title' => __('Image to 3D Model', 'cmb2'),
                'object_types' => array('product'), // Post type
                'context' => 'side',
                'priority' => 'low',
                'show_names' => false, // Show field names on the left
                'cmb_styles' => true, // false to disable the CMB stylesheet
                'closed' => false, // Keep the metabox closed by default
            ));

            $image_to_3d->add_field(array(
                'name' => '3D Model with Image',
                'desc' => '',
                'type' => 'title',
                'id' => 'image_to_3d_title',
                'after' => '<a id="generate-text-to-3d" class="cmb2-upload-button button-secondary">Generate 3D model from Image</a>',
            ));
        }
    }
}
