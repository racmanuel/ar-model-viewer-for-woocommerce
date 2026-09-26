<?php
/**
 * Provide the public-facing button that opens the viewer.
 *
 * The label is configurable in the settings, with the translated default as a fallback.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public/partials
 */

$armvw_button_text = (string) Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_button_text');

if ('' === trim($armvw_button_text)) {
    $armvw_button_text = __('View in 3D', 'ar-model-viewer-for-woocommerce');
}
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<button type="button" id="ar_model_viewer_for_woocommerce_btn" data-product-id="<?php echo esc_attr((string) get_the_ID()); ?>">
    <?php echo esc_html($armvw_button_text); ?>
</button>