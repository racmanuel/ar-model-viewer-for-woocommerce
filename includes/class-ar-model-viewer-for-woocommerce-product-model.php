<?php
/**
 * The file that defines the per product model of the plugin.
 *
 * A 3D model is attached to a product by three meta values (the file itself, a poster and
 * an alt text), and every product can override a set of viewer attributes that only make
 * sense for its own geometry: where the camera starts, how the object is oriented, whether
 * it has an animation. This class is the single place where those values are read, defaulted
 * and validated, so the shortcode, the product tab and the admin preview can never disagree.
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
 * Per product model data and viewer overrides.
 *
 * @since      3.0.0
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/includes
 */
class Ar_Model_Viewer_For_Woocommerce_Product_Model
{
    /**
     * Meta key of the 3D file.
     *
     * The three keys below are the ones the previous metabox library already used. They are
     * kept untouched so no product loses its model.
     *
     * @since 3.0.0
     * @var   string
     */
    const META_SOURCE = 'ar_model_viewer_for_woocommerce_file_object';

    /**
     * Meta key of the poster image.
     *
     * @since 3.0.0
     * @var   string
     */
    const META_POSTER = 'ar_model_viewer_for_woocommerce_file_poster';

    /**
     * Meta key of the alt text.
     *
     * @since 3.0.0
     * @var   string
     */
    const META_ALT = 'ar_model_viewer_for_woocommerce_file_alt';

    /**
     * Prefix shared by every meta key of a product override.
     *
     * @since 3.0.0
     * @var   string
     */
    const META_PREFIX = 'ar_model_viewer_for_woocommerce_';

    /**
     * Memoized resolutions for the current request, keyed by product id and blog id.
     *
     * A product page renders the viewer once but reads the model several times (the tab, the
     * button, the schema), and every read hits the meta cache. This keeps it to one lookup.
     *
     * @since 3.0.0
     * @var   array<int, array<string, mixed>>
     */
    private static $resolved = array();

