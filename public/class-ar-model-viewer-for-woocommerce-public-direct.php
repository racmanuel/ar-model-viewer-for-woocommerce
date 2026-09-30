<?php
/**
 * Direct AR product links for the Pro plan.
 *
 * @package Ar_Model_Viewer_For_Woocommerce
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Renders a lightweight product page for stable AR links.
 */
class Ar_Model_Viewer_For_Woocommerce_Public_Direct
{
    const QUERY_VAR = 'armvw_direct_product';
    const PATH = 'ar-model-viewer';

    /**
     * @var Ar_Model_Viewer_For_Woocommerce_Public
     */
    private $public;

    /**
     * @param Ar_Model_Viewer_For_Woocommerce_Public $public Public viewer service.
     */
    public function __construct($public)
    {
        $this->public = $public;
    }

    /**
     * Register the stable product URL.
     *
     * @return void
     */
    public static function register_rewrite_rule()
    {
        add_rewrite_rule(
            '^' . self::PATH . '/([0-9]+)/?$',
            'index.php?' . self::QUERY_VAR . '=$matches[1]',
            'top'
        );
    }

    /**
     * Build the stable public URL for a product or variation.
     *
     * @param int  $product_id   Product identifier.
     * @param int  $variation_id Variation identifier.
     * @param bool $from_qr      Mark the URL as opened from a QR code.
     * @return string
     */
    public static function url($product_id, $variation_id = 0, $from_qr = false)
    {
        $args = array();

        if (absint($variation_id)) {
            $args['variation_id'] = absint($variation_id);
        }

        if ($from_qr) {
            $args['armvw_qr'] = 1;
        }

        return add_query_arg($args, home_url('/' . self::PATH . '/' . absint($product_id) . '/'));
    }

    /**
     * Add the direct-link query variable.
     *
     * @param array<int, string> $vars Existing query variables.
     * @return array<int, string>
     */
    public function register_query_var($vars)
    {
        $vars[] = self::QUERY_VAR;

        return $vars;
    }

    /**
     * Render the direct page when the stable URL was requested.
     *
     * @return void
     */
    public function render()
    {
        $product_id = absint(get_query_var(self::QUERY_VAR));

        if (!$product_id) {
            return;
        }

        if (!$this->is_pro_available()) {
            $this->send_not_found();
        }

        $variation_id = isset($_GET['variation_id']) ? absint($_GET['variation_id']) : 0;
        $data = $this->public->get_model_and_settings_data($product_id, $variation_id);

        if (is_wp_error($data)) {
            $this->send_not_found();
        }

        $product = wc_get_product($product_id);

        if (!$product || 'publish' !== get_post_status($product_id)) {
            $this->send_not_found();
        }

        $attributes = array_merge(
            array(
                'src' => $data['model_3d_file'],
                'alt' => $data['model_alt'],
                'poster' => $data['model_poster'],
                'loading' => $data['loading'],
                'reveal' => $data['reveal'],
                'ar' => $data['ar'],
                'ar-modes' => implode(' ', $data['ar_modes']),
                'ar-scale' => $data['scale'],
                'ar-placement' => $data['placement'],
                'xr-environment' => $data['xr_environment'],
                'with-credentials' => $data['with_credentials'],
                'ios-src' => $data['model_ios_src'],
            ),
            $data['attributes'],
            $data['product_attributes']
        );
        $attributes = array_filter($attributes, array(__CLASS__, 'is_filled'));
        $viewer_markup = Ar_Model_Viewer_For_Woocommerce_Settings::render_attributes($attributes);
        $title = $product->get_name();
        $direct_url = self::url($product_id, $variation_id, isset($_GET['armvw_qr']));

        nocache_headers();
        status_header(200);
        include plugin_dir_path(__FILE__) . 'partials/ar-model-viewer-for-woocommerce-public-direct.php';
        exit;
    }

    /**
     * Enqueue the direct page interaction.
     *
     * @return void
     */
    public function enqueue_assets()
    {
        if (!get_query_var(self::QUERY_VAR)) {
            return;
        }

        $product_id = absint(get_query_var(self::QUERY_VAR));
        $variation_id = isset($_GET['variation_id']) ? absint($_GET['variation_id']) : 0;
        $event = isset($_GET['armvw_qr']) ? 'qr_open' : 'direct_link_open';

        wp_add_inline_script(
            'ar-model-viewer-for-woocommerce',
            '(function(){document.addEventListener("DOMContentLoaded",function(){var b=document.getElementById("armvw-direct-ar-button");var v=document.querySelector(".armvw-direct__viewer");if(b&&v){b.addEventListener("click",function(){var analytics=window.armvwFront&&window.armvwFront.analytics;if(analytics&&analytics.enabled&&analytics.endpoint){var body=new URLSearchParams();body.append("event","direct_ar_attempt");body.append("product_id",' . $product_id . ');body.append("variation_id",' . $variation_id . ');fetch(analytics.endpoint,{method:"POST",body:body,keepalive:true});}if(v.activateAR){v.activateAR();}});}var analytics=window.armvwFront&&window.armvwFront.analytics;if(analytics&&analytics.enabled&&analytics.endpoint&&(!window.localStorage||"1"!==window.localStorage.getItem(analytics.storageKey||"armvwAnalyticsOptOut"))){var body=new URLSearchParams();body.append("event","' . esc_js($event) . '");body.append("product_id",' . $product_id . ');body.append("variation_id",' . $variation_id . ');fetch(analytics.endpoint,{method:"POST",body:body,keepalive:true});}}); }());'
        );
    }

    /**
     * Check whether the Pro feature is active.
     *
     * @return bool
     */
    private function is_pro_available()
    {
        return function_exists('ar_model_viewer_for_woocommerce_fs')
            && ar_model_viewer_for_woocommerce_fs()->is__premium_only()
            && ar_model_viewer_for_woocommerce_fs()->is_plan('pro', true)
            && ar_model_viewer_for_woocommerce_fs()->can_use_premium_code();
    }

    /**
     * Check whether an attribute should be printed.
     *
     * @param mixed $value Attribute value.
     * @return bool
     */
    private static function is_filled($value)
    {
        return true === $value || (is_scalar($value) && '' !== (string) $value);
    }

    /**
     * Return a private feature as a normal 404.
     *
     * @return void
     */
    private function send_not_found()
    {
        status_header(404);
        nocache_headers();
        wp_die(
            esc_html__('The requested AR product could not be found.', 'ar-model-viewer-for-woocommerce'),
            esc_html__('AR product not found', 'ar-model-viewer-for-woocommerce'),
            array('response' => 404)
        );
    }
}
