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
     * Version of the settings shape.
     *
     * It is bumped whenever a stored value changes meaning, so the migration in
     * `maybe_upgrade()` runs once per change.
     *
     * @since 3.0.0
     * @var   string
     */
    const VERSION = '4';

    /**
     * Option that remembers the settings shape already migrated.
     *
     * @since 3.0.0
     * @var   string
     */
    const VERSION_OPTION = 'ar_model_viewer_for_woocommerce_settings_version';

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
                    'default' => '11',
                    'choices' => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11'),
                    'allow_empty' => true,
                ),
                'ar_model_viewer_for_woocommerce_single_product_tabs' => array(
                    'type' => 'radio',
                    'default' => 'yes',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_button_text' => array(
                    'type' => 'text',
                    'default' => 'View in 3D',
                ),
                'ar_model_viewer_for_woocommerce_tab_title' => array(
                    'type' => 'text',
                    'default' => 'View Product on 3D',
                ),
                'ar_model_viewer_for_woocommerce_loading' => array(
                    'type' => 'radio',
                    'default' => 'auto',
                    // `lazy` is not offered any more: the library documents `auto` and `lazy` as the
                    // same value, so two options would describe one single behaviour.
                    'choices' => array('auto', 'eager'),
                ),
                'ar_model_viewer_for_woocommerce_reveal' => array(
                    'type' => 'radio',
                    'default' => 'auto',
                    // `interaction` is not a valid value: `auto` and `manual` are the only ones the
                    // library accepts. Offering it emitted an attribute the viewer ignored.
                    'choices' => array('auto', 'manual'),
                ),
                'ar_model_viewer_for_woocommerce_with_credentials' => array(
                    'type' => 'radio',
                    'default' => 'yes',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_poster_color' => array(
                    'type' => 'color',
                    'default' => 'rgb(255,255,255)',
                    'alpha' => true,
                ),
                // Lighting. Every default is the empty string, which means "do not print the
                // attribute at all": the viewer keeps the value documented by the library, so a
                // store that never opens this tab renders exactly as it did before.
                'ar_model_viewer_for_woocommerce_tone_mapping' => array(
                    'type' => 'select',
                    'default' => '',
                    // The library default is `neutral`, the function it recommends for shops, so
                    // the empty option is the recommended one and the rest are explicit changes.
                    'choices' => array('aces', 'agx', 'reinhard', 'cineon', 'linear', 'none'),
                    'allow_empty' => true,
                ),
                'ar_model_viewer_for_woocommerce_exposure' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => 0,
                    'max' => 10,
                    'step' => '0.1',
                ),
                'ar_model_viewer_for_woocommerce_shadow_intensity' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => 0,
                    'max' => 1,
                    'step' => '0.05',
                ),
                'ar_model_viewer_for_woocommerce_shadow_softness' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => 0,
                    'max' => 1,
                    'step' => '0.05',
                ),
                'ar_model_viewer_for_woocommerce_environment_image' => array(
                    'type' => 'resource',
                    'default' => '',
                    // `neutral` and `legacy` are the two keywords the attribute accepts besides
                    // a URL to an .hdr or .jpg file.
                    'keywords' => array('neutral', 'legacy'),
                ),
                'ar_model_viewer_for_woocommerce_skybox_image' => array(
                    'type' => 'resource',
                    'default' => '',
                    'keywords' => array(),
                ),
                // Interaction. `camera-controls` and `auto-rotate` used to be hardcoded in the
                // markup; they are settings now, and their default keeps the previous behaviour
                // so nothing changes in a store that never opens this tab.
                'ar_model_viewer_for_woocommerce_camera_controls' => array(
                    'type' => 'radio',
                    'default' => 'yes',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_auto_rotate' => array(
                    'type' => 'radio',
                    'default' => 'yes',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_auto_rotate_delay' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => 0,
                    'max' => 60000,
                    'step' => '100',
                ),
                'ar_model_viewer_for_woocommerce_rotation_per_second' => array(
                    'type' => 'angle',
                    'default' => '',
                ),
                'ar_model_viewer_for_woocommerce_interaction_prompt' => array(
                    'type' => 'select',
                    'default' => '',
                    'choices' => array('auto', 'none'),
                    'allow_empty' => true,
                ),
                'ar_model_viewer_for_woocommerce_interaction_prompt_style' => array(
                    'type' => 'select',
                    'default' => '',
                    'choices' => array('wiggle', 'basic'),
                    'allow_empty' => true,
                ),
                'ar_model_viewer_for_woocommerce_interaction_prompt_threshold' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => 0,
                    'max' => 60000,
                    'step' => '100',
                ),
                'ar_model_viewer_for_woocommerce_touch_action' => array(
                    'type' => 'select',
                    'default' => '',
                    // `auto` is deliberately absent: the library does not accept it here, the
                    // equivalent behaviour is obtained by turning the camera controls off.
                    'choices' => array('pan-y', 'pan-x', 'none'),
                    'allow_empty' => true,
                ),
                'ar_model_viewer_for_woocommerce_disable_zoom' => array(
                    'type' => 'radio',
                    'default' => 'yes',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_disable_pan' => array(
                    'type' => 'radio',
                    'default' => 'no',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_disable_tap' => array(
                    'type' => 'radio',
                    'default' => 'no',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_orbit_sensitivity' => array(
                    'type' => 'number',
                    'default' => '',
                    // Negative values are allowed on purpose: they reverse the rotation, which is
                    // the documented way of looking at the inside of a model.
                    'min' => -10,
                    'max' => 10,
                    'step' => '0.1',
                ),
                'ar_model_viewer_for_woocommerce_zoom_sensitivity' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => -10,
                    'max' => 10,
                    'step' => '0.1',
                ),
                'ar_model_viewer_for_woocommerce_pan_sensitivity' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => -10,
                    'max' => 10,
                    'step' => '0.1',
                ),
                'ar_model_viewer_for_woocommerce_interpolation_decay' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => 1,
                    'max' => 5000,
                    'step' => '10',
                ),
                'ar_model_viewer_for_woocommerce_a11y' => array(
                    'type' => 'radio',
                    // On by default: it only adds the translations a screen reader needs, and a
                    // store that does not want them in the markup can turn it off.
                    'default' => 'yes',
                    'choices' => array('yes', 'no'),
                ),
                // Performance. The first three are not HTML attributes but static properties of
                // the element, so they are not printed in the markup: `static_properties()`
                // returns them and a small script applies them once the library is defined.
                'ar_model_viewer_for_woocommerce_minimum_render_scale' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => 0.25,
                    'max' => 1,
                    'step' => '0.05',
                ),
                'ar_model_viewer_for_woocommerce_power_preference' => array(
                    'type' => 'select',
                    'default' => '',
                    'choices' => array('high-performance', 'low-power', 'default'),
                    'allow_empty' => true,
                ),
                'ar_model_viewer_for_woocommerce_model_cache_size' => array(
                    'type' => 'number',
                    'default' => '',
                    'min' => 0,
                    'max' => 50,
                    'step' => '1',
                ),
                // Left empty the library downloads them from a Google CDN, which is a request to
                // a third party on every product page that uses a compressed model.
                'ar_model_viewer_for_woocommerce_draco_decoder_location' => array(
                    'type' => 'resource',
                    'default' => '',
                    'keywords' => array(),
                ),
                'ar_model_viewer_for_woocommerce_ktx2_transcoder_location' => array(
                    'type' => 'resource',
                    'default' => '',
                    'keywords' => array(),
                ),
                'ar_model_viewer_for_woocommerce_meshopt_decoder_location' => array(
                    'type' => 'resource',
                    'default' => '',
                    'keywords' => array(),
                ),
                'ar_model_viewer_for_woocommerce_ar' => array(
                    'type' => 'radio',
                    'default' => 'yes',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_ar_modes' => array(
                    'type' => 'checklist',
                    'default' => array('webxr', 'scene-viewer', 'quick-look'),
                    'choices' => array('webxr', 'scene-viewer', 'quick-look'),
                    // An empty list would be emitted as `ar-modes=""`, which is not a valid
                    // attribute, so at least one mode has to survive validation.
                    'min_items' => 1,
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
                    // Enabled by default to match the store's current AR presentation. It can be
                    // disabled when frame rate matters more than lighting realism.
                    'default' => 'yes',
                    'choices' => array('yes', 'no'),
                ),
                'ar_model_viewer_for_woocommerce_ar_button' => array(
                    'type' => 'radio',
                    'default' => 'no',
                    'choices' => array('yes', 'no'),
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
                'ar_model_viewer_for_woocommerce_logger' => array(
                    'type' => 'checkbox',
                    'default' => '1',
                ),
                'ar_model_viewer_for_woocommerce_analytics' => array(
                    'type' => 'checkbox',
                    'default' => '0',
                ),
                'ar_model_viewer_for_woocommerce_analytics_opt_out' => array(
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
            $ar_modes = self::get('ar_model_viewer_for_woocommerce_ar_modes');

            // The stored value may predate `min_items` or come from a direct database write, so the
            // empty list is normalized here too: consumers can rely on a non-empty array.
            if (!is_array($ar_modes) || array() === $ar_modes) {
                $defaults = self::defaults();
                $ar_modes = $defaults['ar_model_viewer_for_woocommerce_ar_modes'];
            }

            self::$viewer = array(
                'loading' => self::get('ar_model_viewer_for_woocommerce_loading'),
                'reveal' => self::get('ar_model_viewer_for_woocommerce_reveal'),
                'poster_color' => self::get('ar_model_viewer_for_woocommerce_poster_color'),
                'ar_modes' => array_values($ar_modes),
                'scale' => self::get('ar_model_viewer_for_woocommerce_ar_scale'),
                'placement' => self::get('ar_model_viewer_for_woocommerce_ar_placement'),
                'ar_button_text' => self::get('ar_model_viewer_for_woocommerce_ar_button_text'),
                'ar_button_background_color' => self::get('ar_model_viewer_for_woocommerce_ar_button_background_color'),
                'ar_button_text_color' => self::get('ar_model_viewer_for_woocommerce_ar_button_text_color'),
                // Toggles are returned as booleans so no consumer has to compare strings.
                'with_credentials' => 'yes' === self::get('ar_model_viewer_for_woocommerce_with_credentials'),
                'ar' => 'yes' === self::get('ar_model_viewer_for_woocommerce_ar'),
                'xr_environment' => 'yes' === self::get('ar_model_viewer_for_woocommerce_xr_environment'),
                'ar_button' => 'yes' === self::get('ar_model_viewer_for_woocommerce_ar_button'),
            );
        }

        return self::$viewer;
    }

    /**
     * Return the viewer attributes that every placement shares.
     *
     * These settings map one to one to an HTML attribute, with no conditional logic in
     * between, so the key of the array is the attribute name and the value is what has to be
     * printed. Consumers that build markup (shortcode, product tab and admin preview) append
     * the result to their own attributes, which means a new appearance setting reaches every
     * placement at once instead of having to be repeated in three files.
     *
     * An empty value means "let the library decide", so the attribute is dropped from the
     * output. That keeps the markup of existing installations unchanged.
     *
     * @since 3.0.0
     * @return array<string, string> Attribute name => value, empty values removed.
     */
    public static function shared_attributes()
    {
        $attributes = array(
            // Boolean attributes: they are printed on their own when the setting is enabled and
            // left out when it is not, which is how the library documents them.
            'camera-controls' => self::toggle('ar_model_viewer_for_woocommerce_camera_controls'),
            'auto-rotate' => self::toggle('ar_model_viewer_for_woocommerce_auto_rotate'),
            'auto-rotate-delay' => self::get('ar_model_viewer_for_woocommerce_auto_rotate_delay'),
            'rotation-per-second' => self::get('ar_model_viewer_for_woocommerce_rotation_per_second'),
            'interaction-prompt' => self::get('ar_model_viewer_for_woocommerce_interaction_prompt'),
            'interaction-prompt-style' => self::get('ar_model_viewer_for_woocommerce_interaction_prompt_style'),
            'interaction-prompt-threshold' => self::get('ar_model_viewer_for_woocommerce_interaction_prompt_threshold'),
            'touch-action' => self::get('ar_model_viewer_for_woocommerce_touch_action'),
            'disable-zoom' => self::toggle('ar_model_viewer_for_woocommerce_disable_zoom'),
            'disable-pan' => self::toggle('ar_model_viewer_for_woocommerce_disable_pan'),
            'disable-tap' => self::toggle('ar_model_viewer_for_woocommerce_disable_tap'),
            'orbit-sensitivity' => self::get('ar_model_viewer_for_woocommerce_orbit_sensitivity'),
            'zoom-sensitivity' => self::get('ar_model_viewer_for_woocommerce_zoom_sensitivity'),
            'pan-sensitivity' => self::get('ar_model_viewer_for_woocommerce_pan_sensitivity'),
            'interpolation-decay' => self::get('ar_model_viewer_for_woocommerce_interpolation_decay'),
            // The library ships its accessibility strings in English only, so they are translated
            // here instead of being exposed as twelve text fields nobody would fill in.
            'a11y' => self::a11y_strings(),
            'tone-mapping' => self::get('ar_model_viewer_for_woocommerce_tone_mapping'),
            'exposure' => self::get('ar_model_viewer_for_woocommerce_exposure'),
            'shadow-intensity' => self::get('ar_model_viewer_for_woocommerce_shadow_intensity'),
            'shadow-softness' => self::get('ar_model_viewer_for_woocommerce_shadow_softness'),
            'environment-image' => self::get('ar_model_viewer_for_woocommerce_environment_image'),
            'skybox-image' => self::get('ar_model_viewer_for_woocommerce_skybox_image'),
        );

        return array_filter($attributes, array(__CLASS__, 'is_filled'));
    }

    /**
     * Return the static properties of the viewer element.
     *
     * These settings are not HTML attributes: the library exposes them as static properties of
     * the element class, so they cannot travel in the markup and a script has to assign them once
     * `model-viewer` is defined. They are returned here, with numeric values already cast to
     * numbers, so the script only has to copy them.
     *
     * @since 3.0.0
     * @return array<string, mixed> Property name => value, empty values removed.
     */
    public static function static_properties()
    {
        $scale = self::get('ar_model_viewer_for_woocommerce_minimum_render_scale');
        $cache = self::get('ar_model_viewer_for_woocommerce_model_cache_size');

        $properties = array(
            'minimumRenderScale' => '' === $scale ? '' : (float) $scale,
            'powerPreference' => self::get('ar_model_viewer_for_woocommerce_power_preference'),
            'modelCacheSize' => '' === $cache ? '' : (int) $cache,
            'dracoDecoderLocation' => self::get('ar_model_viewer_for_woocommerce_draco_decoder_location'),
            'ktx2TranscoderLocation' => self::get('ar_model_viewer_for_woocommerce_ktx2_transcoder_location'),
            'meshoptDecoderLocation' => self::get('ar_model_viewer_for_woocommerce_meshopt_decoder_location'),
        );

        return array_filter($properties, array(__CLASS__, 'is_filled'));
    }

    /**
     * Whether a value has to be taken into account.
     *
     * The empty string is what "do not print it" looks like in this plugin, but `0` and `0.0` are
     * meaningful values (`shadow-intensity="0"` turns the shadow off, `modelCacheSize="0"` empties
     * the cache), so they must survive the filter. Booleans are handled first because `false` is
     * dropped while `true` is the marker of a boolean attribute.
     *
     * @since 3.0.0
     * @param mixed $value Value to check.
     * @return bool True when the value has to be used.
     */
    private static function is_filled($value)
    {
        if (true === $value) {
            return true;
        }

        if (is_bool($value) || null === $value) {
            return false;
        }

        if (is_array($value)) {
            return array() !== $value;
        }

        return '' !== $value;
    }

    /**
     * Read a yes/no setting as a boolean attribute value.
     *
     * @since 3.0.0
     * @param string $key Option key.
     * @return bool True when the setting is enabled.
     */
    private static function toggle($key)
    {
        return 'yes' === self::get($key);
    }

    /**
     * Serialize a set of attributes into markup.
     *
     * @since 3.0.0
     * @param array<string, string> $attributes Attribute name => value.
     * @return string A leading-space separated list of escaped attributes, or an empty string.
     */
    public static function render_attributes(array $attributes)
    {
        $markup = '';

        foreach ($attributes as $name => $value) {
            if (true === $value) {
                // Boolean attributes carry no value, so they are printed on their own.
                $markup .= ' ' . sanitize_key($name);
                continue;
            }

            $markup .= ' ' . sanitize_key($name) . '="' . esc_attr($value) . '"';
        }

        return $markup;
    }

    /**
     * Migrate stored values when the shape of the settings changes.
     *
     * Toggles were stored as `active`, `deactivate`, `deactive`, `true` and `false` depending on
     * the field. They are all `yes`/`no` now. This runs before anything reads the settings, so
     * the front end never sees a value it cannot compare.
     *
     * @since 3.0.0
     * @return void
     */
    public static function maybe_upgrade()
    {
        if (self::VERSION === get_option(self::VERSION_OPTION)) {
            return;
        }

        $stored = get_option(self::OPTION_KEY, array());

        if (is_array($stored)) {
            $map = array(
                'active' => 'yes',
                'deactivate' => 'no',
                'deactive' => 'no',
                'true' => 'yes',
                'false' => 'no',
            );

            $toggles = array(
                'ar_model_viewer_for_woocommerce_ar',
                'ar_model_viewer_for_woocommerce_xr_environment',
                'ar_model_viewer_for_woocommerce_ar_button',
                'ar_model_viewer_for_woocommerce_with_credentials',
            );

            foreach ($toggles as $key) {
                if (isset($stored[$key]) && is_string($stored[$key]) && isset($map[$stored[$key]])) {
                    $stored[$key] = $map[$stored[$key]];
                }
            }

            // Values the settings screen used to offer but that the library does not accept (or
            // that behave exactly like another value) are translated here. Without this, an
            // existing installation would keep emitting an attribute the viewer ignores.
            $retired = array(
                'ar_model_viewer_for_woocommerce_reveal' => array('interaction' => 'auto'),
                'ar_model_viewer_for_woocommerce_loading' => array('lazy' => 'auto'),
            );

            foreach ($retired as $key => $values) {
                if (isset($stored[$key]) && is_string($stored[$key]) && isset($values[$stored[$key]])) {
                    $stored[$key] = $values[$stored[$key]];
                }
            }

            unset($stored['ar_model_viewer_for_woocommerce_api_key_meshy']);

            update_option(self::OPTION_KEY, $stored);
        }

        self::flush();

        update_option(self::VERSION_OPTION, self::VERSION);
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
                $values = array_values(array_intersect($definition['choices'], $values));

                // A checklist that requires items falls back to its default instead of being
                // stored empty, so the markup never receives an attribute with no value.
                if (array() === $values && !empty($definition['min_items'])) {
                    return $definition['default'];
                }

                return $values;

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

            case 'number':
                return self::sanitize_number($definition, $raw);

            case 'angle':
                return self::sanitize_angle($raw);

            case 'resource':
                return self::sanitize_resource($definition, $raw);

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
     * Validate a numeric value, clamped to the range declared by the field.
     *
     * Values are stored as strings because the empty string is meaningful here: it means "do
     * not print the attribute". A comma is accepted as the decimal separator so a numeric
     * keyboard in a Spanish locale does not throw the value away.
     *
     * @since 3.0.0
     * @param array $definition Field definition.
     * @param mixed $raw        Raw submitted value.
     * @return string The sanitized number, or an empty string.
     */
    private static function sanitize_number(array $definition, $raw)
    {
        if (!is_scalar($raw)) {
            return '';
        }

        $value = str_replace(',', '.', trim(sanitize_text_field(wp_unslash($raw))));

        if ('' === $value) {
            return '';
        }

        if (!is_numeric($value)) {
            return '';
        }

        $number = (float) $value;

        if (isset($definition['min'])) {
            $number = max($number, (float) $definition['min']);
        }

        if (isset($definition['max'])) {
            $number = min($number, (float) $definition['max']);
        }

        // Trailing zeros are trimmed so `1.00` and `1` are stored, and compared, the same way.
        return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
    }

    /**
     * Validate an angle or a rotation speed.
     *
     * `rotation-per-second` accepts a number in radians, or a value with an explicit unit:
     * degrees (`30deg`), radians (`0.5rad`) or a percentage of the default speed (`-100%`).
     *
     * @since 3.0.0
     * @param mixed $raw Raw submitted value.
     * @return string The sanitized value, or an empty string when it is not an angle.
     */
    public static function sanitize_angle($raw)
    {
        if (!is_scalar($raw)) {
            return '';
        }

        $value = str_replace(',', '.', strtolower(trim(sanitize_text_field(wp_unslash($raw)))));

        if ('' === $value) {
            return '';
        }

        if (!preg_match('/^-?[0-9]+(\.[0-9]+)?(deg|rad|%)?$/', $value)) {
            return '';
        }

        return $value;
    }

    /**
     * Validate an image source that can also be one of the keywords of the attribute.
     *
     * `environment-image` accepts `neutral` and `legacy` in addition to a URL, so a plain
     * `esc_url_raw()` would silently drop those two values.
     *
     * @since 3.0.0
     * @param array $definition Field definition.
     * @param mixed $raw        Raw submitted value.
     * @return string The sanitized value, or an empty string.
     */
    public static function sanitize_resource(array $definition, $raw)
    {
        if (!is_scalar($raw)) {
            return '';
        }

        $value = trim(sanitize_text_field(wp_unslash($raw)));

        if ('' === $value) {
            return '';
        }

        $keywords = isset($definition['keywords']) ? $definition['keywords'] : array();

        if (in_array($value, $keywords, true)) {
            return $value;
        }

        return esc_url_raw($value);
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
     * Build the JSON that the `a11y` attribute expects.
     *
     * The attribute takes the twelve orientations of the model plus the text of the interaction
     * prompt, because a screen reader has to describe both. The library only ships them in
     * English, so building the JSON from translated strings is what makes the viewer understandable
     * in the language of the store. Returns an empty string when the setting is off, so the
     * attribute is left out of the markup entirely.
     *
     * @since 3.0.0
     * @return string A JSON object, or an empty string.
     */
    private static function a11y_strings()
    {
        if (!self::toggle('ar_model_viewer_for_woocommerce_a11y')) {
            return '';
        }

        $strings = array(
            'front' => __('Front of the model', 'ar-model-viewer-for-woocommerce'),
            'back' => __('Back of the model', 'ar-model-viewer-for-woocommerce'),
            'left' => __('Left side of the model', 'ar-model-viewer-for-woocommerce'),
            'right' => __('Right side of the model', 'ar-model-viewer-for-woocommerce'),
            'upper-front' => __('Upper front of the model', 'ar-model-viewer-for-woocommerce'),
            'upper-back' => __('Upper back of the model', 'ar-model-viewer-for-woocommerce'),
            'upper-left' => __('Upper left of the model', 'ar-model-viewer-for-woocommerce'),
            'upper-right' => __('Upper right of the model', 'ar-model-viewer-for-woocommerce'),
            'lower-front' => __('Lower front of the model', 'ar-model-viewer-for-woocommerce'),
            'lower-back' => __('Lower back of the model', 'ar-model-viewer-for-woocommerce'),
            'lower-left' => __('Lower left of the model', 'ar-model-viewer-for-woocommerce'),
            'lower-right' => __('Lower right of the model', 'ar-model-viewer-for-woocommerce'),
            'interaction-prompt' => __('Use the mouse, a finger or the arrow keys to move the model', 'ar-model-viewer-for-woocommerce'),
        );

        return wp_json_encode($strings);
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

}
