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
    const SCHEMA_VERSION = '2';
    const SCHEMA_OPTION = 'ar_model_viewer_for_woocommerce_analytics_schema_version';
    const REST_NAMESPACE = 'ar-model-viewer/v1';
    const REST_ROUTE = '/events';
    const ANALYTICS_ROUTE = '/analytics';

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
        'direct_link_open',
        'qr_open',
        'direct_ar_attempt',
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
            `variation_id` bigint(20) unsigned NOT NULL DEFAULT 0,
            `event` varchar(32) NOT NULL,
            `mode` varchar(32) NOT NULL DEFAULT '',
            `error_code` varchar(64) NOT NULL DEFAULT '',
            `duration_bucket` varchar(16) NOT NULL DEFAULT '',
            `event_count` bigint(20) unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY  (`day`, `product_id`, `variation_id`, `event`, `mode`, `error_code`, `duration_bucket`),
            KEY product_day (`product_id`, `day`),
            KEY variation_day (`variation_id`, `day`),
            KEY event_day (`event`, `day`)
        ) {$charset_collate};";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- dbDelta requires the complete schema statement.
        dbDelta($sql);

        if ($table !== $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)))) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- The schema statement is built from fixed plugin SQL and a trusted table name.
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
    * @param int    $variation_id    Variation identifier or 0.
     * @return bool
     */
    public static function record($event, $product_id = 0, $mode = '', $error_code = '', $duration_bucket = '', $variation_id = 0)
    {
        global $wpdb;

        $event = sanitize_key($event);
        $mode = sanitize_key($mode);
        $error_code = sanitize_key($error_code);
        $duration_bucket = sanitize_key($duration_bucket);
        $product_id = absint($product_id);
        $variation_id = absint($variation_id);

        if ('' === $event || strlen($event) > 32 || strlen($mode) > 32 || strlen($error_code) > 64 || strlen($duration_bucket) > 16) {
            return false;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The table name is generated from the WordPress prefix and the fixed plugin suffix.
        $result = $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The table name is generated from the WordPress prefix and the fixed plugin suffix.
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The table name is generated from the WordPress prefix and the fixed plugin suffix.
                "INSERT INTO " . self::table_name() . " (`day`, `product_id`, `variation_id`, `event`, `mode`, `error_code`, `duration_bucket`, `event_count`)
                VALUES (%s, %d, %d, %s, %s, %s, %s, 1)
                ON DUPLICATE KEY UPDATE event_count = event_count + 1",
                current_time('Y-m-d'),
                $product_id,
                $variation_id,
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
                    'variation_id' => array(
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

        register_rest_route(
            self::REST_NAMESPACE,
            self::ANALYTICS_ROUTE,
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(__CLASS__, 'read_rest_analytics'),
                'permission_callback' => array(__CLASS__, 'can_read_analytics'),
                'args' => array(
                    'from' => array('sanitize_callback' => 'sanitize_text_field'),
                    'to' => array('sanitize_callback' => 'sanitize_text_field'),
                    'product_id' => array('sanitize_callback' => 'absint'),
                    'variation_id' => array('sanitize_callback' => 'absint'),
                    'event' => array('sanitize_callback' => 'sanitize_key'),
                    'mode' => array('sanitize_callback' => 'sanitize_key'),
                    'page' => array('default' => 1, 'sanitize_callback' => 'absint'),
                    'per_page' => array('default' => 50, 'sanitize_callback' => 'absint'),
                    'group_by' => array('default' => 'event', 'sanitize_callback' => 'sanitize_key'),
                ),
            )
        );
    }

    /**
     * Check the capability used by the plugin settings screen.
     *
     * @return bool
     */
    public static function can_read_analytics()
    {
        return current_user_can(Ar_Model_Viewer_For_Woocommerce_Settings::CAPABILITY);
    }

    /**
     * Return filtered aggregate analytics for an authenticated administrator.
     *
     * @param WP_REST_Request $request REST request.
     * @return array|WP_Error
     */
    public static function read_rest_analytics(WP_REST_Request $request)
    {
        global $wpdb;

        $table = self::table_name();
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));

        if ($table !== $exists) {
            return new WP_Error('armvw_analytics_unavailable', __('Analytics storage is not available.', 'ar-model-viewer-for-woocommerce'), array('status' => 503));
        }

        $from = self::rest_date($request->get_param('from'), gmdate('Y-m-d', time() - (30 * DAY_IN_SECONDS)));
        $to = self::rest_date($request->get_param('to'), current_time('Y-m-d'));

        if ($from > $to) {
            return new WP_Error('armvw_invalid_date_range', __('The analytics date range is invalid.', 'ar-model-viewer-for-woocommerce'), array('status' => 400));
        }

        $allowed_events = self::EVENTS;
        $event = sanitize_key($request->get_param('event'));

        if ('' !== $event && !in_array($event, $allowed_events, true)) {
            return new WP_Error('armvw_invalid_event', __('The analytics event is invalid.', 'ar-model-viewer-for-woocommerce'), array('status' => 400));
        }

        $allowed_groups = array('day', 'event', 'product', 'variation', 'mode');
        $group_by = sanitize_key($request->get_param('group_by'));

        if (!in_array($group_by, $allowed_groups, true)) {
            return new WP_Error('armvw_invalid_group', __('The analytics grouping is invalid.', 'ar-model-viewer-for-woocommerce'), array('status' => 400));
        }

        $page = max(1, absint($request->get_param('page')));
        $per_page = min(200, max(1, absint($request->get_param('per_page'))));
        $where = array('`day` BETWEEN %s AND %s');
        $values = array($from, $to);

        $product_id = absint($request->get_param('product_id'));
        $variation_id = absint($request->get_param('variation_id'));

        if ($product_id) {
            $where[] = '`product_id` = %d';
            $values[] = $product_id;
        }

        if ($variation_id) {
            $where[] = '`variation_id` = %d';
            $values[] = $variation_id;
        }

        if ('' !== $event) {
            $where[] = '`event` = %s';
            $values[] = $event;
        }

        $mode = sanitize_key($request->get_param('mode'));

        if ('' !== $mode) {
            $where[] = '`mode` = %s';
            $values[] = $mode;
        }

        $where_sql = implode(' AND ', $where);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table and WHERE fragments are generated from fixed identifiers and placeholder-backed values.
        $summary = $wpdb->get_row($wpdb->prepare("SELECT COALESCE(SUM(`event_count`), 0) AS total, COUNT(DISTINCT `product_id`) AS products, COUNT(DISTINCT `variation_id`) AS variations FROM {$table} WHERE {$where_sql}", $values), ARRAY_A);
        $group_column = array(
            'day' => '`day`',
            'event' => '`event`',
            'product' => '`product_id`',
            'variation' => '`variation_id`',
            'mode' => '`mode`',
        )[$group_by];
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Grouping column, table and WHERE fragments come from fixed allowlists.
        $count = absint($wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT {$group_column}) FROM {$table} WHERE {$where_sql}", $values)));
        $offset = ($page - 1) * $per_page;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Grouping column, table and WHERE fragments come from fixed allowlists; limits and offsets use placeholders.
        $rows = $wpdb->get_results($wpdb->prepare("SELECT {$group_column} AS `group_value`, SUM(`event_count`) AS `event_count` FROM {$table} WHERE {$where_sql} GROUP BY {$group_column} ORDER BY `event_count` DESC LIMIT %d OFFSET %d", array_merge($values, array($per_page, $offset))), ARRAY_A);

        foreach ($rows as &$row) {
            $row['event_count'] = absint($row['event_count']);

            if (in_array($group_by, array('product', 'variation'), true)) {
                $row['group_value'] = absint($row['group_value']);
            } else {
                $row['group_value'] = (string) $row['group_value'];
            }
        }
        unset($row);

        return array(
            'summary' => array(
                'total' => absint($summary['total']),
                'products' => absint($summary['products']),
                'variations' => absint($summary['variations']),
            ),
            'range' => array('from' => $from, 'to' => $to),
            'filters' => array(
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'event' => $event,
                'mode' => $mode,
                'group_by' => $group_by,
            ),
            'rows' => $rows,
            'pagination' => array(
                'page' => $page,
                'per_page' => $per_page,
                'total' => $count,
                'pages' => $count ? (int) ceil($count / $per_page) : 0,
            ),
        );
    }

    /**
     * Normalize an optional REST date to an ISO date.
     *
     * @param mixed  $value   Candidate date.
     * @param string $fallback Default date.
     * @return string
     */
    private static function rest_date($value, $fallback)
    {
        $value = is_scalar($value) ? sanitize_text_field((string) $value) : '';

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : $fallback;
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
        $variation_id = absint($request->get_param('variation_id'));

        if (!in_array($event, self::EVENTS, true)) {
            return new WP_REST_Response(array('recorded' => false), 400);
        }

        if ($product_id && 'product' !== get_post_type($product_id)) {
            $product_id = 0;
        }

        if ($variation_id && 'product_variation' !== get_post_type($variation_id)) {
            $variation_id = 0;
        }

        if ($variation_id && $product_id && absint(wp_get_post_parent_id($variation_id)) !== $product_id) {
            $variation_id = 0;
        }

        self::$request_count++;
        self::record(
            $event,
            $product_id,
            sanitize_key($request->get_param('mode')),
            sanitize_key($request->get_param('error_code')),
            sanitize_key($request->get_param('duration_bucket')),
            $variation_id
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

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- The table name is generated from the WordPress prefix and fixed plugin suffix.
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- The table name is generated from the WordPress prefix and fixed plugin suffix; the date is a placeholder.
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