<?php
/**
 * The markup of the metabox that holds the viewer options of a single product.
 *
 * Every field is empty by default and an empty field means "do not print this attribute": the
 * viewer keeps the value documented by the library. That is why the form never forces a value
 * and why a product that overrides nothing looks exactly like it did before this metabox
 * existed.
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

wp_nonce_field('armvw_save_3d_options', 'armvw_3d_options_nonce');

$armvw_fields = Ar_Model_Viewer_For_Woocommerce_Product_Model::fields();
?>
<p class="description">
    <?php esc_html_e('These options belong to this product only. Leave a field empty to let the viewer decide, which is the right choice unless the model needs a specific camera or arrives in the wrong orientation.', 'ar-model-viewer-for-woocommerce'); ?>
</p>

<table class="form-table" role="presentation">
    <tbody>
        <?php foreach ($armvw_fields as $armvw_short => $armvw_field) : ?>
            <?php
            $armvw_value = Ar_Model_Viewer_For_Woocommerce_Product_Model::read($product, $armvw_short, $armvw_field['type']);
            $armvw_id = 'armvw-product-' . str_replace('_', '-', $armvw_short);
            $armvw_name = 'armvw_product[' . $armvw_short . ']';
            ?>
            <tr>
                <th scope="row">
                    <label for="<?php echo esc_attr($armvw_id); ?>"><?php echo esc_html($armvw_field['label']); ?></label>
                </th>
                <td>
                    <?php if ('toggle' === $armvw_field['type']) : ?>
                        <label for="<?php echo esc_attr($armvw_id); ?>">
                            <input
                                type="checkbox"
                                id="<?php echo esc_attr($armvw_id); ?>"
                                name="<?php echo esc_attr($armvw_name); ?>"
                                value="yes"
                                <?php checked(true === $armvw_value); ?>
                            />
                            <?php esc_html_e('Enabled for this product', 'ar-model-viewer-for-woocommerce'); ?>
                        </label>
                    <?php else : ?>
                        <input
                            type="text"
                            class="regular-text"
                            id="<?php echo esc_attr($armvw_id); ?>"
                            name="<?php echo esc_attr($armvw_name); ?>"
                            value="<?php echo esc_attr(is_string($armvw_value) ? $armvw_value : ''); ?>"
                            <?php echo '' !== $armvw_field['placeholder'] ? ' placeholder="' . esc_attr($armvw_field['placeholder']) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the expression. ?>
                        />
                    <?php endif; ?>
                    <p class="description"><?php echo wp_kses_post($armvw_field['help']); ?></p>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p>
    <button type="button" class="button" id="armvw-use-current-view">
        <?php esc_html_e('Use the current view as the starting camera', 'ar-model-viewer-for-woocommerce'); ?>
    </button>
</p>
<p class="description">
    <?php esc_html_e('Open the 3D preview above, move the model until you see the angle you want to sell from, and press this button: the camera position, the target and the field of view are copied into the fields so you do not have to guess the numbers.', 'ar-model-viewer-for-woocommerce'); ?>
</p>
