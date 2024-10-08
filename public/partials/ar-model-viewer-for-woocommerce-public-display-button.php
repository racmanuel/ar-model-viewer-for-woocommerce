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
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<button id="ar_model_viewer_for_woocommerce_btn" data-product-id="<?php echo get_the_ID(); ?>">View in 3D</button>