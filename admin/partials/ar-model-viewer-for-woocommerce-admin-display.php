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
            <?php _e('AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce'); ?>
        </h3>
        <!-- Docs -->
        <h3>
            <span class="dashicons dashicons-admin-page"></span>
            <?php _e('Documentation', 'ar-model-viewer-for-woocommerce'); ?>
        </h3>
        <hr>
        <p style="text-align: justify;">
            <?php _e('This plugin use the Google Library <samp>model-viewer</samp> is a web component
            that makes rendering
            interactive 3D models - optionally in AR - easy to do, on as many browsers and devices as possible.
            <samp>model-viewer</samp> strives to give you great defaults for rendering quality and performance.
            As new standards and APIs become available model-viewer will be improved to take advantage of them. If
            possible, fallbacks and polyfills will be supported to provide a seamless development experience.','ar-model-viewer-for-woocommerce'); ?>
        </p>
        <a class="button button-primary" href="https://modelviewer.dev/" target="_blank"><span
                class="dashicons dashicons-media-document"></span> Go to Documentation</a>
        <!-- contribute to the project -->
        <h3>
            <span class="dashicons dashicons-groups"></span>
            Would you like to contribute to the project?
        </h3>
        <hr>
        <p style="text-align: justify;">
            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quisquam optio tempore
            iusto consequuntur dicta deserunt repellendus accusamus unde, voluptatem sit impedit ut aliquid suscipit.
            Neque quod illum est beatae omnis.
        </p>
        <a class="button button-primary" href="" target="_blank">Go to GitHub Project</a>
        <h3><span class="dashicons dashicons-admin-generic"></span> ¿Do you have a technical problem?</h3>
        <hr>
        <p style="text-align: justify;">
            ¿Tienes un problema técnico? Por favor contáctanos. Estaremos encantados de ayudarte. ¿O tal vez tienes una
            idea para una nueva funcionalidad? Por favor, háznosla completando el formulario de soporte. ¡Intentaremos
            añadirla!
            Please check our FAQ before adding a thread with technical problem. If you do not find help there, check
            support forum for similar problems. Before you contact us check the configuration of your server and attach
            it in your message, e.g. as a screenshot.
        </p>
        <a class="button button-primary" href="" target="_blank"><span class="dashicons dashicons-star-filled"></span>
            Go to Contribute</a>
    </div>
    <div class="ar-model-viewer-for-woocommerce-card">
        <h3>Do you like <span class="dashicons dashicons-thumbs-up"></span> our plugin?</h3>
        <hr>
        <p style="text-align: justify;">
            <?php _e('Could you rate it? Please let us know what you think about our plugin. It is important for us to develop this tool. Thank you for all the ratings, reviews and donations.','ar-model-viewer-for-woocommerce') ?>
        </p>
        <a class="button button-primary" href="" target="_blank"><span class="dashicons dashicons-star-filled"></span>
            Add a Review</a>
        <h3>¿Do you need a custom development on WordPress?</h3>
        <hr>
        <p>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Ea nam dolorem, voluptatem nobis enim distinctio
            eos aspernatur ut mollitia laborum cum! Odio molestias beatae blanditiis asperiores hic dolore ipsa placeat?
        </p>
        <a class="button button-primary" href="" target="_blank"><span class="dashicons dashicons-coffee"></span> Give
            me a coffee</a>
        <h3>I love <span class="dashicons dashicons-heart"></span> what I do!</h3>
        <hr>
        <p style="text-align: justify;">
            <?php _e('However, working on plugins and technical support requires many hours of work. If you want to appreciate it, you can give me a coffee.
            If every user of the plugin did it, I could dedicate myself entirely to working on this plugin. Thank you all!','ar-model-viewer-for-woocommerce'); ?>
        </p>
        <a class="button button-primary" href="" target="_blank"><span class="dashicons dashicons-coffee"></span> Give
            me a coffee</a>
    </div>
</div>
<br>