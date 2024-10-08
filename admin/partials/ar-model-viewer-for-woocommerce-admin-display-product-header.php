<?php
/**
 * Display the AR Model Viewer plugin information and provide buttons for tutorial and settings.
 */

?>

<div class="cmb-row">
    <div class="cmb-th">
        <!-- Display a logo image for the AR Model Viewer with proper escaping for the image URL and alt attribute. -->
        <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/armvw-logo-transparent.png'); ?>"
                alt="<?php esc_attr_e('Logo - AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce');?>"
                class="ar-model-viewer-logo">
    </div>

    <div class="cmb-td">
        <p>
            <!-- Plugin description for the AR Model Viewer plugin. This paragraph explains the plugin's features. -->
            <?php _e('The AR Model Viewer for WooCommerce is a versatile plugin designed to enhance your online store by displaying 3D models of your products. Customers can view these models in augmented reality (AR), providing an interactive and immersive shopping experience. The plugin supports 3D model files in .glb and .gltf formats and is incredibly easy to use.', 'ar-model-viewer-for-woocommerce'); ?>
        </p>
        <p>
            <?php _e('Whether your website is an eCommerce platform or a WooCommerce-based store, the AR Model Viewer plugin seamlessly integrates to allow customers to explore your products in 3D and AR.', 'ar-model-viewer-for-woocommerce'); ?>
        </p>

        <!-- Button to start the product tutorial. -->
        <a class="button button-primary ar-model-viewer-for-woocommerce" id="ar_model_viewer_for_woocommerce_product_tutorial">
            <?php _e('Start Tutorial', 'ar-model-viewer-for-woocommerce'); ?>
        </a>

        <!-- Button to go to the settings page for the plugin -->
        <a class="button button-primary ar-model-viewer-for-woocommerce" id="ar_model_viewer_for_woocommerce_product_settings" href="<?php echo esc_url(admin_url('options-general.php?page=ar_model_viewer_for_woocommerce_settings')); ?>">
            <?php _e('Go to Settings', 'ar-model-viewer-for-woocommerce'); ?>
        </a>

        <!-- Button to get a preview of 3D Model in admin-->
        <a class="button button-primary ar-model-viewer-for-woocommerce" id="ar_model_viewer_for_woocommerce_product_preview" data-product-id="<?php echo esc_attr(get_the_ID()); ?>">
            <?php _e('Preview of 3D Model', 'ar-model-viewer-for-woocommerce'); ?>
        </a>
    </div>
</div>
