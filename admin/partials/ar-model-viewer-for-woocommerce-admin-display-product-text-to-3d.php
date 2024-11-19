<?php
/**
 * Check if WooCommerce is active and if the current page is the product edit screen.
 * If it is, retrieve and sanitize the product ID, otherwise set it to 0.
 */

// Ensure WooCommerce is active and we're on the product edit screen
if (isset($_GET['post']) && get_post_type($_GET['post']) === 'product') {
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
<div id="meshy-ai-text-to-3d-form" class="meshy-ai-text-to-3d-form">
    <div class="meshy-container">
        <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/meshy-wordmark-light.png'); ?>" alt="Meshi AI" class="meshy-logo" width="150"/>
        <p>Meshy is your 3D generative AI toolbox for effortlessly creating 3D assets from text or images, accelerating your 3D workflow. With Meshy, you can create high-quality textures and 3D models in minutes. Empowering creators of all skill levels, even those without any prior 3D experience, to generate fully textured 3D models from a simple text prompt within 2 minutes.</p>
        <a id="tutorial-text-to-3d" class="cmb2-upload-button button-secondary mesh-button">
            <?php _e('Tutorial to Generate a 3D Model with Text', 'ar-model-viewer-for-woocommerce');?>
        </a>
    </div>

    <div class="form-group">
        <label for="prompt"><?php _e('Prompt', 'ar-model-viewer-for-woocommerce');?></label>
        <textarea id="prompt" name="prompt" rows="3" placeholder="<?php _e('Describe the 3D model you want to generate...', 'ar-model-viewer-for-woocommerce');?>"></textarea>
        <small><?php _e('Describe what kind of object the 3D model is.', 'ar-model-viewer-for-woocommerce');?></small>
    </div>

    <div class="form-group">
        <label for="negative_prompt"><?php _e('Negative Prompt', 'ar-model-viewer-for-woocommerce');?></label>
        <textarea id="negative_prompt" name="negative_prompt" rows="3" placeholder="<?php _e('Specify what you don\'t want in the model...', 'ar-model-viewer-for-woocommerce');?>"></textarea>
        <small><?php _e('Describe how the model should NOT look.', 'ar-model-viewer-for-woocommerce');?></small>
    </div>

    <div class="form-group">
        <label for="art_style"><?php _e('Art Style', 'ar-model-viewer-for-woocommerce');?></label>
        <select id="art_style" name="art_style">
            <option value="realistic"><?php _e('Realistic', 'ar-model-viewer-for-woocommerce');?></option>
            <option value="sculpture"><?php _e('Sculpture', 'ar-model-viewer-for-woocommerce');?></option>
            <option value="pbr"><?php _e('PBR', 'ar-model-viewer-for-woocommerce');?></option>
        </select>
        <small>
            <?php _e('Describe your desired artistic style for the object. Default is "realistic".', 'ar-model-viewer-for-woocommerce');?><br>
            <strong><?php _e('Available values:', 'ar-model-viewer-for-woocommerce');?></strong>
            <ul>
                <li><?php _e('Realistic: Realistic style', 'ar-model-viewer-for-woocommerce');?></li>
                <li><?php _e('Sculpture: Sculpture style', 'ar-model-viewer-for-woocommerce');?></li>
                <li><?php _e('PBR: PBR style', 'ar-model-viewer-for-woocommerce');?></li>
            </ul>
        </small>
    </div>

    <div class="form-group">
        <label for="topology"><?php _e('Topology', 'ar-model-viewer-for-woocommerce');?></label>
        <select id="topology" name="topology">
            <option value="triangle"><?php _e('Triangle', 'ar-model-viewer-for-woocommerce');?></option>
            <option value="quad"><?php _e('Quad', 'ar-model-viewer-for-woocommerce');?></option>
        </select>
        <small>
            <?php _e('Specify the topology of the generated model. Default is "triangle".', 'ar-model-viewer-for-woocommerce');?><br>
            <strong><?php _e('Available values:', 'ar-model-viewer-for-woocommerce');?></strong>
            <ul>
                <li><?php _e('Quad: Generate a quad-dominant mesh.', 'ar-model-viewer-for-woocommerce');?></li>
                <li><?php _e('Triangle: Generate a decimated triangle mesh.', 'ar-model-viewer-for-woocommerce');?></li>
            </ul>
        </small>
    </div>

    <div class="form-group">
        <label for="target_polycount"><?php _e('Target Polycount', 'ar-model-viewer-for-woocommerce');?></label>
        <input type="number" id="target_polycount" name="target_polycount" min="10000" max="30000" value="30000">
        <small>
            <?php _e('Specify the target number of polygons in the generated model. The actual number may vary depending on the complexity of the geometry.', 'ar-model-viewer-for-woocommerce');?><br>
            <strong><?php _e('Valid range based on user level:', 'ar-model-viewer-for-woocommerce');?></strong>
            <ul>
                <li><?php _e('Premium users: 3,000 to 100,000 (inclusive)', 'ar-model-viewer-for-woocommerce');?></li>
                <li><?php _e('Free users: 10,000 to 30,000 (inclusive)', 'ar-model-viewer-for-woocommerce');?></li>
            </ul>
            <?php _e('Default is 30,000 if not specified.', 'ar-model-viewer-for-woocommerce');?><br>
        </small>
    </div>

    <a id="generate-text-to-3d" class="cmb2-upload-button button-secondary" data-product-id="<?php echo $escaped_product_id; ?>">
        <?php _e('Generate a preview of 3D Model', 'ar-model-viewer-for-woocommerce');?>
    </a>
</div>
