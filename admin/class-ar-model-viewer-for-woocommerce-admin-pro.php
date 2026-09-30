<?php
/**
 * Premium admin functionality.
 *
 * @package Ar_Model_Viewer_For_Woocommerce
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Registers features that require the Pro plan.
 */
class Ar_Model_Viewer_For_Woocommerce_Admin_Pro
{
    /**
     * @var string
     */
    private $plugin_name;

    /**
     * @var string
     */
    private $plugin_prefix;

    /**
     * @var string
     */
    private $version;

    /**
     * @param string $plugin_name   Plugin name.
     * @param string $plugin_prefix Plugin prefix.
     * @param string $version       Plugin version.
     */
    public function __construct($plugin_name, $plugin_prefix, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_prefix = $plugin_prefix;
        $this->version = $version;
    }

    /**
     * Register the viewer widget in Elementor.
     *
     * @param object $widgets_manager Elementor widgets manager.
     * @return void
     */
    public function register_ar_model_viewer_widget($widgets_manager)
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/widgets/ar-model-viewer-for-woocommerce-widget.php';
        $widgets_manager->register(new \Ar_Model_Viewer_For_Woocommerce_Widget());
    }

    /**
     * Render the direct AR link and QR controls on a product editor.
     *
     * @return void
     */
    public function render_product_qr_fields()
    {
        global $post;

        if (!$post || !$post->ID) {
            return;
        }

        echo $this->qr_markup($post->ID, 0); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is escaped inside qr_markup().
    }

    /**
     * Render the direct AR link and QR controls on a variation editor.
     *
     * @param int                  $loop      Variation loop index.
     * @param array                $data      Variation data.
     * @param WC_Product_Variation $variation Variation object.
     * @return void
     */
    public function render_variation_qr_fields($loop, $data, $variation)
    {
        if (!$variation instanceof WC_Product_Variation) {
            return;
        }

        echo $this->qr_markup($variation->get_parent_id(), $variation->get_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is escaped inside qr_markup().
    }

    /**
     * Download one QR code after checking the editor capability and nonce.
     *
     * @return void
     */
    public function download_qr()
    {
        $product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
        $variation_id = isset($_GET['variation_id']) ? absint($_GET['variation_id']) : 0;
        $format = isset($_GET['format']) ? sanitize_key($_GET['format']) : 'svg';
        $nonce_id = $variation_id ? $variation_id : $product_id;

        if (!$nonce_id || !current_user_can('edit_post', $nonce_id)) {
            wp_die(esc_html__('You are not allowed to download this QR code.', 'ar-model-viewer-for-woocommerce'), '', array('response' => 403));
        }

        check_admin_referer('armvw_download_qr_' . $nonce_id);

        if (!in_array($format, array('svg', 'png'), true)) {
            wp_die(esc_html__('The QR format is invalid.', 'ar-model-viewer-for-woocommerce'), '', array('response' => 400));
        }

        if ($variation_id) {
            $variation = wc_get_product($variation_id);

            if (!$variation instanceof WC_Product_Variation || (int) $variation->get_parent_id() !== $product_id) {
                wp_die(esc_html__('The variation is invalid.', 'ar-model-viewer-for-woocommerce'), '', array('response' => 400));
            }
        } elseif (!wc_get_product($product_id)) {
            wp_die(esc_html__('The product is invalid.', 'ar-model-viewer-for-woocommerce'), '', array('response' => 400));
        }

        $url = Ar_Model_Viewer_For_Woocommerce_Public_Direct::url($product_id, $variation_id, true);
        $options = new \chillerlan\QRCode\QROptions(array(
            'outputType' => 'svg' === $format ? \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG : \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => \chillerlan\QRCode\QRCode::ECC_M,
            'scale' => 6,
            'imageBase64' => false,
        ));
        $content = (new \chillerlan\QRCode\QRCode($options))->render($url);
        $filename = 'ar-model-viewer-' . $product_id . ($variation_id ? '-variation-' . $variation_id : '') . '.' . $format;

        nocache_headers();
        header('Content-Type: ' . ('svg' === $format ? 'image/svg+xml' : 'image/png'));
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- QR binary output is generated locally by the QR library.
        exit;
    }

    /**
     * Build the native product-editor QR controls.
     *
     * @param int $product_id   Product identifier.
     * @param int $variation_id Variation identifier.
     * @return string
     */
    private function qr_markup($product_id, $variation_id)
    {
        $nonce_id = $variation_id ? $variation_id : $product_id;
        $url = Ar_Model_Viewer_For_Woocommerce_Public_Direct::url($product_id, $variation_id, false);
        $qr_url = Ar_Model_Viewer_For_Woocommerce_Public_Direct::url($product_id, $variation_id, true);
        $download_args = array(
            'action' => 'armvw_download_qr',
            'product_id' => $product_id,
            'variation_id' => $variation_id,
        );
        $svg_url = wp_nonce_url(
            add_query_arg(array_merge($download_args, array('format' => 'svg')), admin_url('admin-post.php')),
            'armvw_download_qr_' . $nonce_id
        );
        $png_url = wp_nonce_url(
            add_query_arg(array_merge($download_args, array('format' => 'png')), admin_url('admin-post.php')),
            'armvw_download_qr_' . $nonce_id
        );
        $options = new \chillerlan\QRCode\QROptions(array(
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => \chillerlan\QRCode\QRCode::ECC_M,
            'scale' => 4,
            'imageBase64' => false,
        ));
        $preview = (new \chillerlan\QRCode\QRCode($options))->render($qr_url);

        ob_start();
        ?>
        <div class="options_group armvw-pro-qr">
            <p class="form-field armvw-pro-qr__title">
                <label><?php esc_html_e('Direct AR link', 'ar-model-viewer-for-woocommerce'); ?></label>
                <span class="description"><?php esc_html_e('Scan this QR code to open the product in augmented reality.', 'ar-model-viewer-for-woocommerce'); ?></span>
            </p>
            <div class="armvw-pro-qr__body">
                <div class="armvw-pro-qr__preview" aria-hidden="true"><?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is generated locally by the QR library. ?></div>
                <div class="armvw-pro-qr__controls">
                    <p class="form-field armvw-pro-qr__url-field">
                        <label for="armvw-pro-qr-url-<?php echo esc_attr($nonce_id); ?>"><?php esc_html_e('Stable URL', 'ar-model-viewer-for-woocommerce'); ?></label>
                        <input id="armvw-pro-qr-url-<?php echo esc_attr($nonce_id); ?>" type="text" readonly value="<?php echo esc_attr($url); ?>" class="input-text" />
                    </p>
                    <p class="armvw-pro-qr__actions">
                        <a class="button" href="<?php echo esc_url($svg_url); ?>"><?php esc_html_e('Download SVG QR', 'ar-model-viewer-for-woocommerce'); ?></a>
                        <a class="button" href="<?php echo esc_url($png_url); ?>"><?php esc_html_e('Download PNG QR', 'ar-model-viewer-for-woocommerce'); ?></a>
                    </p>
                </div>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}