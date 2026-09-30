<?php
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.Security.NonceVerification.Recommended -- Variables are template-local and reset requests are nonce-validated by the settings controller.
/**
 * Provide the admin view of the plugin settings screen.
 *
 * Available variables:
 *
 * @var Ar_Model_Viewer_For_Woocommerce_Admin_Settings $settings The settings screen controller.
 * @var array<string, array<string, mixed>>            $tabs     Tab definitions keyed by tab slug.
 * @var array<string, array<string, mixed>>            $fields   Field metadata keyed by option key.
 *
 * @link       https://racmanuel.dev
 * @since      3.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$armvw_was_reset = isset($_GET['armvw-reset'])
    && is_string($_GET['armvw-reset'])
    && 'done' === sanitize_key(wp_unslash($_GET['armvw-reset']));
$armvw_ar_state = Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_ar');
$armvw_button_state = Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_btn');
?>
<div class="armvw-shell">
    <div class="armvw-main">
        <div class="armvw-top">
            <header class="armvw-hero">
                <div class="armvw-hero__brand">
                    <img
                        class="armvw-hero__logo"
                        src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'images/armvw-logo-400.png'); ?>"
                        alt="<?php echo esc_attr__('AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce'); ?>"
                    />
                    <div>
                        <h1 class="armvw-hero__title">
                            <?php echo esc_html__('AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce'); ?>
                        </h1>
                        <p class="armvw-hero__tagline">
                            <?php echo esc_html__('Show interactive 3D models and augmented reality previews of your products directly in the browser, on both Android and iOS devices.', 'ar-model-viewer-for-woocommerce'); ?>
                        </p>
                    </div>
                </div>

                <div class="armvw-hero__meta">
                    <span class="armvw-chip armvw-chip--brand">
                        <?php echo esc_html(sprintf(/* translators: %s: plugin version. */ __('Version %s', 'ar-model-viewer-for-woocommerce'), AR_MODEL_VIEWER_FOR_WOOCOMMERCE_VERSION)); ?>
                    </span>
                    <span class="armvw-chip armvw-chip--<?php echo 'yes' === $armvw_ar_state ? 'ok' : 'off'; ?>">
                        <span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
                        <?php echo 'yes' === $armvw_ar_state
                            ? esc_html__('AR enabled', 'ar-model-viewer-for-woocommerce')
                            : esc_html__('AR disabled', 'ar-model-viewer-for-woocommerce'); ?>
                    </span>
                    <span class="armvw-chip armvw-chip--<?php echo $armvw_button_state ? 'ok' : 'off'; ?>">
                        <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                        <?php echo $armvw_button_state
                            ? esc_html__('3D button visible', 'ar-model-viewer-for-woocommerce')
                            : esc_html__('3D button hidden', 'ar-model-viewer-for-woocommerce'); ?>
                    </span>
                </div>
            </header>

            <?php $settings->render_preview_card(); ?>
        </div>

        <?php settings_errors(); ?>

        <?php if ($armvw_was_reset) : ?>
            <div class="notice notice-success">
                <p><?php echo esc_html__('The default settings were restored. Remember to save your API key again if you were using the AI features.', 'ar-model-viewer-for-woocommerce'); ?></p>
            </div>
        <?php endif; ?>

        <form id="armvw-settings-form" class="armvw-form" method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
            <?php settings_fields(Ar_Model_Viewer_For_Woocommerce_Settings::GROUP); ?>

            <?php $settings->render_tab_nav($tabs); ?>

            <?php
            $armvw_is_first = true;

            foreach ($tabs as $armvw_slug => $armvw_tab) {
                $settings->render_panel($armvw_slug, $armvw_tab, $fields, $armvw_is_first);
                $armvw_is_first = false;
            }
            ?>

            <div class="armvw-actions">
                <?php submit_button(esc_html__('Save settings', 'ar-model-viewer-for-woocommerce'), 'primary', 'submit', false); ?>

                <a
                    class="armvw-reset"
                    href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('page' => Ar_Model_Viewer_For_Woocommerce_Settings::PAGE_SLUG, 'armvw-reset' => '1'), admin_url('options-general.php')), 'armvw_reset_settings')); ?>"
                    data-armvw-confirm="<?php echo esc_attr__('Restore every setting to its default value? Your products and 3D models are not affected.', 'ar-model-viewer-for-woocommerce'); ?>"
                >
                    <?php echo esc_html__('Restore default values', 'ar-model-viewer-for-woocommerce'); ?>
                </a>

                <span class="armvw-actions__hint">
                    <?php echo esc_html__('Every setting is stored in a single WordPress option.', 'ar-model-viewer-for-woocommerce'); ?>
                </span>
            </div>
        </form>
    </div>

    <section class="armvw-cards" aria-label="<?php echo esc_attr__('Plugin status, preview and resources', 'ar-model-viewer-for-woocommerce'); ?>">
        <?php $settings->render_sidebar(); ?>
    </section>
</div>
