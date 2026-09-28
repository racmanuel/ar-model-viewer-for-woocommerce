<?php
/**
 * WooCommerce CSV integration for product 3D/AR data.
 *
 * @package Ar_Model_Viewer_For_Woocommerce
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Registers the product model fields with WooCommerce's native CSV tools.
 */
class Ar_Model_Viewer_For_Woocommerce_Product_CSV
{
    const PREFIX = 'armvw_';

    /**
     * Return the technical CSV keys without translating labels during plugin bootstrap.
     *
     * @return array<int, string>
     */
    public static function column_keys()
    {
        return array(
            'armvw_model_url',
            'armvw_poster_url',
            'armvw_model_alt',
            'armvw_camera_orbit',
            'armvw_camera_target',
            'armvw_field_of_view',
            'armvw_orientation',
            'armvw_scale',
            'armvw_animation_name',
            'armvw_variant_name',
            'armvw_autoplay',
            'armvw_ios_src',
        );
    }

    /**
     * Return the stable CSV key and label for every supported field.
     *
     * @return array<string, string>
     */
    public static function columns()
    {
        $columns = array(
            'armvw_model_url' => __('3D model URL', 'ar-model-viewer-for-woocommerce'),
            'armvw_poster_url' => __('3D model poster URL', 'ar-model-viewer-for-woocommerce'),
            'armvw_model_alt' => __('3D model alt text', 'ar-model-viewer-for-woocommerce'),
        );

        foreach (Ar_Model_Viewer_For_Woocommerce_Product_Model::fields() as $short => $field) {
            $columns[self::PREFIX . $short] = $field['label'];
        }

        return $columns;
    }

    /**
     * Add the plugin's columns to the importer mapping options.
     *
     * @param array $options Existing importer options.
     * @return array
     */
    public function add_import_columns($options)
    {
        return array_merge($options, self::columns());
    }

    /**
     * Add automatic mappings for the new and historical column labels.
     *
     * @param array $columns Existing automatic mappings.
     * @return array
     */
    public function add_default_mappings($columns)
    {
        foreach (self::columns() as $key => $label) {
            $columns[$label] = $key;
        }

        $columns['Android .glb URL'] = 'armvw_model_url';
        $columns['IOS .usdz URL'] = 'armvw_ios_src';
        $columns['Poster for 3D Model'] = 'armvw_poster_url';
        $columns['Alt for 3D Model'] = 'armvw_model_alt';

        return $columns;
    }

    /**
     * Save imported model data after WooCommerce has inserted the product.
     *
     * @param WC_Product $product Imported product.
     * @param array      $data    Raw importer data.
     * @return void
     */
    public function process_import($product, $data)
    {
        $files = array(
            'file_object' => self::import_value($data, 'armvw_model_url', 'ar_model_viewer_for_woocommerce_file_android', $product->get_meta(Ar_Model_Viewer_For_Woocommerce_Product_Model::META_SOURCE, true)),
            'file_poster' => self::import_value($data, 'armvw_poster_url', 'ar_model_viewer_for_woocommerce_file_poster', $product->get_meta(Ar_Model_Viewer_For_Woocommerce_Product_Model::META_POSTER, true)),
            'file_alt' => self::import_value($data, 'armvw_model_alt', 'ar_model_viewer_for_woocommerce_file_alt', $product->get_meta(Ar_Model_Viewer_For_Woocommerce_Product_Model::META_ALT, true)),
        );

        if (!array_key_exists('armvw_ios_src', $data) && array_key_exists('ar_model_viewer_for_woocommerce_file_ios', $data)) {
            $data['armvw_ios_src'] = $data['ar_model_viewer_for_woocommerce_file_ios'];
        }

        $options = array();

        foreach (Ar_Model_Viewer_For_Woocommerce_Product_Model::fields() as $short => $field) {
            $key = self::PREFIX . $short;
            $options[$short] = array_key_exists($key, $data)
                ? $data[$key]
                : $product->get_meta(Ar_Model_Viewer_For_Woocommerce_Product_Model::meta_key($short), true);
        }

        Ar_Model_Viewer_For_Woocommerce_Product_Model::save_files($product->get_id(), $files);
        Ar_Model_Viewer_For_Woocommerce_Product_Model::save($product->get_id(), $options);
    }

    /**
     * Add columns to the exporter and its default selection.
     *
     * @param array $columns Existing exporter columns.
     * @return array
     */
    public function add_export_columns($columns)
    {
        return array_merge($columns, self::columns());
    }

    /**
     * Return the value for the currently exported plugin column.
     *
     * @param mixed     $value   Existing export value.
     * @param WC_Product $product Product being exported.
     * @return mixed
     */
    public function export_value($value, $product)
    {
        $column = str_replace('woocommerce_product_export_product_column_', '', current_filter());

        if ('armvw_model_url' === $column) {
            return $product->get_meta(Ar_Model_Viewer_For_Woocommerce_Product_Model::META_SOURCE, true, 'edit');
        }

        if ('armvw_poster_url' === $column) {
            return $product->get_meta(Ar_Model_Viewer_For_Woocommerce_Product_Model::META_POSTER, true, 'edit');
        }

        if ('armvw_model_alt' === $column) {
            return $product->get_meta(Ar_Model_Viewer_For_Woocommerce_Product_Model::META_ALT, true, 'edit');
        }

        if (0 === strpos($column, self::PREFIX)) {
            $short = substr($column, strlen(self::PREFIX));

            if (isset(Ar_Model_Viewer_For_Woocommerce_Product_Model::fields()[$short])) {
                return $product->get_meta(Ar_Model_Viewer_For_Woocommerce_Product_Model::meta_key($short), true, 'edit');
            }
        }

        return $value;
    }

    /**
     * Return an imported value while preserving a meta when the column was omitted.
     *
     * @param array  $data       Importer data.
     * @param string $new_key    Current CSV key.
     * @param string $legacy_key Historical CSV key.
     * @param mixed  $fallback   Existing product value.
     * @return mixed
     */
    private static function import_value($data, $new_key, $legacy_key, $fallback)
    {
        if (array_key_exists($new_key, $data)) {
            return $data[$new_key];
        }

        if (array_key_exists($legacy_key, $data)) {
            return $data[$legacy_key];
        }

        return $fallback;
    }
}