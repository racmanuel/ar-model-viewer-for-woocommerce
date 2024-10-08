<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin/partials
 */

?>

<!-- AR Model Viewer for WooCommerce - Page of Settings -->
<div class="ar-model-viewer-for-woocommerce-cards">
    <div class="ar-model-viewer-for-woocommerce-card">
        <!-- TItle -->
        <h3>
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-3d-94.png'); ?>"
                alt="Logo - AR Model Viewer for WooCommerce" class="icon-in-title">
            <?php _e('AR Model Viewer for ', 'ar-model-viewer-for-woocommerce');?>
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-woocommerce-96.png'); ?>"
                alt="Logo - AR Model Viewer for WooCommerce" class="icon-in-title">
        </h3>
        <!--
        <div id="ar-model-viewer-for-woocommerce-render-model">
        </div>-->
        <div class="cmb-row cmb-type-title" data-fieldtype="title">
            <div class="cmb-td">
                <h3 class="cmb2-metabox-title" id="preview-title"></h3><span
                    class="dashicons dashicons-admin-generic"></span> Preview</h3>
            </div>
        </div>
    </div>
    <div class="ar-model-viewer-for-woocommerce-card">
        <!-- Docs -->
        <h3>
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-crystal-ball-96.png'); ?>"
                alt="Logo - AR Model Viewer for WooCommerce" class="icon-in-title">
            <?php _e('Documentation', 'ar-model-viewer-for-woocommerce');?>
        </h3>
        <p style="text-align: justify;">
            <?php _e('This plugin uses the magical power of Google’s model-viewer library, a web component that makes rendering interactive 3D models — and even displaying them in Augmented Reality — as effortless as casting a spell. Designed to work across a wide variety of browsers and devices, model-viewer ensures enchanting default settings for both rendering quality and performance. As new standards and APIs emerge, model-viewer will be enchanted with updates to harness these advancements. If possible, fallback spells and polyfills will be conjured to offer a seamless development experience.
If you have any questions, feel free to visit the official plugin documentation or explore the Google model-viewer documentation.', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <a class="button button-primary" href="https://racmanuel.dev/" target="_blank">
            <?php _e('Go to Documentation', 'ar-model-viewer-for-woocommerce');?></a>
        <h3>
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-man-mage-96.png'); ?>"
                alt="Logo - AR Model Viewer for WooCommerce" class="icon-in-title">
            <?php _e('Do you have a technical problem?', 'ar-model-viewer-for-woocommerce');?>
        </h3>

        <p style="text-align: justify;">
            <?php _e('Before summoning assistance, please check our enchanted FAQ section for answers to common issues. If the magic there doesn’t solve your problem, explore the support forum to see if others have encountered a similar challenge.
Before contacting us directly, please review your server configuration and attach it to your message, for example, as a screenshot, so we can cast the right spell to assist you.', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <a class="button button-primary" href="https://wordpress.org/support/plugin/ar-model-viewer-for-woocommerce/"
            target="_blank">
            <?php _e('Go to Support', 'ar-model-viewer-for-woocommerce');?>
        </a>
        <h3>
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-sparkling-94.png'); ?>"
                alt="Logo - AR Model Viewer for WooCommerce" class="icon-in-title">
            <?php _e('Rate this plugin', 'ar-model-viewer-for-woocommerce');?>
        </h3>

        <p style="text-align: justify;">
            <?php _e('Could you sprinkle a bit of magic and rate our plugin? Let us know what you think—your feedback is essential for us to continue enchanting and improving this tool. Thank you for all the ratings, reviews that help keep the magic alive!', 'ar-model-viewer-for-woocommerce')?>
        </p>
        <a class="button button-primary"
            href="https://wordpress.org/support/plugin/ar-model-viewer-for-woocommerce/reviews/?rate=5#new-post"
            target="_blank">
            <?php _e('Add a Review', 'ar-model-viewer-for-woocommerce');?>
        </a>
        <h3>
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-magic-94.png'); ?>"
                alt="Logo - AR Model Viewer for WooCommerce" class="icon-in-title">
            <?php _e('¿Do you need a custom development on WordPress?', 'ar-model-viewer-for-woocommerce');?>
        </h3>
        <p>
            <?php _e('You can request a custom quote! I’m <a href="https://racmanuel.dev">racmanuel</a>, a Software Engineer and Web Programmer specialized in WordPress. Don’t hesitate to contact me for a personalized quote. I hope AR Model Viewer for WooCommerce is a magical solution for you. Cheers!', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <a class="button button-primary" href="https://racmanuel.dev" target="_blank">
            <?php _e('Request a Quote', 'ar-model-viewer-for-woocommerce');?>
        </a>
        <h3>
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-trust-94.png'); ?>"
                alt="Logo - AR Model Viewer for WooCommerce" class="icon-in-title">
            <?php _e('I love what I do!', 'ar-model-viewer-for-woocommerce');?>
        </h3>

        <p style="text-align: justify;">
            <?php _e('However, working on plugins and technical support requires many hours of work. If you want to appreciate it, you can give me a coffee.
            If every user of the plugin did it, I could dedicate myself entirely to working on this plugin. Thank you all!', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <a class="button button-primary" href="https://racmanuel.dev" target="_blank">
            <?php _e('Give me a coffee', 'ar-model-viewer-for-woocommerce');?>
        </a>
    </div>
</div>
<br>