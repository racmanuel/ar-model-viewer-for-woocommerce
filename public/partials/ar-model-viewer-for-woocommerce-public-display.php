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

<!-- AR Model Viewer for WooCommerce -->
<?php 
  // Check if product have a 3D Model 
  if (empty($android_file_url) and empty($ios_file_url)) {
			echo '<h1>No disponible</h1>';
  }else{
  ?>
  <!-- AR Model Viewer for WooCommerce -->
  <model-viewer alt="<?php echo esc_attr($alt_description) ?>" src="<?php echo esc_url($android_file_url); ?>"
    ios-src="<?php echo esc_url($ios_file_url); ?>" poster="<?php echo esc_url($poster_file_url); ?>" ar
    ar-modes="webxr scene-viewer quick-look" camera-controls seamless-poster shadow-intensity="1" camera-controls
    enable-pan>
  </model-viewer>
  <?php
  }
  ?>