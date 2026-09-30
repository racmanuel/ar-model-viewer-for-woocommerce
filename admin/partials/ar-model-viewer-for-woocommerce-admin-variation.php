<?php
/**
 * The 3D and AR fields of a single WooCommerce variation.
 *
 * A variation is not a small product: it is the same product in another colour or size, so it
 * mostly inherits. That is why the block opens with a switch instead of with three empty file
 * fields. While the switch is off the variation uses the model of its parent, and the file fields
 * stay hidden so the store is not asked to fill in values that will be ignored.
 *
 * The markup follows the one of the variation panel it lives in: `form-row form-row-full`, the
 * same rows WooCommerce uses for SKU, price or weight. The names are indexed by `$loop` because
 * every variation prints the same form on one page.
 *
 * @link       https://racmanuel.dev
 * @since      3.2.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin/partials
 *
 * @var int                  $loop           Index of the variation in the list.
 * @var array                $variation_data Raw variation data.
 * @var WC_Product_Variation|WP_Post $variation Variation being edited.
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/*
 * WooCommerce renders this template along two paths that do not agree on what `$variation` is: on
 * the first load of the page it is a `WC_Product_Variation`, and when the variations are loaded by
 * AJAX it is the `WP_Post` behind it. Reading it as a product only in the first case is how a
 * block disappears from the editor without a single error being logged, so both are accepted.
 */
if ($variation instanceof WC_Product_Variation) {
    $ar_model_viewer_for_woocommerce_variation = $variation;
} elseif (is_object($variation) && isset($variation->ID)) {
    $ar_model_viewer_for_woocommerce_variation = wc_get_product($variation->ID);
} else {
    $ar_model_viewer_for_woocommerce_variation = null;
}

if (!$ar_model_viewer_for_woocommerce_variation instanceof WC_Product_Variation) {
    return;
}

$ar_model_viewer_for_woocommerce_loop = isset($loop) ? absint($loop) : 0;
$ar_model_viewer_for_woocommerce_files = Ar_Model_Viewer_For_Woocommerce_Product_Model::variation_files($ar_model_viewer_for_woocommerce_variation);
$ar_model_viewer_for_woocommerce_scope = 'armvw_variation[' . $ar_model_viewer_for_woocommerce_loop . ']';
$ar_model_viewer_for_woocommerce_id = 'armvw-variation-' . $ar_model_viewer_for_woocommerce_loop;

