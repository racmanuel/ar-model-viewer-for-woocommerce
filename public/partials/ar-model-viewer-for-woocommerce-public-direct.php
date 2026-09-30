<?php
/**
 * Direct AR product page.
 *
 * @package Ar_Model_Viewer_For_Woocommerce
 */

if (!defined('WPINC')) {
    die;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <?php if (!empty($data['model_poster'])) : ?>
        <meta property="og:image" content="<?php echo esc_attr($data['model_poster']); ?>">
    <?php endif; ?>
    <title><?php echo esc_html($title); ?> | <?php echo esc_html(get_bloginfo('name')); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('armvw-direct-page'); ?>>
<?php wp_body_open(); ?>
<main class="armvw-direct" aria-labelledby="armvw-direct-title">
    <section class="armvw-direct__content">
        <img
            class="armvw-direct__logo"
            src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . '../admin/images/armvw-logo-400.png'); ?>"
            alt="<?php esc_attr_e('AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce'); ?>"
        >
        <p class="armvw-direct__eyebrow"><?php esc_html_e('Explore in 3D and AR', 'ar-model-viewer-for-woocommerce'); ?></p>
        <h1 id="armvw-direct-title" class="armvw-direct__title"><?php echo esc_html($title); ?></h1>
        <div class="armvw-direct__viewer-wrap">
            <model-viewer class="armvw-direct__viewer"<?php echo $viewer_markup; ?>></model-viewer>
        </div>
        <button type="button" class="armvw-direct__ar-button" id="armvw-direct-ar-button">
            <?php esc_html_e('View in AR', 'ar-model-viewer-for-woocommerce'); ?>
        </button>
        <p class="armvw-direct__hint">
            <?php esc_html_e('Tap View in AR to see this product in your space.', 'ar-model-viewer-for-woocommerce'); ?>
        </p>
        <a class="armvw-direct__product-link" href="<?php echo esc_url(get_permalink($product_id)); ?>">
            <?php esc_html_e('Back to product details', 'ar-model-viewer-for-woocommerce'); ?>
        </a>
    </section>
</main>
<?php wp_footer(); ?>
</body>
</html>
