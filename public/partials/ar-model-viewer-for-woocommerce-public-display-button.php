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
<button id="ar_model_viewer_for_woocommerce_btn">View in 3D</button>

<div id="dialog" title="">
  <!-- AR Model Viewer for WooCommerce - Styles -->
  <style type="text/css">
    model-viewer#reveal {
      --poster-color: <?php echo $this->ar_model_viewer_for_woocommerce_poster_color($poster_color_type); ?>;
    }
  </style>
  <!-- AR Model Viewer for WooCommerce - HTML -->
  <model-viewer 
    id="reveal"
    alt="<?php echo esc_attr($alt_description) ?>" 
    src="<?php echo esc_url($android_file_url); ?>"
    ios-src="<?php echo esc_url($ios_file_url); ?>" 
    poster="<?php echo esc_url($poster_file_url); ?>"
    <?php echo $this->ar_model_viewer_for_woocommerce_loading_type($loading_type); ?>
    <?php echo $this->ar_model_viewer_for_woocommerce_reveal_type($reveal_type); ?> 
    <?php echo $this->ar_model_viewer_for_woocommerce_ar($ar_active); ?>
    ar-modes="webxr scene-viewer" 
    camera-controls 
    seamless-poster 
    shadow-intensity="1" 
    camera-controls
    enable-pan>
  </model-viewer>
  <button slot="ar-button" style="background-color: white; border-radius: 4px; border: none; position: absolute; top: 16px; right: 16px; ">
      👋 Activate AR
  </button>
</div>