$ar_model_viewer_for_woocommerce_resources = array(
    array(
        'suffix' => 'model',
        'label' => __('3D model', 'ar-model-viewer-for-woocommerce'),
        'placeholder' => 'https://example.com/modelo.glb',
        'kind' => 'model',
        'title' => __('Select the 3D file', 'ar-model-viewer-for-woocommerce'),
        'button' => __('Select file', 'ar-model-viewer-for-woocommerce'),
        'key' => 'file_object',
        'id_key' => 'file_object_id',
        'value' => $ar_model_viewer_for_woocommerce_files['file_object'],
        'attachment' => $ar_model_viewer_for_woocommerce_files['file_object_id'],
    ),
    array(
        'suffix' => 'usdz',
        'label' => __('USDZ file for iPhone', 'ar-model-viewer-for-woocommerce'),
        'placeholder' => 'https://example.com/modelo.usdz',
        'kind' => 'model',
        'title' => __('Select the USDZ file', 'ar-model-viewer-for-woocommerce'),
        'button' => __('Select file', 'ar-model-viewer-for-woocommerce'),
        'key' => 'ios_src',
        'id_key' => 'ios_src_id',
        'value' => $ar_model_viewer_for_woocommerce_files['ios_src'],
        'attachment' => $ar_model_viewer_for_woocommerce_files['ios_src_id'],
    ),
    array(
        'suffix' => 'poster',
        'label' => __('Poster', 'ar-model-viewer-for-woocommerce'),
        'placeholder' => 'https://example.com/poster.jpg',
        'kind' => 'image',
        'title' => __('Select the poster image', 'ar-model-viewer-for-woocommerce'),
        'button' => __('Select image', 'ar-model-viewer-for-woocommerce'),
        'key' => 'file_poster',
        'id_key' => 'file_poster_id',
        'value' => $ar_model_viewer_for_woocommerce_files['file_poster'],
        'attachment' => $ar_model_viewer_for_woocommerce_files['file_poster_id'],
    ),
);
?>
<div class="armvw-variation">
	<p class="form-row form-row-full options">
		<label for="<?php echo esc_attr($ar_model_viewer_for_woocommerce_id . '-custom'); ?>">
			<span class="dashicons dashicons-screenoptions" aria-hidden="true"></span>
			<?php esc_html_e('AR Model Viewer', 'ar-model-viewer-for-woocommerce'); ?>
		</label>
		<label class="armvw-variation__toggle" for="<?php echo esc_attr($ar_model_viewer_for_woocommerce_id . '-custom'); ?>">
			<input
				type="checkbox"
				class="checkbox armvw-variation__custom"
				id="<?php echo esc_attr($ar_model_viewer_for_woocommerce_id . '-custom'); ?>"
				name="<?php echo esc_attr($ar_model_viewer_for_woocommerce_scope . '[custom]'); ?>"
				value="yes"
				<?php checked(true, $ar_model_viewer_for_woocommerce_files['custom']); ?>
			/>
			<?php esc_html_e('Use a custom 3D model for this variation', 'ar-model-viewer-for-woocommerce'); ?>
		</label>
		<?php echo wp_kses_post(wc_help_tip(__('Leave this off to use the model, the USDZ file and the poster of the parent product. Turn it on to give this variation its own files; any field you leave empty still falls back to the parent.', 'ar-model-viewer-for-woocommerce'))); ?>
	</p>

	<div class="armvw-variation__files form-row form-row-full" <?php echo $ar_model_viewer_for_woocommerce_files['custom'] ? '' : 'hidden'; ?>>
		<?php foreach ($ar_model_viewer_for_woocommerce_resources as $ar_model_viewer_for_woocommerce_resource) : ?>
			<?php
            $ar_model_viewer_for_woocommerce_field_id = $ar_model_viewer_for_woocommerce_id . '-' . $ar_model_viewer_for_woocommerce_resource['suffix'];
            $ar_model_viewer_for_woocommerce_id_name = $ar_model_viewer_for_woocommerce_scope . '[' . $ar_model_viewer_for_woocommerce_resource['id_key'] . ']';
            ?>
			<p class="form-row form-row-full armvw-media-row armvw-resource-field">
				<label for="<?php echo esc_attr($ar_model_viewer_for_woocommerce_field_id); ?>"><?php echo esc_html($ar_model_viewer_for_woocommerce_resource['label']); ?></label>
				<input
					type="text"
					class="short"
					id="<?php echo esc_attr($ar_model_viewer_for_woocommerce_field_id); ?>"
					name="<?php echo esc_attr($ar_model_viewer_for_woocommerce_scope . '[' . $ar_model_viewer_for_woocommerce_resource['key'] . ']'); ?>"
					value="<?php echo esc_attr($ar_model_viewer_for_woocommerce_resource['value']); ?>"
					placeholder="<?php echo esc_attr($ar_model_viewer_for_woocommerce_resource['placeholder']); ?>"
				/>
				<input type="hidden" id="<?php echo esc_attr($ar_model_viewer_for_woocommerce_field_id . '-id'); ?>" name="<?php echo esc_attr($ar_model_viewer_for_woocommerce_id_name); ?>" value="<?php echo esc_attr((string) $ar_model_viewer_for_woocommerce_resource['attachment']); ?>" />
				<button
					type="button"
					class="button armvw-media"
					data-armvw-target="<?php echo esc_attr($ar_model_viewer_for_woocommerce_field_id); ?>"
					data-armvw-id="<?php echo esc_attr($ar_model_viewer_for_woocommerce_field_id . '-id'); ?>"
					data-armvw-kind="<?php echo esc_attr($ar_model_viewer_for_woocommerce_resource['kind']); ?>"
					data-armvw-title="<?php echo esc_attr($ar_model_viewer_for_woocommerce_resource['title']); ?>"
					data-armvw-button="<?php echo esc_attr($ar_model_viewer_for_woocommerce_resource['button']); ?>"
				>
					<?php echo esc_html($ar_model_viewer_for_woocommerce_resource['button']); ?>
				</button>
			</p>
		<?php endforeach; ?>
	</div>
</div>
