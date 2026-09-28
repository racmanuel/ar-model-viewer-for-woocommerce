<?php
/**
 * First-party, privacy-conscious analytics storage for the viewer.
 *
 * @package Ar_Model_Viewer_For_Woocommerce
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Stores anonymous daily counters for viewer and AR interactions.
 */
class Ar_Model_Viewer_For_Woocommerce_Analytics
{
    const TABLE_SUFFIX = 'armvw_analytics';
    const SCHEMA_VERSION = '1';
    const SCHEMA_OPTION = 'ar_model_viewer_for_woocommerce_analytics_schema_version';
    const REST_NAMESPACE = 'armvw/v1';
    const REST_ROUTE = '/events';

    /**
     * Events accepted from the public viewer.
     *
     * @var array<int, string>
     */
    const EVENTS = array(
        'viewer_open',
        'viewer_load',
        'viewer_close',
        'ar_attempt',
        'ar_session_started',
        'ar_object_placed',
        'ar_failed',
        'viewer_error',
        'viewer_interaction',
    );

    /**
     * Number of events accepted during the current request.
     *
     * @var int
     */
    private static $request_count = 0;

    /**
     * Return the fully prefixed analytics table name.
     *
     * @return string
     */
    public static function table_name()
    {
        global $wpdb;

        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    /**
     * Create or update the aggregate table.
     *
     * @return void
     */
    public static function create_table()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $table = self::table_name();
        $sql = "CREATE TABLE {$table} (
            `day` date NOT NULL,
            `product_id` bigint(20) unsigned NOT NULL DEFAULT 0,
            `event` varchar(32) NOT NULL,
            `mode` varchar(32) NOT NULL DEFAULT '',
            `error_code` varchar(64) NOT NULL DEFAULT '',
            `duration_bucket` varchar(16) NOT NULL DEFAULT '',
            `event_count` bigint(20) unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY  (`day`, `product_id`, `event`, `mode`, `error_code`, `duration_bucket`),
            KEY product_day (`product_id`, `day`),
            KEY event_day (`event`, `day`)
        ) {$charset_collate};";

        dbDelta($sql);

        if ($table !== $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)))) {
            $wpdb->query($sql);
        }

        if ($table === $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)))) {
            update_option(self::SCHEMA_OPTION, self::SCHEMA_VERSION, false);
        }
    }

    /**
     * Create the table for installations that were already active during an update.
     *
     * @return void
     */
    public static function maybe_upgrade()
    {
        if (self::SCHEMA_VERSION !== get_option(self::SCHEMA_OPTION)) {
            self::create_table();
        }
    }

    /**
     * Increment one anonymous aggregate counter.
     *
     * @param string $event           Normalized event name.
     * @param int    $product_id      Product identifier.
     * @param string $mode            AR mode or empty string.
     * @param string $error_code      Normalized error code or empty string.
     * @param string $duration_bucket Duration bucket or empty string.
     * @return bool
     */
    public static function record($event, $product_id = 0, $mode = '', $error_code = '', $duration_bucket = '')
    {
        global $wpdb;

        $event = sanitize_key($event);
        $mode = sanitize_key($mode);
        $error_code = sanitize_key($error_code);
        $duration_bucket = sanitize_key($duration_bucket);
        $product_id = absint($product_id);

        if ('' === $event || strlen($event) > 32 || strlen($mode) > 32 || strlen($error_code) > 64 || strlen($duration_bucket) > 16) {
            return false;
        }

        $result = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO " . self::table_name() . " (`day`, `product_id`, `event`, `mode`, `error_code`, `duration_bucket`, `event_count`)
                VALUES (%s, %d, %s, %s, %s, %s, 1)
                ON DUPLICATE KEY UPDATE event_count = event_count + 1",
                current_time('Y-m-d'),
                $product_id,
                $event,
                $mode,
                $error_code,
                $duration_bucket
            )
        );

        return false !== $result;
    }

    /**
     * Register the public endpoint used by the viewer.
     *
     * @return void
     */
    public static function register_rest_routes()
    {
        register_rest_route(
            self::REST_NAMESPACE,
            self::REST_ROUTE,
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array(__CLASS__, 'ingest_rest_event'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'event' => array(
                        'required' => true,
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => function ($value) {
                            return in_array(sanitize_key($value), self::EVENTS, true);
                        },
                    ),
                    'product_id' => array(
                        'default' => 0,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ($value) {
                            return absint($value) <= 2147483647;
                        },
                    ),
                    'mode' => array(
                        'default' => '',
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => function ($value) {
                            return strlen(sanitize_key($value)) <= 32;
                        },
                    ),
                    'error_code' => array(
                        'default' => '',
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => function ($value) {
                            return strlen(sanitize_key($value)) <= 64;
                        },
                    ),
                    'duration_bucket' => array(
                        'default' => '',
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => function ($value) {
                            return strlen(sanitize_key($value)) <= 16;
                        },
                    ),
                ),
            )
        );
    }

    /**
     * Store one validated event without retaining a raw request or visitor identifier.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public static function ingest_rest_event(WP_REST_Request $request)
    {
        if (self::$request_count >= 30) {
            return new WP_REST_Response(array('recorded' => false), 429);
        }

        if ('1' !== (string) Ar_Model_Viewer_For_Woocommerce_Settings::get('ar_model_viewer_for_woocommerce_analytics')) {
            return new WP_REST_Response(array('recorded' => false), 202);
        }

        $event = sanitize_key($request->get_param('event'));
        $product_id = absint($request->get_param('product_id'));

        if (!in_array($event, self::EVENTS, true)) {
            return new WP_REST_Response(array('recorded' => false), 400);
        }

        if ($product_id && 'product' !== get_post_type($product_id)) {
            $product_id = 0;
        }

        self::$request_count++;
        self::record(
            $event,
            $product_id,
            sanitize_key($request->get_param('mode')),
            sanitize_key($request->get_param('error_code')),
            sanitize_key($request->get_param('duration_bucket'))
        );

        return new WP_REST_Response(array('recorded' => true), 202);
    }

    /**
     * Delete every stored aggregate.
     *
     * @return bool
     */
    public static function delete_all()
    {
        global $wpdb;

        return false !== $wpdb->query('TRUNCATE TABLE ' . self::table_name());
    }

    public static function summary($days = 30)
    {
        global $wpdb;

        $table = self::table_name();
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));

        if ($table !== $exists) {
            return array();
        }

        $since = gmdate('Y-m-d', time() - (absint($days) * DAY_IN_SECONDS));
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT `event`, SUM(`event_count`) AS total FROM {$table} WHERE `day` >= %s GROUP BY `event`",
                $since
            ),
            ARRAY_A
        );
        $summary = array();

        foreach ($rows as $row) {
            $summary[sanitize_key($row['event'])] = absint($row['total']);
        }

        return $summary;
    }
}