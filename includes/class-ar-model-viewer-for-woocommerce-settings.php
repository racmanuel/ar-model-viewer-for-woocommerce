<?php
/**
 * The file that defines the settings service of the plugin.
 *
 * This class is the single source of truth for the plugin settings: the list of
 * fields, their default values, their validation rules and the normalized array
 * used by the public side of the site.
 *
 * @link       https://racmanuel.dev
 * @since      3.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Settings definition, storage and validation.
 *
 * The plugin stores every setting in a single autoloaded option
 * (`ar_model_viewer_for_woocommerce_settings`) to keep one database read per request.
 * Field definitions live here so defaults, validation and rendering can never drift
 * apart again.
 *
 * @since      3.0.0
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/includes
 */
class Ar_Model_Viewer_For_Woocommerce_Settings
{
    /**
     * The option name where every setting is stored.
     *
     * The page slug is intentionally the same value: the current admin URL
     * (`options-general.php?page=...`) and the Freemius menu registration are
     * preserved after migrating away from the previous metabox library.
     *
     * @since 3.0.0
     * @var   string
     */
    const OPTION_KEY = 'ar_model_viewer_for_woocommerce_settings';

    /**
     * The admin page slug of the settings screen.
     *
     * @since 3.0.0
     * @var   string
     */
    const PAGE_SLUG = 'ar_model_viewer_for_woocommerce_settings';

    /**
     * The settings group used by the Settings API to save the option.
     *
     * @since 3.0.0
     * @var   string
     */
    const GROUP = 'ar_model_viewer_for_woocommerce_settings_group';

    /**
     * The capability required to read and write the settings.
     *
     * @since 3.0.0
     * @var   string
     */
    const CAPABILITY = 'manage_options';

    /**
     * Memoized definitions for the current request.
     *
     * @since 3.0.0
     * @var   array|null
     */
    private static $definitions = null;

    /**
     * Memoized raw option value for the current request.
     *
     * @since 3.0.0
     * @var   array|null
     */
    private static $values = null;

    /**
     * Memoized normalized options used by the viewer.
     *
     * @since 3.0.0
     * @var   array|null
     */
    private static $viewer = null;

    /**
     * Return the technical definition of every setting.
     *
     * This array carries the type, the default value and the allowed values of each
     * field. It deliberately contains no translated strings so it can be used on the
     * front end (defaults and validation) without paying the translation cost.
     *
     * @since 3.0.0
     * @return array<string, array<string, mixed>> Field definitions keyed by option key.
     */
    public static function definitions()
    {
        if (null === self::$definitions) {
            self::$definitions = array(
                'ar_model_viewer_for_woocommerce_btn' => array(
                    'type' => 'select',
                    'default' => '2',
                    'choices' => array('1', '2', '3', '4', '5', '6'),
                    'allow_empty' => true,
                ),
                'ar_model_viewer_for_woocommerce_single_product_tabs' => array(
                    'type' => 'radio',
                    'default' => 'yes',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_loading' => array(
                    'type' => 'radio',
                    'default' => 'auto',
                    'choices' => array('auto', 'lazy', 'eager'),
                ),
                'ar_model_viewer_for_woocommerce_reveal' => array(
                    'type' => 'radio',
                    'default' => 'auto',
                    'choices' => array('auto', 'manual', 'interaction'),
                ),
                'ar_model_viewer_for_woocommerce_with_credentials' => array(
                    'type' => 'radio',
                    'default' => 'false',
                    'choices' => array('false', 'true'),
                ),
                'ar_model_viewer_for_woocommerce_poster_color' => array(
                    'type' => 'color',
                    'default' => 'rgba(255,255,255,0)',
                    'alpha' => true,
                ),
                'ar_model_viewer_for_woocommerce_ar' => array(
                    'type' => 'radio',
                    'default' => 'active',
                    'choices' => array('active', 'deactivate'),
                ),
                'ar_model_viewer_for_woocommerce_ar_modes' => array(
                    'type' => 'checklist',
                    'default' => array('webxr', 'scene-viewer', 'quick-look'),
                    'choices' => array('webxr', 'scene-viewer', 'quick-look'),
                ),
                'ar_model_viewer_for_woocommerce_ar_scale' => array(
                    'type' => 'radio',
                    'default' => 'auto',
                    'choices' => array('auto', 'fixed'),
                ),
                'ar_model_viewer_for_woocommerce_ar_placement' => array(
                    'type' => 'radio',
                    'default' => 'floor',
                    'choices' => array('floor', 'wall'),
                ),
                'ar_model_viewer_for_woocommerce_xr_environment' => array(
                    'type' => 'radio',
                    'default' => 'active',
                    'choices' => array('active', 'deactive'),
                ),
                'ar_model_viewer_for_woocommerce_ar_button' => array(
                    'type' => 'radio',
                    'default' => 'deactive',
                    'choices' => array('active', 'deactive'),
                ),
                'ar_model_viewer_for_woocommerce_ar_button_text' => array(
                    'type' => 'text',
                    'default' => '👋 Activate AR',
                ),
                'ar_model_viewer_for_woocommerce_ar_button_background_color' => array(
                    'type' => 'color',
                    'default' => '#ffffff',
                    'alpha' => false,
                ),
                'ar_model_viewer_for_woocommerce_ar_button_text_color' => array(
                    'type' => 'color',
                    'default' => '#000000',
                    'alpha' => false,
                ),
                'ar_model_viewer_for_woocommerce_api_key_meshy' => array(
                    'type' => 'password',
                    'default' => '',
                ),
                'ar_model_viewer_for_woocommerce_logger' => array(
                    'type' => 'checkbox',
                    'default' => '1',
                ),
            );
        }

        return self::$definitions;
    }

