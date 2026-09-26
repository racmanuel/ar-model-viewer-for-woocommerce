<?php
/**
 * The markup of the metabox that attaches a model file to a product.
 *
 * This is the native replacement of the metabox the plugin used to build with a metabox library.
 * It keeps the same meta keys (`ar_model_viewer_for_woocommerce_file_object`, `_poster` and
 * `_alt`), so a product that already had a model keeps it untouched.
 *
 * The fields are text inputs next to a button that opens the media library: a URL can be typed or
 * pasted, which is what a store that serves its models from a CDN needs, and the button is only a
 * shortcut for the cases where the file was uploaded here.
 *
 * @link       https://racmanuel.dev
 * @since      3.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin/partials
 *
 * @var WC_Product $product Product being edited.
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!isset($product) || !$product instanceof WC_Product) {
    return;
}

$armvw_product_model = 'Ar_Model_Viewer_For_Woocommerce_Product_Model';

wp_nonce_field('armvw_save_model_files', 'armvw_model_files_nonce');

// The inputs carry what this product owns, not the value after the fallbacks: an empty field is
// how the store says "use the featured image" or "use the product name".
$armvw_source = (string) $product->get_meta(constant($armvw_product_model . '::META_SOURCE'), true);
$armvw_poster = (string) $product->get_meta(constant($armvw_product_model . '::META_POSTER'), true);
$armvw_alt = (string) $product->get_meta(constant($armvw_product_model . '::META_ALT'), true);

$armvw_resolved = call_user_func(array($armvw_product_model, 'resolve'), $product);

$armvw_icons = plugin_dir_url(dirname(__FILE__)) . 'images/';
?>
<table class="form-table" role="presentation">
    <tbody>
        <tr>
            <th scope="row">
                <label for="armvw-file-object">
                    <img src="<?php echo esc_url($armvw_icons . 'icons8-3d-94.png'); ?>" alt="" width="20" height="20" />
                    <?php esc_html_e('3D object file', 'ar-model-viewer-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input
                    type="text"
                    class="large-text"
                    id="armvw-file-object"
                    name="armvw_files[file_object]"
                    value="<?php echo esc_attr($armvw_source); ?>"
                    placeholder="https://example.com/modelo.glb"
                />
                <p>
                    <button
                        type="button"
                        class="button armvw-media"
                        data-armvw-target="armvw-file-object"
                        data-armvw-kind="model"
                        data-armvw-title="<?php echo esc_attr__('Select the 3D file', 'ar-model-viewer-for-woocommerce'); ?>"
                        data-armvw-button="<?php echo esc_attr__('Use this file', 'ar-model-viewer-for-woocommerce'); ?>"
                    >
                        <?php esc_html_e('Add or upload a file', 'ar-model-viewer-for-woocommerce'); ?>
                    </button>
                </p>
                <p class="description">
                    <?php esc_html_e('Upload or paste the URL of a .glb or .gltf file. Without it this product has no 3D viewer, and the button and the tab stay empty.', 'ar-model-viewer-for-woocommerce'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="armvw-file-poster">
                    <img src="<?php echo esc_url($armvw_icons . 'icons8-photo-gallery-94.png'); ?>" alt="" width="20" height="20" />
                    <?php esc_html_e('Poster', 'ar-model-viewer-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input
                    type="text"
                    class="large-text"
                    id="armvw-file-poster"
                    name="armvw_files[file_poster]"
                    value="<?php echo esc_attr($armvw_poster); ?>"
                    placeholder="https://example.com/poster.jpg"
                />
                <p>
                    <button
                        type="button"
                        class="button armvw-media"
                        data-armvw-target="armvw-file-poster"
                        data-armvw-kind="image"
                        data-armvw-title="<?php echo esc_attr__('Select the poster image', 'ar-model-viewer-for-woocommerce'); ?>"
                        data-armvw-button="<?php echo esc_attr__('Use this image', 'ar-model-viewer-for-woocommerce'); ?>"
                    >
                        <?php esc_html_e('Add or upload an image', 'ar-model-viewer-for-woocommerce'); ?>
                    </button>
                </p>
                <?php if ('' !== trim($armvw_poster)) : ?>
                    <img
                        src="<?php echo esc_url($armvw_poster); ?>"
                        alt=""
                        style="max-width: 150px; height: auto; display: block; margin: 8px 0; border: 1px solid #dcdcde; border-radius: 4px;"
                    />
                <?php endif; ?>
                <p class="description">
                    <?php
                    if ('featured' === $armvw_resolved['poster_source']) {
                        esc_html_e('The image shown while the model loads. It is empty, so the featured image of the product is being used.', 'ar-model-viewer-for-woocommerce');
                    } elseif ('none' === $armvw_resolved['poster_source']) {
                        esc_html_e('The image shown while the model loads. It is empty and the product has no featured image either, so the viewer renders on a plain background until the file arrives.', 'ar-model-viewer-for-woocommerce');
                    } else {
                        esc_html_e('The image shown while the model loads, before the visitor sees the model itself.', 'ar-model-viewer-for-woocommerce');
                    }
                    ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="armvw-file-alt">
                    <img src="<?php echo esc_url($armvw_icons . 'icons8-info-94.png'); ?>" alt="" width="20" height="20" />
                    <?php esc_html_e('Alt text', 'ar-model-viewer-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input
                    type="text"
                    class="large-text"
                    id="armvw-file-alt"
                    name="armvw_files[file_alt]"
                    value="<?php echo esc_attr($armvw_alt); ?>"
                    placeholder="<?php echo esc_attr($product->get_name()); ?>"
                />
                <p class="description">
                    <?php
                    if ('name' === $armvw_resolved['alt_source']) {
                        esc_html_e('Description of the model for screen readers and for the search engines that read it. It is empty, so the name of the product is being used.', 'ar-model-viewer-for-woocommerce');
                    } elseif ('description' === $armvw_resolved['alt_source']) {
                        esc_html_e('Description of the model for screen readers. It is empty, and so is the product name, so the short description is being used instead.', 'ar-model-viewer-for-woocommerce');
                    } else {
                        esc_html_e('Description of the model for screen readers and for the search engines that read it.', 'ar-model-viewer-for-woocommerce');
                    }
                    ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>
