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
}