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
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-3d-object-96(-xxxhdpi).png'); ?>"
                alt="Logo - AR Model Viewer for WooCommerce" class="icon-in-title">
            <?php _e('AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce');?>
        </h3>
        <!-- Docs -->
        <h3>
            <span class="dashicons dashicons-admin-page"></span>
            <?php _e('Documentation', 'ar-model-viewer-for-woocommerce');?>
        </h3>
        <hr>
        <p style="text-align: justify;">
            <?php _e('This plugin use the Google Library <samp>model-viewer</samp> is a web component
            that makes rendering
            interactive 3D models - optionally in AR - easy to do, on as many browsers and devices as possible.
            <samp>model-viewer</samp> strives to give you great defaults for rendering quality and performance.
            As new standards and APIs become available model-viewer will be improved to take advantage of them. If
            possible, fallbacks and polyfills will be supported to provide a seamless development experience.', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <a class="button button-primary" href="https://racmanuel.dev/" target="_blank"><span
                class="dashicons dashicons-media-document"></span>
            <?php _e('Go to Documentation','ar-model-viewer-for-woocommerce'); ?></a>
        <!-- contribute to the project -->
        <h3>
            <span class="dashicons dashicons-groups"></span>
            <?php _e('Would you like to contribute to the project?', 'ar-model-viewer-for-woocommerce');?>
        </h3>
        <hr>
        <p style="text-align: justify;">
            <?php _e('Are you a developer? You would like to contribute to the project, visit the plugin page on GitHub, if you have any improvements make a (PR) Pull Request and add your improvement.', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <a class="button button-primary" href="https://github.com/racmanuel/ar-model-viewer-for-woocommerce"
            target="_blank"> <span class="dashicons dashicons-networking"></span>
            <?php _e('Go to Repository','ar-model-viewer-for-woocommerce'); ?>
        </a>
        <h3>
            <span class="dashicons dashicons-admin-generic"></span>
            <?php _e('¿Do you have a technical problem?','ar-model-viewer-for-woocommerce'); ?>
        </h3>
        <hr>
        <p style="text-align: justify;">
            <?php _e('Please check our FAQ before adding a thread with technical problem. If you do not find help there, check
            support forum for similar problems. Before you contact us check the configuration of your server and attach
            it in your message, e.g. as a screenshot.', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <a class="button button-primary" href="https://wordpress.org/support/plugin/ar-model-viewer-for-woocommerce/"
            target="_blank"><span class="dashicons dashicons-admin-generic"></span>
            <?php _e('Go to Support','ar-model-viewer-for-woocommerce'); ?>
        </a>
    </div>
    <div class="ar-model-viewer-for-woocommerce-card">
        <h3>
            <span class="dashicons dashicons-thumbs-up"></span>
            <?php _e('Go to Support','ar-model-viewer-for-woocommerce'); ?>
        </h3>
        <hr>
        <p style="text-align: justify;">
            <?php _e('Could you rate it? Please let us know what you think about our plugin. It is important for us to develop this tool. Thank you for all the ratings, reviews and donations.', 'ar-model-viewer-for-woocommerce')?>
        </p>
        <a class="button button-primary"
            href="https://wordpress.org/support/plugin/ar-model-viewer-for-woocommerce/reviews/?rate=5#new-post"
            target="_blank">
            <span class="dashicons dashicons-star-filled"></span>
            <?php _e('Add a Review','ar-model-viewer-for-woocommerce'); ?>
        </a>
        <h3>
            <?php _e('¿Do you need a custom development on WordPress?','ar-model-viewer-for-woocommerce'); ?>
        </h3>
        <hr>
        <p>
            <?php _e('You can do a request for a quote, I am <a href="https://racmanuel.dev">racmanuel</a> a Software Engineer and Web Programmer specialized in WordPress, do not hesitate to contact me to make your quote. I hope that AR Model Viewer for WooCommerce is useful to you. Cheers.', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <a class="button button-primary" href="https://racmanuel.dev" target="_blank">
            <span class="dashicons dashicons-analytics"></span>
            <?php _e('Request a Quote','ar-model-viewer-for-woocommerce'); ?>
        </a>
        <h3>
            <span class="dashicons dashicons-heart"></span>
            <?php _e('I love what I do!','ar-model-viewer-for-woocommerce'); ?>
        </h3>
        <hr>
        <p style="text-align: justify;">
            <?php _e('However, working on plugins and technical support requires many hours of work. If you want to appreciate it, you can give me a coffee.
            If every user of the plugin did it, I could dedicate myself entirely to working on this plugin. Thank you all!', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <a class="button button-primary" href="https://racmanuel.dev" target="_blank"><span
                class="dashicons dashicons-coffee"></span>
            <?php _e('Give me a coffee','ar-model-viewer-for-woocommerce'); ?>
        </a>
    </div>
</div>
<br>