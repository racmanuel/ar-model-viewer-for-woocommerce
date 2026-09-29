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
     * Meta key of the switch that turns on the private configuration of a variation.
     *
     * A variation that does not carry this flag inherits every value from its parent, which is
     * what keeps the database of a catalogue with thirty sizes from holding thirty copies of the
     * same model.
     *
     * @since 3.1.0
     * @var   string
     */
    const META_VARIATION_CUSTOM = 'ar_model_viewer_for_woocommerce_variation_custom';

    /**
     * Meta key of the 3D file of a variation.
     *
     * @since 3.1.0
     * @var   string
     */
    const META_VARIATION_SOURCE = 'ar_model_viewer_for_woocommerce_variation_file_object';

    /**
     * Meta key of the poster of a variation.
     *
     * @since 3.1.0
     * @var   string
     */
    const META_VARIATION_POSTER = 'ar_model_viewer_for_woocommerce_variation_file_poster';

    /**
     * Meta key of the USDZ file of a variation.
     *
     * @since 3.1.0
     * @var   string
     */
    const META_VARIATION_IOS_SRC = 'ar_model_viewer_for_woocommerce_variation_ios_src';

    /**
     * Prefix of the meta keys that remember the Media Library attachment behind a resource.
     *
     * The URL stays the source of truth because it is what travels in a CSV and what survives a
     * migration between installations. The id is kept next to it so the editor can show which
     * attachment was picked and so a URL broken by a change outside WordPress can be repaired.
     *
     * @since 3.1.0
     * @var   string
     */
    const META_ATTACHMENT = 'ar_model_viewer_for_woocommerce_attachment_';

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
     * Memoized effective configurations, keyed by `parent:variation`.
     *
     * A variable product asks for a configuration every time a shopper changes an option, and the
     * same variation is asked for again on the next visit to the page, so the answer is kept for
     * the rest of the request.
     *
     * @since 3.1.0
     * @var   array<string, array<string, mixed>>
     */
    private static $effective = array();

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
     * Diagnose the model resources without performing remote requests.
     *
     * @param WC_Product $product Product to inspect.
     * @return array<string, mixed>
     */
    public static function diagnostics($product)
    {
        $model = self::resolve($product);
        $issues = array();

        if ('' === trim($model['source'])) {
            $issues[] = 'missing-model';
        } else {
            $source_url = wp_parse_url($model['source']);
            $source_extension = strtolower(pathinfo((string) wp_parse_url($model['source'], PHP_URL_PATH), PATHINFO_EXTENSION));

            if (empty($source_url['scheme']) || empty($source_url['host'])) {
                $issues[] = 'invalid-model-url';
            } elseif (!in_array($source_extension, array('glb', 'gltf'), true)) {
                $issues[] = 'unsupported-model-format';
            }
        }

        if ('' !== trim($model['poster'])) {
            $poster_extension = strtolower(pathinfo((string) wp_parse_url($model['poster'], PHP_URL_PATH), PATHINFO_EXTENSION));

            if (!in_array($poster_extension, array('jpg', 'jpeg', 'png', 'webp', 'avif'), true)) {
                $issues[] = 'unsupported-poster-format';
            }
        }

        $ios_src = trim((string) $product->get_meta(self::meta_key('ios_src'), true));

        if ('' !== $ios_src) {
            $ios_url = wp_parse_url($ios_src);
            $ios_extension = strtolower(pathinfo((string) wp_parse_url($ios_src, PHP_URL_PATH), PATHINFO_EXTENSION));

            if (empty($ios_url['scheme']) || empty($ios_url['host'])) {
                $issues[] = 'invalid-ios-url';
            } elseif ('usdz' !== $ios_extension) {
                $issues[] = 'unsupported-ios-format';
            }
        }

        $status = empty($issues) ? 'valid' : 'invalid';

        if (in_array('missing-model', $issues, true)) {
            $status = 'missing';
        } elseif (count($issues) === 1 && false !== strpos($issues[0], 'unsupported')) {
            $status = 'unsupported';
        }

        return array(
            'status' => $status,
            'issues' => $issues,
            'model' => $model,
        );
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
            /*
             * A field that is not part of the submitted form is left alone. An empty field means
             * "inherit" and deletes its meta, but an *absent* field means "this form does not
             * speak about it", and a screen that stopped showing a field must not wipe the value
             * a product already had.
             */
            if (!array_key_exists($short, $input)) {
                continue;
            }

            $value = self::sanitize($field['type'], $input[$short]);
            $meta_key = self::meta_key($short);

            if ('' === $value || false === $value) {
                delete_post_meta($product_id, $meta_key);
                continue;
            }

            update_post_meta($product_id, $meta_key, $value);
        }

        // The Media Library attachment behind the USDZ file follows the same rule as the others.
        if (array_key_exists('ios_src_id', $input)) {
            self::save_attachment_id($product_id, 'ios_src', $input['ios_src_id']);
        } elseif (array_key_exists('ios_src', $input)) {
            self::save_attachment_id($product_id, 'ios_src', self::attachment_id_from_url($input['ios_src']));
        }

        self::$effective = array();
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
            'file_object' => array(self::META_SOURCE, 'url'),
            'file_poster' => array(self::META_POSTER, 'url'),
            'file_alt' => array(self::META_ALT, 'text'),
        );

        foreach ($values as $input_key => $meta) {
            // Absent means "this form does not speak about it", so the stored value is kept.
            if (!array_key_exists($input_key, $input)) {
                continue;
            }

            $meta_key = $meta[0];
            $raw = $input[$input_key];
            $value = 'text' === $meta[1]
                ? sanitize_text_field((string) $raw)
                : esc_url_raw(trim((string) $raw));

            if ('' === $value) {
                delete_post_meta($product_id, $meta_key);
                continue;
            }

            update_post_meta($product_id, $meta_key, $value);
        }

        $stored = array(
            self::META_SOURCE => (string) get_post_meta($product_id, self::META_SOURCE, true),
            self::META_POSTER => (string) get_post_meta($product_id, self::META_POSTER, true),
        );

        /*
         * The attachment id is remembered next to the URL, never instead of it. When the editor
         * sends only a URL, the attachment behind it is looked up here, so a pasted link ends up
         * with the same bookkeeping as one picked in the media library.
         */
        $resources = array(
            'source' => array('file_object', self::META_SOURCE),
            'poster' => array('file_poster', self::META_POSTER),
        );

        foreach ($resources as $short => $resource) {
            list($input_key, $url_key) = $resource;

            if (array_key_exists($input_key . '_id', $input)) {
                self::save_attachment_id($product_id, $short, $input[$input_key . '_id']);
                continue;
            }

            if (array_key_exists($input_key, $input)) {
                self::save_attachment_id($product_id, $short, self::attachment_id_from_url($stored[$url_key]));
            }
        }

        // The resolver memoizes the product, and the values it read are stale now.
        unset(self::$resolved[$product_id]);
        self::$effective = array();
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

    /**
     * Describe the switches that turn a viewer feature on, off or back to the global setting.
     *
     * These are not viewer attributes: `enabled` decides whether the product has a viewer at all,
     * and the other two replace a value of the settings screen for one product. They are kept
     * apart from `fields()` on purpose, because a switch can be off (`no`) while an empty
     * attribute only means "let the library decide".
     *
     * @since 3.1.0
     * @return array<string, array<string, string>> Switch definitions keyed by short name.
     */
    public static function switches()
    {
        return array(
            'enabled' => array(
                'label' => __('Show the 3D and AR viewer', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Turns off the button, the product tab and the shortcode output for this product without deleting its model. Useful while a model is being prepared.', 'ar-model-viewer-for-woocommerce'),
            ),
            'ar_enabled' => array(
                'label' => __('Augmented reality', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Replaces the global augmented reality setting for this product. Leave it on "use the global setting" to follow the settings screen.', 'ar-model-viewer-for-woocommerce'),
            ),
            'auto_rotate' => array(
                'label' => __('Rotate the model automatically', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Replaces the global auto rotation setting for this product. Leave it on "use the global setting" to follow the settings screen.', 'ar-model-viewer-for-woocommerce'),
            ),
        );
    }

    /**
     * Describe the closed lists that a product can override.
     *
     * @since 3.1.0
     * @return array<string, array<string, mixed>> Select definitions keyed by short name.
     */
    public static function selects()
    {
        return array(
            'ar_scale' => array(
                'label' => __('AR scale', 'ar-model-viewer-for-woocommerce'),
                'help' => __('`auto` places the object at the size the file declares, which is what a shopper expects for furniture. `fixed` keeps the size it has on screen, which is what a store wants for a small object that would otherwise be hard to see.', 'ar-model-viewer-for-woocommerce'),
                'options' => array(
                    '' => __('Use the global setting', 'ar-model-viewer-for-woocommerce'),
                    'auto' => __('Automatic, the real size of the model', 'ar-model-viewer-for-woocommerce'),
                    'fixed' => __('Fixed, the size shown on screen', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
            'ar_placement' => array(
                'label' => __('AR placement', 'ar-model-viewer-for-woocommerce'),
                'help' => __('Where the shopper is invited to drop the object. A picture frame belongs on a wall; a chair belongs on the floor.', 'ar-model-viewer-for-woocommerce'),
                'options' => array(
                    '' => __('Use the global setting', 'ar-model-viewer-for-woocommerce'),
                    'floor' => __('Floor', 'ar-model-viewer-for-woocommerce'),
                    'wall' => __('Wall', 'ar-model-viewer-for-woocommerce'),
                ),
            ),
        );
    }

    /**
     * Read one switch of a product, normalised to `yes`, `no` or "inherit".
     *
     * An absent value is the state of every product saved before the switch existed, so it is
     * reported as "inherit" instead of inventing an answer.
     *
     * @since 3.1.0
     * @param WC_Product $product Product or variation.
     * @param string     $short   Short switch name.
     * @return string `yes`, `no` or an empty string.
     */
    public static function switch_value($product, $short)
    {
        $value = (string) $product->get_meta(self::meta_key($short), true);

        return in_array($value, array('yes', 'no'), true) ? $value : '';
    }

    /**
     * Read one closed list value of a product.
     *
     * @since 3.1.0
     * @param WC_Product $product Product or variation.
     * @param string     $short   Short select name.
     * @return string The stored option, or an empty string when it follows the global setting.
     */
    public static function select_value($product, $short)
    {
        $selects = self::selects();

        if (!isset($selects[$short])) {
            return '';
        }

        $value = (string) $product->get_meta(self::meta_key($short), true);

        return '' !== $value && array_key_exists($value, $selects[$short]['options']) ? $value : '';
    }

    /**
     * Whether a product has to render its viewer.
     *
     * @since 3.1.0
     * @param WC_Product $product Product to check.
     * @return bool True when the viewer has to be rendered.
     */
    public static function is_enabled($product)
    {
        return 'no' !== self::switch_value($product, 'enabled');
    }

    /**
     * Save the switches and the closed lists submitted from the product editor.
     *
     * @since 3.1.0
     * @param int   $product_id Product or variation id.
     * @param array $input      Raw submitted values keyed by short name.
     * @return void
     */
    public static function save_switches($product_id, array $input)
    {
        foreach (array_keys(self::switches()) as $short) {
            self::store_choice($product_id, self::meta_key($short), $input, $short, array('yes', 'no'));
        }

        foreach (self::selects() as $short => $field) {
            self::store_choice($product_id, self::meta_key($short), $input, $short, array_keys($field['options']));
        }
    }

    /**
     * Store one value of a closed list, deleting the meta when it follows the global setting.
     *
     * @since 3.1.0
     * @param int    $post_id  Product or variation id.
     * @param string $meta_key Meta key to write.
     * @param array  $input    Raw submitted values.
     * @param string $short    Short field name inside the input.
     * @param array  $allowed  Accepted values.
     * @return void
     */
    private static function store_choice($post_id, $meta_key, array $input, $short, array $allowed)
    {
        // A choice the form never printed is not a choice the store removed.
        if (!array_key_exists($short, $input)) {
            return;
        }

        $raw = is_scalar($input[$short]) ? sanitize_text_field(wp_unslash($input[$short])) : '';

        if (!in_array($raw, $allowed, true) || '' === $raw) {
            delete_post_meta($post_id, $meta_key);
            return;
        }

        update_post_meta($post_id, $meta_key, $raw);
    }

    /**
     * Return the raw values that attach a model to a variation.
     *
     * These are what the variation editor prints, not the values after the fallback: an empty
     * field is how the store says "use the model of the parent product".
     *
     * @since 3.1.0
     * @param WC_Product_Variation $variation Variation object.
     * @return array<string, mixed> URLs and attachment ids.
     */
    public static function variation_files($variation)
    {
        return array(
            'custom' => self::has_custom_configuration($variation),
            'file_object' => (string) $variation->get_meta(self::META_VARIATION_SOURCE, true),
            'file_poster' => (string) $variation->get_meta(self::META_VARIATION_POSTER, true),
            'ios_src' => (string) $variation->get_meta(self::META_VARIATION_IOS_SRC, true),
            'file_object_id' => (int) $variation->get_meta(self::META_ATTACHMENT . 'variation_source', true),
            'file_poster_id' => (int) $variation->get_meta(self::META_ATTACHMENT . 'variation_poster', true),
            'ios_src_id' => (int) $variation->get_meta(self::META_ATTACHMENT . 'variation_ios_src', true),
        );
    }

    /**
     * Whether a variation carries its own configuration.
     *
     * @since 3.1.0
     * @param WC_Product_Variation $variation Variation object.
     * @return bool True when the variation overrides the parent.
     */
    public static function has_custom_configuration($variation)
    {
        return 'yes' === $variation->get_meta(self::META_VARIATION_CUSTOM, true);
    }

    /**
     * Save the model resources of a variation.
     *
     * The switch and the three files are saved together because they are one decision: turning
     * the switch off removes the files, so a variation can never keep an orphan model that would
     * come back the day somebody ticks the box again by accident.
     *
     * @since 3.1.0
     * @param int   $variation_id Variation id.
     * @param array $input        Raw submitted values.
     * @return void
     */
    public static function save_variation($variation_id, array $input)
    {
        $custom = isset($input['custom']) && in_array($input['custom'], array('yes', '1', 1, true), true);

        if ($custom) {
            update_post_meta($variation_id, self::META_VARIATION_CUSTOM, 'yes');
        } else {
            delete_post_meta($variation_id, self::META_VARIATION_CUSTOM);
        }

        if (!$custom) {
            delete_post_meta($variation_id, self::META_VARIATION_SOURCE);
            delete_post_meta($variation_id, self::META_VARIATION_POSTER);
            delete_post_meta($variation_id, self::META_VARIATION_IOS_SRC);
            delete_post_meta($variation_id, self::META_ATTACHMENT . 'variation_source');
            delete_post_meta($variation_id, self::META_ATTACHMENT . 'variation_poster');
            delete_post_meta($variation_id, self::META_ATTACHMENT . 'variation_ios_src');

            self::$effective = array();

            return;
        }

        $values = array(
            'file_object' => self::META_VARIATION_SOURCE,
            'file_poster' => self::META_VARIATION_POSTER,
            'ios_src' => self::META_VARIATION_IOS_SRC,
        );

        foreach ($values as $input_key => $meta_key) {
            // Absent means the form does not speak about it, so the stored value is kept.
            if (!array_key_exists($input_key, $input)) {
                continue;
            }

            $value = esc_url_raw(trim((string) $input[$input_key]));

            if ('' === $value) {
                delete_post_meta($variation_id, $meta_key);
                continue;
            }

            update_post_meta($variation_id, $meta_key, $value);
        }

        foreach (array('variation_source' => 'file_object_id', 'variation_poster' => 'file_poster_id', 'variation_ios_src' => 'ios_src_id') as $short => $input_key) {
            if (!array_key_exists($input_key, $input)) {
                continue;
            }

            self::save_attachment_id($variation_id, $short, $input[$input_key]);
        }

        self::$effective = array();
    }

    /**
     * Merge the shared attributes with the switches of a product.
     *
     * The shared list is what the settings screen produces, and it already contains `auto-rotate`.
     * A product that turns the rotation off therefore has to *remove* the attribute rather than
     * add a second one: a duplicated attribute is won by the first one in the markup, so appending
     * the override would leave the global value in force without any visible sign of it.
     *
     * @since 3.1.0
     * @param array<string, mixed> $shared  Attributes produced by the settings.
     * @param WC_Product           $product Product being rendered.
     * @return array<string, mixed> Attributes with the product overrides applied.
     */
    public static function merge_shared_attributes(array $shared, $product)
    {
        $auto_rotate = self::switch_value($product, 'auto_rotate');

        if ('yes' === $auto_rotate) {
            $shared['auto-rotate'] = true;
        } elseif ('no' === $auto_rotate) {
            unset($shared['auto-rotate'], $shared['auto-rotate-delay'], $shared['rotation-per-second']);
        }

        return $shared;
    }

    /**
     * Return a variation only when it really belongs to the given product.
     *
     * A REST request carries two independent ids, so without this check a shopper could read the
     * model of a variation of another product by pairing the ids by hand.
     *
     * @since 3.1.0
     * @param int $product_id   Parent product id.
     * @param int $variation_id Candidate variation id.
     * @return WC_Product_Variation|null The variation, or null when it does not belong.
     */
    public static function get_variation($product_id, $variation_id)
    {
        $variation_id = absint($variation_id);

        if (!$variation_id) {
            return null;
        }

        $variation = wc_get_product($variation_id);

        if (!$variation instanceof WC_Product_Variation) {
            return null;
        }

        return (int) $variation->get_parent_id() === absint($product_id) ? $variation : null;
    }

    /**
     * Resolve the configuration a viewer has to render, applying the fallback parent to variation.
     *
     * The parent is always the base: a variation only replaces the three resources it owns (the
     * 3D file, the USDZ file and the poster) and inherits everything else, which is what makes a
     * variation with no model of its own render exactly like its parent instead of rendering
     * nothing.
     *
     * @since 3.1.0
     * @param WC_Product           $product   Parent or simple product.
     * @param WC_Product_Variation $variation Optional variation of that product.
     * @return array<string, mixed> Effective resources, their origin, and the viewer options.
     */
    public static function resolve_effective($product, $variation = null)
    {
        if (!$variation instanceof WC_Product_Variation || (int) $variation->get_parent_id() !== (int) $product->get_id()) {
            $variation = null;
        }

        $key = $product->get_id() . ':' . ($variation ? $variation->get_id() : 0);

        if (isset(self::$effective[$key])) {
            return self::$effective[$key];
        }

        $parent = self::resolve($product);

        $effective = array(
            'product_id' => (int) $product->get_id(),
            'variation_id' => $variation ? (int) $variation->get_id() : 0,
            'source' => $parent['source'],
            'source_origin' => 'parent',
            'source_id' => (int) $product->get_meta(self::META_ATTACHMENT . 'source', true),
            'poster' => $parent['poster'],
            'poster_origin' => $parent['poster_source'],
            'poster_id' => (int) $product->get_meta(self::META_ATTACHMENT . 'poster', true),
            'alt' => $parent['alt'],
            'ios_src' => trim((string) $product->get_meta(self::meta_key('ios_src'), true)),
            'ios_src_origin' => 'parent',
            'ios_src_id' => (int) $product->get_meta(self::META_ATTACHMENT . 'ios_src', true),
            'ar_scale' => self::select_value($product, 'ar_scale'),
            'ar_placement' => self::select_value($product, 'ar_placement'),
            'ar_enabled' => self::switch_value($product, 'ar_enabled'),
            'auto_rotate' => self::switch_value($product, 'auto_rotate'),
        );

        if ($variation && self::has_custom_configuration($variation)) {
            $effective = self::apply_variation($effective, $variation);
        }

        self::$effective[$key] = $effective;

        return $effective;
    }

    /**
     * Replace the resources of an effective configuration with the ones of a variation.
     *
     * Every field falls back on its own, so a variation that only carries a poster still uses the
     * model of the parent, which is the behaviour the editor promises when it says that an empty
     * field inherits.
     *
     * @since 3.1.0
     * @param array                $effective Configuration resolved from the parent.
     * @param WC_Product_Variation $variation Variation with its own configuration.
     * @return array<string, mixed> The configuration with the variation values applied.
     */
    private static function apply_variation(array $effective, $variation)
    {
        $resources = array(
            'source' => array(
                'meta' => self::META_VARIATION_SOURCE,
                'attachment' => 'variation_source',
            ),
            'poster' => array(
                'meta' => self::META_VARIATION_POSTER,
                'attachment' => 'variation_poster',
            ),
            'ios_src' => array(
                'meta' => self::META_VARIATION_IOS_SRC,
                'attachment' => 'variation_ios_src',
            ),
        );

        foreach ($resources as $field => $resource) {
            $value = trim((string) $variation->get_meta($resource['meta'], true));

            if ('' === $value) {
                continue;
            }

            $effective[$field] = esc_url_raw($value);
            $effective[$field . '_origin'] = 'variation';
            $effective[$field . '_id'] = (int) $variation->get_meta(self::META_ATTACHMENT . $resource['attachment'], true);
        }

        return $effective;
    }

    /**
     * Remember the Media Library attachment behind a resource.
     *
     * @since 3.1.0
     * @param int    $post_id Product or variation id.
     * @param string $short   Resource name, for example `source` or `variation_poster`.
     * @param mixed  $raw     Submitted attachment id.
     * @return void
     */
    private static function save_attachment_id($post_id, $short, $raw)
    {
        $attachment_id = absint($raw);
        $meta_key = self::META_ATTACHMENT . $short;

        if (!$attachment_id || 'attachment' !== get_post_type($attachment_id)) {
            delete_post_meta($post_id, $meta_key);
            return;
        }

        update_post_meta($post_id, $meta_key, $attachment_id);
    }

    /**
     * Find the Media Library attachment that matches a URL.
     *
     * A store that pastes a URL instead of opening the library still gets an attachment id, which
     * is what lets a later export repair a link that changed. The lookup is only done while
     * saving, never while rendering, because it is a database query.
     *
     * @since 3.1.0
     * @param string $url Public URL of the file.
     * @return int Attachment id, or 0 when it is not in this installation.
     */
    public static function attachment_id_from_url($url)
    {
        $url = esc_url_raw(trim((string) $url));

        if ('' === $url || !function_exists('attachment_url_to_postid')) {
            return 0;
        }

        return absint(attachment_url_to_postid($url));
    }

    /**
     * Forget the memoized configurations.
     *
     * @since 3.1.0
     * @return void
     */
    public static function flush()
    {
        self::$resolved = array();
        self::$effective = array();
    }
}
