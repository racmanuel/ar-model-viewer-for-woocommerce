<?php
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- These are scoped template variables with the plugin's established armvw prefix.
/**
 * The 3D and AR fields of a product.
 *
 * They live inside the General tab of Product data, next to the fields WooCommerce already puts
 * there, and they are built with the same markup: an `options_group`, `form-field` rows, `short`
 * inputs and `description` lines.
 *
 * The list is deliberately short. A product has to answer four questions: which file, what the
 * shopper sees while it loads, whether augmented reality is offered, and how it behaves in it.
 * Everything else is either derived from those answers or belongs to the settings screen, and a
 * field nobody understands is a field that ends up filled in wrong.
 *
 * The markup is written by hand instead of through `woocommerce_wp_text_input()` because every
 * resource needs two inputs and a button in the same row: the visible URL and the id of the
 * attachment behind it, which is bookkeeping the store never types.
 *
 * @link       https://racmanuel.dev
 * @since      3.2.0
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

$armvw_owner = 'Ar_Model_Viewer_For_Woocommerce_Product_Model';

wp_nonce_field('armvw_save_viewer_options', 'armvw_viewer_nonce');

// The inputs carry what this product owns, not the value after the fallbacks: an empty field is
// how the store says "use the featured image" or "use the product name".
$armvw_source = (string) $product->get_meta($armvw_owner::META_SOURCE, true);
$armvw_poster = (string) $product->get_meta($armvw_owner::META_POSTER, true);

$armvw_ids = array(
    'source' => (int) $product->get_meta($armvw_owner::META_ATTACHMENT . 'source', true),
    'poster' => (int) $product->get_meta($armvw_owner::META_ATTACHMENT . 'poster', true),
    'ios_src' => (int) $product->get_meta($armvw_owner::META_ATTACHMENT . 'ios_src', true),
);

$armvw_resolved = $armvw_owner::resolve($product);
$armvw_diagnostics = $armvw_owner::diagnostics($product);
$armvw_fields = $armvw_owner::fields();
$armvw_switches = $armvw_owner::switches();
$armvw_selects = $armvw_owner::selects();

$armvw_ios_src = $armvw_owner::read($product, 'ios_src', 'resource');
$armvw_ios_src = is_string($armvw_ios_src) ? $armvw_ios_src : '';

$armvw_camera = $armvw_owner::read($product, 'camera_orbit', $armvw_fields['camera_orbit']['type']);
$armvw_camera = is_string($armvw_camera) ? $armvw_camera : '';

/*
 * The state of the model is attached to the field it talks about instead of living in a banner of
 * its own. It is the one piece of information that has to be read before touching anything, and
 * the description line of the model field is where the store is already looking.
 */
switch ($armvw_diagnostics['status']) {
    case 'missing':
        $armvw_model_note = __('No 3D file yet. The viewer is not offered on this product until you add one.', 'ar-model-viewer-for-woocommerce');
        break;

    case 'valid':
        $armvw_model_note = 'featured' === $armvw_resolved['poster_source']
            ? __('Ready. The viewer loads with the featured image of the product as poster.', 'ar-model-viewer-for-woocommerce')
            : __('Ready. The viewer uses the poster selected below.', 'ar-model-viewer-for-woocommerce');
        break;

    case 'unsupported':
        $armvw_model_note = __('The model resources use an unsupported file format. Use GLB or glTF for the model, an image for the poster and USDZ for iPhone AR.', 'ar-model-viewer-for-woocommerce');
        break;

    default:
        $armvw_model_note = __('The model URL needs attention. Use an absolute URL with a supported file extension; the product can still be saved while you fix it.', 'ar-model-viewer-for-woocommerce');
        break;
}
?>
<p class="form-field armvw-section-heading">
	<strong><?php esc_html_e('3D and augmented reality', 'ar-model-viewer-for-woocommerce'); ?></strong>
</p>