    /**
     * Describe the viewer attributes that can be overridden per product.
     *
     * `attribute` is the HTML attribute the value is printed as, and `type` is the validation
     * applied on save. The default value of every field is the empty string, which is what
     * "inherit from the library" looks like: no attribute is printed and the viewer keeps its
     * own default.
     *
     * @since 3.0.0
     * @return array<string, array<string, string>> Field definitions keyed by short name.
     */
    public static function fields()
    {
        return array(
            'camera_orbit' => array(
                'attribute' => 'camera-orbit',
                'type' => 'vector',
                'label' => __('Starting camera position', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Angle and distance the camera starts at, as `theta phi radius`, for example `0deg 75deg 105%`. The percentage frames the model the same way regardless of its size, which is why it is the usual starting point.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '0deg 75deg 105%',
            ),
            'camera_target' => array(
                'attribute' => 'camera-target',
                'type' => 'vector',
                'label' => __('Point the camera orbits', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Point the model turns around, as `x y z` in metres, for example `0m 0.5m 0m`. Use `auto` in a dimension to let the viewer centre the model itself.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => 'auto auto auto',
            ),
            'field_of_view' => array(
                'attribute' => 'field-of-view',
                'type' => 'angle',
                'label' => __('Field of view', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Vertical angle the camera covers, for example `30deg`. A smaller value flattens the perspective the way a telephoto lens does, a wider one shows more of the model at once.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => 'auto',
            ),
            'orientation' => array(
                'attribute' => 'orientation',
                'type' => 'vector',
                'label' => __('Orientation', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Turns the model itself, as `roll pitch yaw` in degrees. This is the field to fix a model that arrives lying down or facing backwards, without having to edit the file.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '0deg 0deg 0deg',
            ),
            'scale' => array(
                'attribute' => 'scale',
                'type' => 'vector',
                'label' => __('Scale', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Size on the three axes, for example `1 1 1`. Mind that this value is also the one AR uses to decide the real size of the object, so changing it changes how the product is placed on the floor of the shopper.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '1 1 1',
            ),
            'animation_name' => array(
                'attribute' => 'animation-name',
                'type' => 'text',
                'label' => __('Animation', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Name of the animation to play when the model has more than one. Leave it empty to play the first animation the file contains.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '',
            ),
            'variant_name' => array(
                'attribute' => 'variant-name',
                'type' => 'text',
                'label' => __('Variant', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Name of the variant to show, if the model carries variants such as colourways or materials.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '',
            ),
            'autoplay' => array(
                'attribute' => 'autoplay',
                'type' => 'toggle',
                'label' => __('Play the animation on load', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Starts the animation as soon as the model is revealed. It only applies to models that have one.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => '',
            ),
            'ios_src' => array(
                'attribute' => 'ios-src',
                'type' => 'resource',
                'label' => __('USDZ file for iPhone', 'ar-model-viewer-for-woocommerce'),
                'help' => __('File used by AR Quick Look on iPhone. Without it, an iPhone generates the USDZ from the main file on the fly when the shopper taps the AR button, which takes a moment and can lose the animations.', 'ar-model-viewer-for-woocommerce'),
                'placeholder' => 'https://example.com/modelo.usdz',
            ),
        );
    }

    /**
     * Full meta key of a product override.
     *
     * @since 3.0.0
     * @param string $short Short field name, for example `camera_orbit`.
     * @return string The meta key.
     */
    public static function meta_key($short)
    {
        return self::META_PREFIX . $short;
    }

    /**
     * Read the model of a product, with the fallbacks of the original plugin.
     *
     * The file itself has no fallback: without it there is nothing to render. The poster falls
     * back to the featured image of the product and the alt text to its name and then to its
     * short description, which is what the description of the fields always promised.
     *
     * @since 3.0.0
     * @param WC_Product $product Product object.
     * @return array<string, string> `source`, `poster`, `alt` and where each value came from.
     */
    public static function resolve($product)
    {
        $key = $product->get_id();

        if (isset(self::$resolved[$key])) {
            return self::$resolved[$key];
        }

        $source = (string) $product->get_meta(self::META_SOURCE, true);

        $poster = trim((string) $product->get_meta(self::META_POSTER, true));
        $poster_source = 'own';

        if ('' === $poster) {
            $image_id = (int) $product->get_image_id();
            $poster = $image_id ? (string) wp_get_attachment_url($image_id) : '';
            $poster_source = $image_id ? 'featured' : 'none';
        }

        $alt = trim((string) $product->get_meta(self::META_ALT, true));
        $alt_source = 'own';

        if ('' === $alt) {
            $alt = (string) $product->get_name();
            $alt_source = 'name';
        }

        if ('' === $alt) {
            $alt = (string) $product->get_short_description();
            $alt_source = 'description';
        }

        self::$resolved[$key] = array(
            'source' => $source,
            'poster' => $poster,
            'poster_source' => $poster_source,
            'alt' => $alt,
            'alt_source' => $alt_source,
        );

        return self::$resolved[$key];
    }

    /**
     * Read the overrides of a product as a list of viewer attributes.
     *
     * A field left empty is not an attribute with an empty value: it is an instruction to let
     * the viewer decide, so it is dropped from the result. That is what makes these fields
     * additive instead of destructive, and why a product saved before this class existed
     * continues to render exactly as it did.
     *
     * @since 3.0.0
     * @param WC_Product $product Product object.
     * @return array<string, mixed> Attribute name => value, ready for Settings::render_attributes().
     */
    public static function attributes($product)
    {
        $attributes = array();

        foreach (self::fields() as $short => $field) {
            $value = self::read($product, $short, $field['type']);

            if ('' === $value) {
                continue;
            }

            $attributes[$field['attribute']] = $value;
        }

        return $attributes;
    }

    /**
     * Read and validate a single override of a product.
     *
     * @since 3.0.0
     * @param WC_Product $product Product object.
     * @param string     $short   Short field name.
     * @param string     $type    Validation type.
     * @return mixed The value, `true` for an enabled toggle, or an empty string when empty.
     */
    public static function read($product, $short, $type)
    {
        $raw = $product->get_meta(self::meta_key($short), true);

        if ('toggle' === $type) {
            return 'yes' === $raw ? true : '';
        }

        return self::sanitize($type, $raw);
    }

    /**
     * Save the overrides submitted from the product editor.
     *
     * An emptied field deletes its meta instead of storing an empty string, so "inherit" has a
     * single representation in the database and a product never carries a value that means
     * nothing.
     *
     * @since 3.0.0
     * @param int   $product_id Product id.
     * @param array $input      Raw submitted values keyed by short field name.
     * @return void
     */
    public static function save($product_id, array $input)
    {
        foreach (self::fields() as $short => $field) {
            $raw = isset($input[$short]) ? $input[$short] : '';
            $value = self::sanitize($field['type'], $raw);
            $meta_key = self::meta_key($short);

            if ('' === $value || false === $value) {
                delete_post_meta($product_id, $meta_key);
                continue;
            }

            update_post_meta($product_id, $meta_key, $value);
        }
    }

    /**
     * Save the three values that attach a model to a product.
     *
     * The meta keys are the same ones the metabox library used, so a product that already had a
     * model keeps it: this only changes who writes the values, not where they live. A field left
     * empty deletes its meta, which is what makes the poster and the alt text fall back to the
     * featured image and the product name.
     *
     * @since 3.0.0
     * @param int   $product_id Product id.
     * @param array $input      Raw submitted values: `file_object`, `file_poster` and `file_alt`.
     * @return void
     */
    public static function save_files($product_id, array $input)
    {
        $values = array(
            self::META_SOURCE => isset($input['file_object']) ? esc_url_raw(trim((string) $input['file_object'])) : '',
            self::META_POSTER => isset($input['file_poster']) ? esc_url_raw(trim((string) $input['file_poster'])) : '',
            self::META_ALT => isset($input['file_alt']) ? sanitize_text_field((string) $input['file_alt']) : '',
        );

        foreach ($values as $meta_key => $value) {
            if ('' === $value) {
                delete_post_meta($product_id, $meta_key);
                continue;
            }

            update_post_meta($product_id, $meta_key, $value);
        }

        // The resolver memoizes the product, and the values it read are stale now.
        unset(self::$resolved[$product_id]);
    }

    /**
     * Validate a value against the type declared by its field.
     *
     * The numeric, angular and URL cases reuse the validators of the settings class, so a value
     * typed in the product editor and the same value typed in the settings screen are accepted
     * and stored in exactly the same way.
     *
     * @since 3.0.0
     * @param string $type Validation type.
     * @param mixed  $raw  Raw submitted value.
     * @return string The sanitized value, or an empty string.
     */
    private static function sanitize($type, $raw)
    {
        switch ($type) {
            case 'angle':
                return Ar_Model_Viewer_For_Woocommerce_Settings::sanitize_angle($raw);

            case 'resource':
                return Ar_Model_Viewer_For_Woocommerce_Settings::sanitize_resource(array('keywords' => array()), $raw);

            case 'vector':
                return self::sanitize_vector($raw);

            case 'toggle':
                return 'yes' === $raw ? 'yes' : '';

            case 'text':
            default:
                return is_scalar($raw) ? sanitize_text_field(wp_unslash($raw)) : '';
        }
    }

    /**
     * Validate a three component value such as `camera-orbit` or `scale`.
     *
     * Accepts three space separated numbers with an optional unit, and the keyword `auto` in any
     * of the three. Expressions such as `calc()` or `env()` are deliberately not accepted: they
     * are for scroll driven cameras, and a typo there is invisible in the editor.
     *
     * @since 3.0.0
     * @param mixed $raw Raw submitted value.
     * @return string The sanitized value, or an empty string.
     */
    private static function sanitize_vector($raw)
    {
        if (!is_scalar($raw)) {
            return '';
        }

        $value = str_replace(',', '.', strtolower(trim(sanitize_text_field(wp_unslash($raw)))));

        if ('' === $value) {
            return '';
        }

        $component = '(auto|-?[0-9]+(\.[0-9]+)?(deg|rad|m|cm|mm|%)?)';

        if (!preg_match('/^' . $component . '(\s+' . $component . '){2}$/', $value)) {
            return '';
        }

        return $value;
    }
}