    /**
     * Return the default value of every setting.
     *
     * @since 3.0.0
     * @return array<string, mixed> Default values keyed by option key.
     */
    public static function defaults()
    {
        $defaults = array();

        foreach (self::definitions() as $key => $definition) {
            $defaults[$key] = $definition['default'];
        }

        return $defaults;
    }

    /**
     * Return every setting, with defaults applied to the keys that are missing.
     *
     * Because the option is stored as a single array, a new release can add keys without
     * a database migration: missing keys simply fall back to their default value.
     *
     * @since 3.0.0
     * @return array<string, mixed> Settings keyed by option key.
     */
    public static function all()
    {
        if (null === self::$values) {
            $stored = get_option(self::OPTION_KEY, array());

            self::$values = array_merge(self::defaults(), is_array($stored) ? $stored : array());
        }

        return self::$values;
    }

    /**
     * Return a single setting.
     *
     * @since 3.0.0
     * @param string $key      Option key.
     * @param mixed  $fallback Value returned when the key is unknown.
     * @return mixed The setting value, or the fallback for unknown keys.
     */
    public static function get($key, $fallback = null)
    {
        $values = self::all();

        if (array_key_exists($key, $values)) {
            return $values[$key];
        }

        return $fallback;
    }

    /**
     * Return the normalized options consumed by the 3D viewer markup.
     *
     * Public-facing classes (shortcode, product tab and templates) use these short keys,
     * so this method keeps their markup untouched after the migration.
     *
     * @since 3.0.0
     * @return array<string, mixed> Viewer options.
     */
    public static function viewer_options()
    {
        if (null === self::$viewer) {
            self::$viewer = array(
                'loading' => self::get('ar_model_viewer_for_woocommerce_loading'),
                'reveal' => self::get('ar_model_viewer_for_woocommerce_reveal'),
                'with_credentials' => self::get('ar_model_viewer_for_woocommerce_with_credentials'),
                'poster_color' => self::get('ar_model_viewer_for_woocommerce_poster_color'),
                'ar' => self::get('ar_model_viewer_for_woocommerce_ar'),
                'ar_modes' => self::get('ar_model_viewer_for_woocommerce_ar_modes'),
                'scale' => self::get('ar_model_viewer_for_woocommerce_ar_scale'),
                'placement' => self::get('ar_model_viewer_for_woocommerce_ar_placement'),
                'xr_environment' => self::get('ar_model_viewer_for_woocommerce_xr_environment'),
            );
        }

        return self::$viewer;
    }

