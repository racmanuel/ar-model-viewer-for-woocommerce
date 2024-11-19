<?php
/**
 * Check if WooCommerce is active and if the current page is the product edit screen.
 * If it is, retrieve and sanitize the product ID, otherwise set it to 0.
 */

// Ensure WooCommerce is active and we're on the product edit screen
if (function_exists('wc_get_product') && isset($_GET['post']) && get_post_type($_GET['post']) === 'product') {
    /**
     * Get the product ID from the current post.
     *
     * @var int $product_id The sanitized product ID.
     */
    $product_id = intval($_GET['post']); // Sanitize the product ID as an integer
} else {
    /**
     * Fallback value for product ID.
     *
     * @var int $product_id If WooCommerce is not active or the post type is not 'product', the product ID is set to 0.
     */
    $product_id = 0; // Fallback in case WooCommerce isn't active or we're not on a product page
}

/**
 * Escape the product ID for safe output in HTML.
 *
 * @var string $escaped_product_id The product ID escaped for use in an HTML attribute.
 */
$escaped_product_id = esc_attr($product_id);

?>

<div class="cmb-row">
    <div class="cmb-th">
        <label>
            <!-- Display a logo image for the AR Model Viewer with proper escaping for the image URL and alt attribute. -->
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-workspace-94.png'); ?>"
                alt="<?php esc_attr_e('Logo - AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce');?>"
                class="icon-in-field">
            <?php _e('Use in Template File', 'ar-model-viewer-for-woocommerce');?>
        </label>
    </div>
    <div class="cmb-td">
        <p>
            <!-- Translation function for the instructional text. -->
            <?php _e('Copy and paste the PHP code into your template file:', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <div class="shortcode-box" id="ar-model-viewer-for-woocommerce-template-file">
            <code id="php-include-text">
                <!-- Generate PHP code for embedding the AR Model Viewer shortcode in a template file. -->
                &lt;?php echo do_shortcode('[ar_model_viewer_for_woocommerce_shortcode id="<?php echo esc_attr($escaped_product_id); ?>"]'); ?&gt;
            </code>
            <button class="copy-button" id="button-copy-shortcode-template-file"
                aria-label="<?php esc_attr_e('Copy the PHP code', 'ar-model-viewer-for-woocommerce');?>"
                title="<?php esc_attr_e('Copy the PHP code', 'ar-model-viewer-for-woocommerce');?>">
                <!-- Translation function for the copy button text. -->
                <?php _e('Copy', 'ar-model-viewer-for-woocommerce');?>
            </button>
        </div>
    </div>
</div>

<div class="cmb-row">
    <div class="cmb-th">
        <label>
            <!-- Display the same logo image for the shortcode section with proper escaping. -->
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/icons8-workspace-94.png'); ?>"
                alt="<?php esc_attr_e('Logo - AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce');?>"
                class="icon-in-field">
            <?php _e('Use in Shortcode', 'ar-model-viewer-for-woocommerce');?>
        </label>
    </div>
    <div class="cmb-td">
        <p>
            <!-- Translation function for the instructional text. -->
            <?php _e('Copy and paste this shortcode into your posts, pages, and widget:', 'ar-model-viewer-for-woocommerce');?>
        </p>
        <div class="shortcode-box" id="ar-model-viewer-for-woocommerce-shortcode">
            <code id="shortcode-text">
                <!-- Generate the AR Model Viewer shortcode with the escaped product ID. -->
                [ar_model_viewer_for_woocommerce_shortcode id="<?php echo esc_html($escaped_product_id); ?>"]
            </code>
            <button class="copy-button" id="button-copy-shortcode"
                aria-label="<?php esc_attr_e('Copy the shortcode', 'ar-model-viewer-for-woocommerce');?>"
                title="<?php esc_attr_e('Copy the shortcode', 'ar-model-viewer-for-woocommerce');?>">
                <!-- Translation function for the copy button text. -->
                <?php _e('Copy', 'ar-model-viewer-for-woocommerce');?>
            </button>
        </div>
    </div>
</div>
