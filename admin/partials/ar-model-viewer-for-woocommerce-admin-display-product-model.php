<?php
/**
 * The markup of the metabox that holds every viewer option of a product.
 *
 * There is a single metabox on purpose: the file that attaches the model and the values that
 * describe how to look at it belong to the same decision, and asking for them in two separate
 * boxes made the store fill two forms to configure one viewer.
 *
 * The markup reuses the cards, fields and controls of the settings screen (`armvw-card`,
 * `armvw-field`, `armvw-input`), which is why editing a product looks like the panel where the
 * defaults are set. The rules of that screen that are global are scoped with
 * `.armvw-settings-page`, so they do not leak into the editor.
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

$armvw_model_class = 'Ar_Model_Viewer_For_Woocommerce_Product_Model';

wp_nonce_field('armvw_save_viewer_options', 'armvw_viewer_nonce');

// The inputs carry what this product owns, not the value after the fallbacks: an empty field is
// how the store says "use the featured image" or "use the product name".
$armvw_source = (string) $product->get_meta(constant($armvw_model_class . '::META_SOURCE'), true);
$armvw_poster = (string) $product->get_meta(constant($armvw_model_class . '::META_POSTER'), true);
$armvw_alt = (string) $product->get_meta(constant($armvw_model_class . '::META_ALT'), true);

$armvw_resolved = call_user_func(array($armvw_model_class, 'resolve'), $product);
$armvw_fields = call_user_func(array($armvw_model_class, 'fields'));

// The tabs group the fields the way the settings screen does, so a product does not show one
// endless form. The icons are the family used by the cards and the tabs of that screen.
$armvw_tabs = array(
    'model' => array(
        'label' => __('Model files', 'ar-model-viewer-for-woocommerce'),
        'icon' => 'dashicons-screenoptions',
    ),
    'camera' => array(
        'label' => __('Camera', 'ar-model-viewer-for-woocommerce'),
        'icon' => 'dashicons-camera',
    ),
    'transform' => array(
        'label' => __('Transform', 'ar-model-viewer-for-woocommerce'),
        'icon' => 'dashicons-image-rotate',
    ),
    'ar' => array(
        'label' => __('iPhone AR', 'ar-model-viewer-for-woocommerce'),
        'icon' => 'dashicons-smartphone',
    ),
);

// Every field carries an icon, the way the tabs and the cards do on the settings screen.
$armvw_field_icons = array(
    'camera_orbit' => 'dashicons-camera',
    'camera_target' => 'dashicons-screenoptions',
    'field_of_view' => 'dashicons-visibility',
    'orientation' => 'dashicons-image-rotate',
    'scale' => 'dashicons-editor-expand',
    'animation_name' => 'dashicons-controls-play',
    'variant_name' => 'dashicons-art',
    'ios_src' => 'dashicons-smartphone',
);

// One line that answers the only question worth answering at a glance: what will the viewer do
// with what this product has right now.
if ('' === trim($armvw_source)) {
    $armvw_state = array(
        'tone' => 'warn',
        'text' => __('No 3D file yet. The button and the product tab stay empty until you add one.', 'ar-model-viewer-for-woocommerce'),
    );
} elseif ('featured' === $armvw_resolved['poster_source']) {
    $armvw_state = array(
        'tone' => 'ok',
        'text' => __('Ready. The viewer loads with the featured image of the product as poster.', 'ar-model-viewer-for-woocommerce'),
    );
} elseif ('none' === $armvw_resolved['poster_source']) {
    $armvw_state = array(
        'tone' => 'warn',
        'text' => __('Ready, but there is no poster and the product has no featured image, so the viewer renders on a plain background until the file arrives.', 'ar-model-viewer-for-woocommerce'),
    );
} else {
    $armvw_state = array(
        'tone' => 'ok',
        'text' => __('Ready. The viewer uses the poster selected below.', 'ar-model-viewer-for-woocommerce'),
    );
}
?>
<div class="armvw-metabox">
    <p class="armvw-metabox__state armvw-metabox__state--<?php echo esc_attr($armvw_state['tone']); ?>">
        <?php echo esc_html($armvw_state['text']); ?>
    </p>

    <nav class="armvw-tabs" role="tablist" aria-label="<?php echo esc_attr__('Viewer options', 'ar-model-viewer-for-woocommerce'); ?>">
        <?php $armvw_first = true; ?>
        <?php foreach ($armvw_tabs as $armvw_slug => $armvw_tab) : ?>
            <button
                type="button"
                class="armvw-tab<?php echo $armvw_first ? ' is-active' : ''; ?>"
                id="armvw-tab-<?php echo esc_attr($armvw_slug); ?>"
                data-armvw-tab="<?php echo esc_attr($armvw_slug); ?>"
                role="tab"
                aria-controls="armvw-panel-<?php echo esc_attr($armvw_slug); ?>"
                aria-selected="<?php echo $armvw_first ? 'true' : 'false'; ?>"
                tabindex="<?php echo $armvw_first ? '0' : '-1'; ?>"
            >
                <span class="dashicons <?php echo esc_attr($armvw_tab['icon']); ?>" aria-hidden="true"></span>
                <?php echo esc_html($armvw_tab['label']); ?>
            </button>
            <?php $armvw_first = false; ?>
        <?php endforeach; ?>
    </nav>

    <section class="armvw-panel" id="armvw-panel-model" role="tabpanel" aria-labelledby="armvw-tab-model" tabindex="0">
    <div class="armvw-card">
        <div class="armvw-card__header">
            <span class="dashicons dashicons-screenoptions" aria-hidden="true"></span>
            <div>
                <h3 class="armvw-card__title"><?php esc_html_e('Model files', 'ar-model-viewer-for-woocommerce'); ?></h3>
                <p class="armvw-card__desc"><?php esc_html_e('Upload or paste the URL of a .glb file, and decide what the shopper sees while it loads.', 'ar-model-viewer-for-woocommerce'); ?></p>
            </div>
        </div>

        <div class="armvw-field">
            <div class="armvw-field__main">
                <label class="armvw-field__label" for="armvw-file-object">
                    <span class="dashicons dashicons-media-default" aria-hidden="true"></span>
                    <?php esc_html_e('3D object file', 'ar-model-viewer-for-woocommerce'); ?>
                </label>
                <p class="armvw-field__desc"><?php esc_html_e('Without this file the product has no 3D viewer at all, and the button and the tab stay empty.', 'ar-model-viewer-for-woocommerce'); ?></p>
            </div>
            <div class="armvw-field__control">
                <input
                    type="text"
                    class="armvw-input"
                    id="armvw-file-object"
                    name="armvw_files[file_object]"
                    value="<?php echo esc_attr($armvw_source); ?>"
                    placeholder="https://example.com/modelo.glb"
                />
                <p class="armvw-field__actions">
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
            </div>
        </div>

        <div class="armvw-field">
            <div class="armvw-field__main">
                <label class="armvw-field__label" for="armvw-file-poster">
                    <span class="dashicons dashicons-format-image" aria-hidden="true"></span>
                    <?php esc_html_e('Poster', 'ar-model-viewer-for-woocommerce'); ?>
                </label>
                <p class="armvw-field__desc"><?php esc_html_e('Image shown while the model loads. Leave it empty to use the featured image of the product.', 'ar-model-viewer-for-woocommerce'); ?></p>
            </div>
            <div class="armvw-field__control">
                <input
                    type="text"
                    class="armvw-input"
                    id="armvw-file-poster"
                    name="armvw_files[file_poster]"
                    value="<?php echo esc_attr($armvw_poster); ?>"
                    placeholder="https://example.com/poster.jpg"
                />
                <p class="armvw-field__actions">
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
                <?php if ('featured' === $armvw_resolved['poster_source']) : ?>
                    <?php if ('' !== trim($armvw_resolved['poster'])) : ?>
                        <img class="armvw-thumb" src="<?php echo esc_url($armvw_resolved['poster']); ?>" alt="" />
                    <?php endif; ?>
                    <p class="armvw-field__desc"><?php esc_html_e('Currently using the featured image of the product.', 'ar-model-viewer-for-woocommerce'); ?></p>
                <?php elseif ('' !== trim($armvw_poster)) : ?>
                    <img class="armvw-thumb" src="<?php echo esc_url($armvw_poster); ?>" alt="" />
                <?php endif; ?>
            </div>
        </div>

        <div class="armvw-field">
            <div class="armvw-field__main">
                <label class="armvw-field__label" for="armvw-file-alt">
                    <span class="dashicons dashicons-info" aria-hidden="true"></span>
                    <?php esc_html_e('Alt text', 'ar-model-viewer-for-woocommerce'); ?>
                </label>
                <p class="armvw-field__desc">
                    <?php
                    if ('name' === $armvw_resolved['alt_source']) {
                        esc_html_e('Description of the model for screen readers. It is empty, so the name of the product is being used.', 'ar-model-viewer-for-woocommerce');
                    } elseif ('description' === $armvw_resolved['alt_source']) {
                        esc_html_e('Description of the model for screen readers. It is empty, and so is the product name, so the short description is being used instead.', 'ar-model-viewer-for-woocommerce');
                    } else {
                        esc_html_e('Description of the model for screen readers and for the search engines that read it.', 'ar-model-viewer-for-woocommerce');
                    }
                    ?>
                </p>
            </div>
            <div class="armvw-field__control">
                <input
                    type="text"
                    class="armvw-input"
                    id="armvw-file-alt"
                    name="armvw_files[file_alt]"
                    value="<?php echo esc_attr($armvw_alt); ?>"
                    placeholder="<?php echo esc_attr($product->get_name()); ?>"
                />
            </div>
        </div>
    </div>

    </section>

    <section class="armvw-panel" id="armvw-panel-camera" role="tabpanel" aria-labelledby="armvw-tab-camera" tabindex="0" hidden>
    <div class="armvw-card">
        <div class="armvw-card__header">
            <span class="dashicons dashicons-camera" aria-hidden="true"></span>
            <div>
                <h3 class="armvw-card__title"><?php esc_html_e('Camera', 'ar-model-viewer-for-woocommerce'); ?></h3>
                <p class="armvw-card__desc"><?php esc_html_e('Where the model is looked at from when the viewer opens. Instead of guessing the numbers, move the model in the preview above and copy the result.', 'ar-model-viewer-for-woocommerce'); ?></p>
            </div>
        </div>

        <p class="armvw-field__actions">
            <button type="button" class="button button-secondary" id="armvw-use-current-view">
                <?php esc_html_e('Use the current view as the starting camera', 'ar-model-viewer-for-woocommerce'); ?>
            </button>
        </p>

        <?php foreach (array('camera_orbit', 'camera_target', 'field_of_view') as $armvw_short) : ?>
            <?php
            $armvw_field = $armvw_fields[$armvw_short];
            $armvw_id = 'armvw-product-' . str_replace('_', '-', $armvw_short);
            $armvw_value = call_user_func(array($armvw_model_class, 'read'), $product, $armvw_short, $armvw_field['type']);
            ?>
            <div class="armvw-field">
                <div class="armvw-field__main">
                    <label class="armvw-field__label" for="<?php echo esc_attr($armvw_id); ?>">
                        <span class="dashicons <?php echo esc_attr($armvw_field_icons[$armvw_short]); ?>" aria-hidden="true"></span>
                        <?php echo esc_html($armvw_field['label']); ?>
                    </label>
                    <p class="armvw-field__desc"><?php echo wp_kses_post($armvw_field['help']); ?></p>
                </div>
                <div class="armvw-field__control">
                    <input
                        type="text"
                        class="armvw-input"
                        id="<?php echo esc_attr($armvw_id); ?>"
                        name="armvw_product[<?php echo esc_attr($armvw_short); ?>]"
                        value="<?php echo esc_attr(is_string($armvw_value) ? $armvw_value : ''); ?>"
                        placeholder="<?php echo esc_attr($armvw_field['placeholder']); ?>"
                    />
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    </section>

    <section class="armvw-panel" id="armvw-panel-transform" role="tabpanel" aria-labelledby="armvw-tab-transform" tabindex="0" hidden>
    <div class="armvw-card">
        <div class="armvw-card__header">
            <span class="dashicons dashicons-image-rotate" aria-hidden="true"></span>
            <div>
                <h3 class="armvw-card__title"><?php esc_html_e('Transform and animation', 'ar-model-viewer-for-woocommerce'); ?></h3>
                <p class="armvw-card__desc"><?php esc_html_e('Fixes for a model that arrives lying down, upside down or at the wrong size, and what to do when the file carries an animation.', 'ar-model-viewer-for-woocommerce'); ?></p>
            </div>
        </div>

        <?php foreach (array('orientation', 'scale', 'animation_name', 'variant_name') as $armvw_short) : ?>
            <?php
            $armvw_field = $armvw_fields[$armvw_short];
            $armvw_id = 'armvw-product-' . str_replace('_', '-', $armvw_short);
            $armvw_value = call_user_func(array($armvw_model_class, 'read'), $product, $armvw_short, $armvw_field['type']);
            ?>
            <div class="armvw-field">
                <div class="armvw-field__main">
                    <label class="armvw-field__label" for="<?php echo esc_attr($armvw_id); ?>">
                        <span class="dashicons <?php echo esc_attr($armvw_field_icons[$armvw_short]); ?>" aria-hidden="true"></span>
                        <?php echo esc_html($armvw_field['label']); ?>
                    </label>
                    <p class="armvw-field__desc"><?php echo wp_kses_post($armvw_field['help']); ?></p>
                </div>
                <div class="armvw-field__control">
                    <input
                        type="text"
                        class="armvw-input"
                        id="<?php echo esc_attr($armvw_id); ?>"
                        name="armvw_product[<?php echo esc_attr($armvw_short); ?>]"
                        value="<?php echo esc_attr(is_string($armvw_value) ? $armvw_value : ''); ?>"
                        placeholder="<?php echo esc_attr($armvw_field['placeholder']); ?>"
                    />
                </div>
            </div>
        <?php endforeach; ?>

        <?php $armvw_autoplay = call_user_func(array($armvw_model_class, 'read'), $product, 'autoplay', 'toggle'); ?>
        <div class="armvw-field">
            <div class="armvw-field__main">
                <label class="armvw-field__label" for="armvw-product-autoplay">
                    <span class="dashicons dashicons-controls-play" aria-hidden="true"></span>
                    <?php echo esc_html($armvw_fields['autoplay']['label']); ?>
                </label>
                <p class="armvw-field__desc"><?php echo wp_kses_post($armvw_fields['autoplay']['help']); ?></p>
            </div>
            <div class="armvw-field__control">
                <label class="armvw-checkbox" for="armvw-product-autoplay">
                    <input
                        type="checkbox"
                        id="armvw-product-autoplay"
                        name="armvw_product[autoplay]"
                        value="yes"
                        <?php checked(true === $armvw_autoplay); ?>
                    />
                    <span><?php esc_html_e('Start the animation as soon as the model is revealed.', 'ar-model-viewer-for-woocommerce'); ?></span>
                </label>
            </div>
        </div>
    </div>

    </section>

    <section class="armvw-panel" id="armvw-panel-ar" role="tabpanel" aria-labelledby="armvw-tab-ar" tabindex="0" hidden>
    <div class="armvw-card">
        <div class="armvw-card__header">
            <span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
            <div>
                <h3 class="armvw-card__title"><?php esc_html_e('Augmented reality on iPhone', 'ar-model-viewer-for-woocommerce'); ?></h3>
                <p class="armvw-card__desc"><?php esc_html_e('Android opens the same file, so this is the only platform that can need a second one.', 'ar-model-viewer-for-woocommerce'); ?></p>
            </div>
        </div>

        <?php
        $armvw_field = $armvw_fields['ios_src'];
        $armvw_value = call_user_func(array($armvw_model_class, 'read'), $product, 'ios_src', $armvw_field['type']);
        ?>
        <div class="armvw-field">
            <div class="armvw-field__main">
                <label class="armvw-field__label" for="armvw-product-ios-src">
                    <span class="dashicons <?php echo esc_attr($armvw_field_icons['ios_src']); ?>" aria-hidden="true"></span>
                    <?php echo esc_html($armvw_field['label']); ?>
                </label>
                <p class="armvw-field__desc"><?php echo wp_kses_post($armvw_field['help']); ?></p>
            </div>
            <div class="armvw-field__control">
                <input
                    type="text"
                    class="armvw-input"
                    id="armvw-product-ios-src"
                    name="armvw_product[ios_src]"
                    value="<?php echo esc_attr(is_string($armvw_value) ? $armvw_value : ''); ?>"
                    placeholder="<?php echo esc_attr($armvw_field['placeholder']); ?>"
                />
                <p class="armvw-field__actions">
                    <button
                        type="button"
                        class="button armvw-media"
                        data-armvw-target="armvw-product-ios-src"
                        data-armvw-kind="model"
                        data-armvw-title="<?php echo esc_attr__('Select the USDZ file', 'ar-model-viewer-for-woocommerce'); ?>"
                        data-armvw-button="<?php echo esc_attr__('Use this file', 'ar-model-viewer-for-woocommerce'); ?>"
                    >
                        <?php esc_html_e('Add or upload a file', 'ar-model-viewer-for-woocommerce'); ?>
                    </button>
                </p>
            </div>
        </div>
    </div>
    </section>
</div>