    /**
     * Validate and normalize the submitted settings.
     *
     * Used as the `sanitize_callback` of the registered setting. Every value is checked
     * against the allowed values of its field definition, and any key owned by a third
     * party add-on is preserved.
     *
     * @since 3.0.0
     * @param mixed $input Raw value submitted by the settings form.
     * @return array<string, mixed> Sanitized settings ready to be stored.
     */
    public static function sanitize($input)
    {
        $definitions = self::definitions();
        $current = self::all();
        $input = is_array($input) ? $input : array();
        $clean = array();

        foreach ($definitions as $key => $definition) {
            if ('checkbox' === $definition['type'] || 'checklist' === $definition['type']) {
                // A missing key means "nothing selected", so the default must not leak in.
                $fallback = $definition['default'];
            } elseif (array_key_exists($key, $current) && !array_key_exists($key, $input)) {
                // Field not submitted (for example an option removed from the screen).
                $fallback = $current[$key];
            } else {
                $fallback = $definition['default'];
            }

            $raw = array_key_exists($key, $input) ? $input[$key] : null;
            $clean[$key] = self::sanitize_value($definition, $raw, $fallback);
        }

        // Keep unknown keys so add-ons sharing this option are not wiped on save.
        foreach ($current as $key => $value) {
            if (!array_key_exists($key, $definitions)) {
                $clean[$key] = $value;
            }
        }

        self::flush();

        return $clean;
    }

    /**
     * Validate a single value against its field definition.
     *
     * @since 3.0.0
     * @param array $definition Field definition.
     * @param mixed $raw        Raw submitted value.
     * @param mixed $fallback   Value returned when validation fails.
     * @return mixed The sanitized value.
     */
    private static function sanitize_value(array $definition, $raw, $fallback)
    {
        switch ($definition['type']) {
            case 'checklist':
                if (!is_array($raw)) {
                    return $fallback;
                }

                $values = array_map('sanitize_text_field', wp_unslash($raw));

                return array_values(array_intersect($definition['choices'], $values));

            case 'checkbox':
                return empty($raw) ? '' : '1';

            case 'radio':
            case 'select':
                $value = is_scalar($raw) ? sanitize_text_field(wp_unslash($raw)) : '';

                if (in_array($value, $definition['choices'], true)) {
                    return $value;
                }

                if (!empty($definition['allow_empty']) && '' === $value) {
                    return '';
                }

                return $fallback;

            case 'color':
                return self::sanitize_color($raw, $fallback);

            case 'password':
            case 'text':
                return is_scalar($raw) ? sanitize_text_field(wp_unslash($raw)) : $fallback;

            default:
                return $fallback;
        }
    }

    /**
     * Validate a CSS color value.
     *
     * Accepts hexadecimal (3, 4, 6 and 8 digits), rgb(), rgba(), hsl(), hsla() and
     * `transparent`, because those are the formats the color picker can produce.
     *
     * @since 3.0.0
     * @param mixed  $raw      Raw submitted value.
     * @param string $fallback Value returned when the color is not valid.
     * @return string The sanitized color.
     */
    private static function sanitize_color($raw, $fallback)
    {
        if (!is_scalar($raw)) {
            return $fallback;
        }

        $value = strtolower(trim(sanitize_text_field(wp_unslash($raw))));

        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/', $value)) {
            return $value;
        }

        if (preg_match('/^(rgb|hsl)a?\(\s*[0-9.,%\s\/]+\)$/', $value)) {
            return $value;
        }

        if ('transparent' === $value) {
            return $value;
        }

        return $fallback;
    }

    /**
     * Restore every setting to its default value.
     *
     * @since 3.0.0
     * @return bool True when the option was updated.
     */
    public static function reset()
    {
        $current = self::all();
        $defaults = self::defaults();

        // Unknown keys (add-ons) are preserved on reset.
        foreach ($current as $key => $value) {
            if (!array_key_exists($key, $defaults)) {
                $defaults[$key] = $value;
            }
        }

        self::flush();

        return update_option(self::OPTION_KEY, $defaults);
    }

    /**
     * Clear the memoized values of the current request.
     *
     * @since 3.0.0
     * @return void
     */
    public static function flush()
    {
        self::$values = null;
        self::$viewer = null;
    }

    /**
     * Whether an API key for the 3D generation service is configured.
     *
     * @since 3.0.0
     * @return bool True when a key is stored.
     */
    public static function has_api_key()
    {
        $key = self::get('ar_model_viewer_for_woocommerce_api_key_meshy');

        return is_string($key) && '' !== trim($key);
    }
}
