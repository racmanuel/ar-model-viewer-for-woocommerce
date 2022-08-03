<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/public/partials
 */

// Global product variable
global $product;

//Get the file url for android
$get_android_file = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_android', true);
//Get the fiel url for IOS
$get_ios_file = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_ios', true);
//Get the alt for web accessibility
$get_alt = get_post_meta($product->get_id(), 'ar_model_viewer_for_woocommerce_file_alt', true);
/**
 * NOTE FOR DEVELOPMENT: MAYBE ADD A OPTION IN WP-ADMIN TO CHECK IF GET THE CURRENT POST IMAGE OR ADD ANOTHER
 */
//Get the Poster
$get_poster = wp_get_attachment_url($product->get_image_id());

// Check if the customs fields has a value.
if (!empty($get_android_file)) {
    $android_file_url = $get_android_file;
}
if (!empty($get_ios_file)) {
    $ios_file_url = $get_ios_file;
}
if (!empty($get_alt)) {
    $alt_description = sanitize_text_field($get_alt);
}
if (!empty($get_poster)) {
    $poster_file_url = $get_poster;
}
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<!-- AR Model Viewer for WooCommerce -->

<model-viewer
    alt="<?php echo esc_attr($alt_description) ?>"
    src="<?php echo esc_url($android_file_url); ?>"
    ios-src="<?php echo esc_url($ios_file_url); ?>"
    poster="<?php echo esc_url($poster_file_url); ?>"
    ar
    ar-modes="webxr scene-viewer quick-look"
    camera-controls
    seamless-poster shadow-intensity="1" camera-controls enable-pan></model-viewer>