<div class="options_group armvw-product-fields armvw-product-fields--resources">
	<p class="form-field armvw-resource-field armvw-resource-field--enabled">
		<label for="armvw-product-enabled"><?php echo esc_html($armvw_switches['enabled']['label']); ?></label>
		<input type="hidden" name="armvw_switches[enabled]" value="no" />
		<input
			type="checkbox"
			id="armvw-product-enabled"
			name="armvw_switches[enabled]"
			value="yes"
			<?php checked($armvw_owner::is_enabled($product)); ?>
		/>
		<?php echo wp_kses_post(wc_help_tip($armvw_switches['enabled']['help'])); ?>
	</p>

	<p class="form-field armvw-resource-field">
		<label for="armvw-file-object"><?php esc_html_e('3D model file', 'ar-model-viewer-for-woocommerce'); ?></label>
		<input
			type="text"
			class="short"
			id="armvw-file-object"
			name="armvw_files[file_object]"
			value="<?php echo esc_attr($armvw_source); ?>"
			placeholder="https://example.com/modelo.glb"
		/>
		<input type="hidden" id="armvw-file-object-id" name="armvw_files[file_object_id]" value="<?php echo esc_attr((string) $armvw_ids['source']); ?>" />
		<button
			type="button"
			class="button armvw-media"
			data-armvw-target="armvw-file-object"
			data-armvw-id="armvw-file-object-id"
			data-armvw-kind="model"
			data-armvw-title="<?php echo esc_attr__('Select the 3D file', 'ar-model-viewer-for-woocommerce'); ?>"
			data-armvw-button="<?php echo esc_attr__('Use this file', 'ar-model-viewer-for-woocommerce'); ?>"
		>
			<?php esc_html_e('Select .glb or .gltf', 'ar-model-viewer-for-woocommerce'); ?>
		</button>
		<?php echo wp_kses_post(wc_help_tip($armvw_model_note)); ?>
	</p>

	<p class="form-field armvw-resource-field">
		<label for="armvw-product-ios-src"><?php echo esc_html($armvw_fields['ios_src']['label']); ?></label>
		<input
			type="text"
			class="short"
			id="armvw-product-ios-src"
			name="armvw_product[ios_src]"
			value="<?php echo esc_attr($armvw_ios_src); ?>"
			placeholder="<?php echo esc_attr($armvw_fields['ios_src']['placeholder']); ?>"
		/>
		<input type="hidden" id="armvw-product-ios-src-id" name="armvw_product[ios_src_id]" value="<?php echo esc_attr((string) $armvw_ids['ios_src']); ?>" />
		<button
			type="button"
			class="button armvw-media"
			data-armvw-target="armvw-product-ios-src"
			data-armvw-id="armvw-product-ios-src-id"
			data-armvw-kind="usdz"
			data-armvw-title="<?php echo esc_attr__('Select the USDZ file', 'ar-model-viewer-for-woocommerce'); ?>"
			data-armvw-button="<?php echo esc_attr__('Use this file', 'ar-model-viewer-for-woocommerce'); ?>"
		>
			<?php esc_html_e('Select .usdz', 'ar-model-viewer-for-woocommerce'); ?>
		</button>
		<?php echo wp_kses_post(wc_help_tip($armvw_fields['ios_src']['help'])); ?>
	</p>

	<p class="form-field armvw-resource-field">
		<label for="armvw-file-poster"><?php esc_html_e('Poster', 'ar-model-viewer-for-woocommerce'); ?></label>
		<input
			type="text"
			class="short"
			id="armvw-file-poster"
			name="armvw_files[file_poster]"
			value="<?php echo esc_attr($armvw_poster); ?>"
			placeholder="https://example.com/poster.jpg"
		/>
		<input type="hidden" id="armvw-file-poster-id" name="armvw_files[file_poster_id]" value="<?php echo esc_attr((string) $armvw_ids['poster']); ?>" />
		<button
			type="button"
			class="button armvw-media"
			data-armvw-target="armvw-file-poster"
			data-armvw-id="armvw-file-poster-id"
			data-armvw-kind="image"
			data-armvw-title="<?php echo esc_attr__('Select the poster image', 'ar-model-viewer-for-woocommerce'); ?>"
			data-armvw-button="<?php echo esc_attr__('Use this image', 'ar-model-viewer-for-woocommerce'); ?>"
		>
			<?php esc_html_e('Select an image', 'ar-model-viewer-for-woocommerce'); ?>
		</button>
		<?php
		echo wp_kses_post(wc_help_tip(
			'featured' === $armvw_resolved['poster_source']
				? __('Shown while the model loads. It is empty, so the featured image of the product is being used.', 'ar-model-viewer-for-woocommerce')
				: __('Shown while the model loads. An absolute URL of a JPG, PNG, WebP or AVIF image.', 'ar-model-viewer-for-woocommerce')
		));
		?>
		<?php if ('' !== trim($armvw_poster) || ('featured' === $armvw_resolved['poster_source'] && '' !== trim($armvw_resolved['poster']))) : ?>
			<img class="armvw-thumb" src="<?php echo esc_url('' !== trim($armvw_poster) ? $armvw_poster : $armvw_resolved['poster']); ?>" alt="" />
		<?php endif; ?>
	</p>
