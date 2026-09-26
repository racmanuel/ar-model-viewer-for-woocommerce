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

<?php
/*
 * Over the image the label is hidden on narrow screens, so the button carries an accessible name of
 * its own: a button that is only an icon says nothing to a screen reader. Anywhere else the visible
 * label is enough and no duplicate name is added.
 */
$armvw_over_image = isset($armvw_position) && '11' === $armvw_position;
$armvw_classes = $armvw_over_image ? 'armvw-button armvw-button--over-image' : 'armvw-button';
$armvw_aria = $armvw_over_image ? ' aria-label="' . esc_attr($armvw_button_text) . '"' : '';
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<button type="button" class="<?php echo esc_attr($armvw_classes); ?>" id="ar_model_viewer_for_woocommerce_btn" data-product-id="<?php echo esc_attr((string) get_the_ID()); ?>"<?php echo $armvw_aria; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the expression above. ?>>
    <?php
    /*
     * An inline SVG and not a dashicon: the icon font is not loaded on the front end for anonymous
     * visitors, so a dashicon here would be an empty box for every shopper who is not logged in.
     * The path inherits the colour of the button through `currentColor`.
     */
    ?>
    <svg class="armvw-button__icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
        <path fill="currentColor" d="M12 2.4 21 7v10l-9 4.6L3 17V7l9-4.6Zm0 2.3L5.5 8 12 11.2 18.5 8 12 4.7ZM5 9.8v6l6 3v-6l-6-3Zm8 9 6-3v-6l-6 3v6Z" />
    </svg>
    <span><?php echo esc_html($armvw_button_text); ?></span>
</button>