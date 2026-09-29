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
            'armvw_enabled',
            'armvw_ar_enabled',
            'armvw_ar_scale',
            'armvw_ar_placement',
            'armvw_auto_rotate',
            'armvw_variation_custom',
            'armvw_variation_model_url',
            'armvw_variation_ios_src',
            'armvw_variation_poster_url',
        );
    }

    /**
     * Columns that only have a meaning on a variation row.
     *
     * @return array<int, string>
     */
    public static function variation_column_keys()
    {
        return array(
            'armvw_variation_custom',
            'armvw_variation_model_url',
            'armvw_variation_ios_src',
            'armvw_variation_poster_url',
        );
    }

    /**
     * Columns that hold the switches and the closed lists of a product.
     *
     * @return array<int, string>
     */
    public static function switch_column_keys()
    {
        return array(
            'armvw_enabled',
            'armvw_ar_enabled',
            'armvw_ar_scale',
            'armvw_ar_placement',
            'armvw_auto_rotate',
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
            'armvw_model_url' => __('AR Model Viewer: Model', 'ar-model-viewer-for-woocommerce'),
            'armvw_poster_url' => __('AR Model Viewer: Poster', 'ar-model-viewer-for-woocommerce'),
            'armvw_model_alt' => __('AR Model Viewer: Alt text', 'ar-model-viewer-for-woocommerce'),
            'armvw_enabled' => __('AR Model Viewer: Enabled', 'ar-model-viewer-for-woocommerce'),
            'armvw_ar_enabled' => __('AR Model Viewer: AR enabled', 'ar-model-viewer-for-woocommerce'),
            'armvw_ar_scale' => __('AR Model Viewer: AR scale', 'ar-model-viewer-for-woocommerce'),
            'armvw_ar_placement' => __('AR Model Viewer: AR placement', 'ar-model-viewer-for-woocommerce'),
            'armvw_auto_rotate' => __('AR Model Viewer: Auto rotate', 'ar-model-viewer-for-woocommerce'),
            'armvw_variation_custom' => __('AR Model Viewer: Variation uses its own files', 'ar-model-viewer-for-woocommerce'),
            'armvw_variation_model_url' => __('AR Model Viewer: Variation model', 'ar-model-viewer-for-woocommerce'),
            'armvw_variation_ios_src' => __('AR Model Viewer: Variation USDZ', 'ar-model-viewer-for-woocommerce'),
            'armvw_variation_poster_url' => __('AR Model Viewer: Variation poster', 'ar-model-viewer-for-woocommerce'),
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
        /*
         * WooCommerce fires this hook for the product and for each of its variation rows, so a
         * variation is answered with the variation branch and never writes on the parent by
         * mistake. A variation carries three files and one switch; everything else it inherits.
         */
        if ($product instanceof WC_Product_Variation) {
            $this->import_variation($product, $data);

            return;
        }

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

        /*
         * The switches keep the value they already had when the column is not part of the file, so
         * a CSV that only carries the model does not silently turn the viewer back on.
         */
        $switches = array();

        foreach (self::switch_column_keys() as $column) {
            $short = substr($column, strlen(self::PREFIX));
            $switches[$short] = array_key_exists($column, $data)
                ? $data[$column]
                : $product->get_meta(Ar_Model_Viewer_For_Woocommerce_Product_Model::meta_key($short), true);
        }

        Ar_Model_Viewer_For_Woocommerce_Product_Model::save_files($product->get_id(), $files);
        Ar_Model_Viewer_For_Woocommerce_Product_Model::save($product->get_id(), $options);
        Ar_Model_Viewer_For_Woocommerce_Product_Model::save_switches($product->get_id(), $switches);
    }

    /**
     * Save the viewer files of an imported variation.
     *
     * An absent column leaves the stored value alone and an empty column clears it, which is what
     * makes the difference between "this file never mentioned the field" and "the file says this
     * variation has no model of its own" survive a round trip.
     *
     * @param WC_Product_Variation $variation Imported variation.
     * @param array                $data      Raw importer data.
     * @return void
     */
    private function import_variation($variation, array $data)
    {
        if (!array_intersect(self::variation_column_keys(), array_keys($data))) {
            return;
        }

        $current = Ar_Model_Viewer_For_Woocommerce_Product_Model::variation_files($variation);

        $custom = array_key_exists('armvw_variation_custom', $data)
            ? $data['armvw_variation_custom']
            : ($current['custom'] ? 'yes' : '');

        $input = array(
            'custom' => self::is_truthy($custom) ? 'yes' : '',
            'file_object' => self::import_value($data, 'armvw_variation_model_url', 'armvw_variation_model_url', $current['file_object']),
            'ios_src' => self::import_value($data, 'armvw_variation_ios_src', 'armvw_variation_ios_src', $current['ios_src']),
            'file_poster' => self::import_value($data, 'armvw_variation_poster_url', 'armvw_variation_poster_url', $current['file_poster']),
        );

        /*
         * The attachment id of the file the CSV came from means nothing in this installation, so
         * it is looked up again from the URL. A URL that does not belong to this media library
         * simply leaves the id empty and the URL in place, which is what keeps a model served
         * from a CDN working.
         */
        $input['file_object_id'] = Ar_Model_Viewer_For_Woocommerce_Product_Model::attachment_id_from_url($input['file_object']);
        $input['ios_src_id'] = Ar_Model_Viewer_For_Woocommerce_Product_Model::attachment_id_from_url($input['ios_src']);
        $input['file_poster_id'] = Ar_Model_Viewer_For_Woocommerce_Product_Model::attachment_id_from_url($input['file_poster']);

        Ar_Model_Viewer_For_Woocommerce_Product_Model::save_variation($variation->get_id(), $input);
    }

    /**
     * Read a boolean out of a CSV cell.
     *
     * A spreadsheet writes `1`, `yes` or `true` for the same thing depending on the locale of the
     * person who filled it in, so all three are accepted.
     *
     * @param mixed $value Cell value.
     * @return bool True when the cell means "yes".
     */
    private static function is_truthy($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), array('1', 'yes', 'true', 'y', 'si', 'sí'), true);
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

        /*
         * The variation columns are only filled on a variation row, and the product switches only
         * on a product row. Exporting the switches on both would duplicate them, and a file that
         * was imported back would find two answers for the same question.
         */
        if (in_array($column, self::variation_column_keys(), true)) {
            return self::export_variation_value($column, $product);
        }

        if (in_array($column, self::switch_column_keys(), true)) {
            if ($product instanceof WC_Product_Variation) {
                return '';
            }

            return $product->get_meta(
                Ar_Model_Viewer_For_Woocommerce_Product_Model::meta_key(substr($column, strlen(self::PREFIX))),
                true,
                'edit'
            );
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
     * Return the value of a variation column for the row being exported.
     *
     * A variation that inherits exports empty cells, and that emptiness is the instruction the
     * importer reads to keep using the model of the parent. Writing the resolved model here would
     * turn every inheritance into a copy on the way back in.
     *
     * @param string     $column  Column being exported.
     * @param WC_Product $product Product of the row.
     * @return string
     */
    private static function export_variation_value($column, $product)
    {
        if (!$product instanceof WC_Product_Variation) {
            return '';
        }

        if ('armvw_variation_custom' === $column) {
            return Ar_Model_Viewer_For_Woocommerce_Product_Model::has_custom_configuration($product) ? 'yes' : '';
        }

        $keys = array(
            'armvw_variation_model_url' => Ar_Model_Viewer_For_Woocommerce_Product_Model::META_VARIATION_SOURCE,
            'armvw_variation_ios_src' => Ar_Model_Viewer_For_Woocommerce_Product_Model::META_VARIATION_IOS_SRC,
            'armvw_variation_poster_url' => Ar_Model_Viewer_For_Woocommerce_Product_Model::META_VARIATION_POSTER,
        );

        if (!isset($keys[$column])) {
            return '';
        }

        return (string) $product->get_meta($keys[$column], true, 'edit');
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