</div>

<div class="options_group armvw-product-fields armvw-product-fields--behavior">
	<?php
    /*
     * The switches keep a third answer, "use the global setting", instead of being a plain
     * checkbox. Augmented reality is configured once in the settings screen and a product only has
     * to speak up when it is the exception. A checkbox would force every product of a catalogue to
     * repeat the same decision, and the first product that forgot would silently lose the feature.
     */
    woocommerce_wp_select(
        array(
            'id' => 'armvw-product-ar-enabled',
            'name' => 'armvw_switches[ar_enabled]',
            'value' => $armvw_owner::switch_value($product, 'ar_enabled'),
            'label' => $armvw_switches['ar_enabled']['label'],
            'description' => $armvw_switches['ar_enabled']['help'],
			'desc_tip' => true,
            'options' => array(
                '' => __('Use the global setting', 'ar-model-viewer-for-woocommerce'),
                'yes' => __('Enabled', 'ar-model-viewer-for-woocommerce'),
                'no' => __('Disabled', 'ar-model-viewer-for-woocommerce'),
            ),
        )
    );

    woocommerce_wp_select(
        array(
            'id' => 'armvw-product-auto-rotate',
            'name' => 'armvw_switches[auto_rotate]',
            'value' => $armvw_owner::switch_value($product, 'auto_rotate'),
            'label' => $armvw_switches['auto_rotate']['label'],
            'description' => $armvw_switches['auto_rotate']['help'],
			'desc_tip' => true,
            'options' => array(
                '' => __('Use the global setting', 'ar-model-viewer-for-woocommerce'),
                'yes' => __('Enabled', 'ar-model-viewer-for-woocommerce'),
                'no' => __('Disabled', 'ar-model-viewer-for-woocommerce'),
            ),
        )
    );

    foreach (array('ar_scale', 'ar_placement') as $armvw_select_short) {
        $armvw_select = $armvw_selects[$armvw_select_short];

        woocommerce_wp_select(
            array(
                'id' => 'armvw-product-' . str_replace('_', '-', $armvw_select_short),
                'name' => 'armvw_switches[' . $armvw_select_short . ']',
                'value' => $armvw_owner::select_value($product, $armvw_select_short),
                'label' => $armvw_select['label'],
                'description' => $armvw_select['help'],
				'desc_tip' => true,
                'options' => $armvw_select['options'],
            )
        );
    }

    woocommerce_wp_text_input(
        array(
            'id' => 'armvw-product-camera-orbit',
            'name' => 'armvw_product[camera_orbit]',
            'value' => $armvw_camera,
            'label' => $armvw_fields['camera_orbit']['label'],
            'placeholder' => $armvw_fields['camera_orbit']['placeholder'],
            'description' => $armvw_fields['camera_orbit']['help'],
			'desc_tip' => true,
        )
    );
    ?>
</div>

<div class="options_group armvw-product-fields armvw-product-fields--preview">
	<p class="form-field">
		<label><?php esc_html_e('Preview', 'ar-model-viewer-for-woocommerce'); ?></label>
		<button type="button" class="button" id="armvw-use-current-view">
			<?php esc_html_e('Use the current view as the starting camera', 'ar-model-viewer-for-woocommerce'); ?>
		</button>
		<button
			type="button"
			class="button"
			id="ar_model_viewer_for_woocommerce_product_preview"
			data-product-id="<?php echo esc_attr((string) $product->get_id()); ?>"
		>
			<?php esc_html_e('Preview the model', 'ar-model-viewer-for-woocommerce'); ?>
		</button>
		<?php echo wp_kses_post(wc_help_tip(__('Save the product before previewing, so the viewer reads the files you have just chosen.', 'ar-model-viewer-for-woocommerce'))); ?>
	</p>
</div>
