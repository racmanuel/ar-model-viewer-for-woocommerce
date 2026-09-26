<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality in product edit and new page of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the admin-facing stylesheet and JavaScript.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 * @author     Manuel Ramirez Coronel <ra_cm@outlook.com>
 */
class Ar_Model_Viewer_For_Woocommerce_Admin_Product
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The unique prefix of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
     */
    private $plugin_prefix;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The variable to call WooCommerce class logger.
     *
     * @since    1.0.0
     * @access   public
     * @var      object    $logger    The variable to call WooCommerce class logger.
     */
    private $logger;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of this plugin.
     * @param    string    $plugin_prefix  The unique prefix of this plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct($plugin_name, $plugin_prefix, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_prefix = $plugin_prefix;
        $this->version = $version;
        $this->logger = new Ar_Model_Viewer_For_Woocommerce_Logger($plugin_name, $plugin_prefix, $version);
    }

    /**
     * Register the metabox with the options of the viewer for a single product.
     *
     * It is a native metabox on purpose: these fields belong to the viewer, not to the metabox
     * library that still owns the file fields, so they can be maintained, tested and documented
     * without a dependency in between.
     *
     * @since 3.0.0
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_register_3d_options_metabox()
    {
        add_meta_box(
            'ar-model-viewer-for-woocommerce-3d-options',
            __('3D viewer: camera and model', 'ar-model-viewer-for-woocommerce'),
            array($this, 'ar_model_viewer_for_woocommerce_render_3d_options_metabox'),
            'product',
            'normal',
            'default'
        );
    }

    /**
     * Render the metabox of the viewer options.
     *
     * @since 3.0.0
     * @param WP_Post $post Product being edited.
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_render_3d_options_metabox($post)
    {
        $product = wc_get_product($post->ID);

        if (!$product) {
            return;
        }

        include plugin_dir_path(__FILE__) . 'partials/ar-model-viewer-for-woocommerce-admin-display-product-3d-options.php';
    }

    /**
     * Save the viewer options of a product.
     *
     * An emptied field deletes its meta, so a product that inherits every value carries nothing
     * in the database. Values are validated by the model class, which is also what reads them,
     * so what is stored and what is printed can never disagree.
     *
     * @since 3.0.0
     * @param int $post_id Product id.
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_save_3d_options($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['armvw_3d_options_nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['armvw_3d_options_nonce']));

        if (!wp_verify_nonce($nonce, 'armvw_save_3d_options')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $input = array();

        if (isset($_POST['armvw_product']) && is_array($_POST['armvw_product'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every value is validated by type in the model class.
            $input = wp_unslash($_POST['armvw_product']);
        }

        Ar_Model_Viewer_For_Woocommerce_Product_Model::save($post_id, $input);

        $this->logger->log_to_woocommerce(
            sprintf('Viewer options updated for product ID: %d', $post_id),
            'info'
        );
    }

    /**
     * Register the metabox that attaches the model file to a product.
     *
     * This is the native replacement of the first metabox the plugin used to build with the
     * metabox library. It is registered with `add_meta_box()` so the plugin no longer needs a
     * third party dependency to ask for a file, and it keeps the same meta keys, which means no
     * product loses the model it already had.
     *
     * @since 3.0.0
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_register_model_metabox()
    {
        add_meta_box(
            'ar-model-viewer-for-woocommerce-model',
            __('AR Model Viewer for WooCommerce', 'ar-model-viewer-for-woocommerce'),
            array($this, 'ar_model_viewer_for_woocommerce_render_model_metabox'),
            'product',
            'normal',
            'high'
        );
    }

    /**
     * Render the metabox that attaches the model file to a product.
     *
     * @since 3.0.0
     * @param WP_Post $post Product being edited.
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_render_model_metabox($post)
    {
        $product = wc_get_product($post->ID);

        if (!$product) {
            return;
        }

        include plugin_dir_path(__FILE__) . 'partials/ar-model-viewer-for-woocommerce-admin-display-product-model.php';
    }

    /**
     * Save the three values that attach a model to a product.
     *
     * @since 3.0.0
     * @param int $post_id Product id.
     * @return void
     */
    public function ar_model_viewer_for_woocommerce_save_model_files($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['armvw_model_files_nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['armvw_model_files_nonce']));

        if (!wp_verify_nonce($nonce, 'armvw_save_model_files')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $input = array();

        if (isset($_POST['armvw_files']) && is_array($_POST['armvw_files'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every value is sanitized in the model class.
            $input = wp_unslash($_POST['armvw_files']);
        }

        Ar_Model_Viewer_For_Woocommerce_Product_Model::save_files($post_id, $input);
    }

    /**
     * Handles the retrieval of 3D model settings and product details via AJAX request.
     *
     * This function is triggered by an AJAX request to get the 3D model settings and product details.
     * It verifies the validity of the product ID, retrieves global settings, and prepares the data for response.
     *
     * @since    1.0.0
     * @return   void
     */
    public function ar_model_viewer_for_woocommerce_get_model_and_settings()
    {
        // Verify if the AJAX request includes the product ID
        if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
            $this->logger->log_to_woocommerce('Invalid Product ID in AJAX request.', 'error'); // Log error
            wp_send_json_error('Invalid Product ID.');
            wp_die();
        }

        // Retrieve the product ID from the AJAX request
        $product_id = intval($_POST['product_id']); // Convert to integer
        if (!$product_id) {
            $this->logger->log_to_woocommerce('Invalid Product ID after intval conversion.', 'error'); // Log error
            wp_send_json_error('Invalid Product ID.');
            wp_die();
        }

        // Log successful retrieval of settings
        $this->logger->log_to_woocommerce('Global settings retrieved successfully.', 'info'); // Log info

        // Retrieve individual settings
        $settings = $this->get_ar_model_viewer_settings();

        // The product is fetched once and handed to the resolver, which is the same one the
        // shortcode, the product tab and the public endpoint use. This handler used to call
        // three local copies of that logic, and each copy fetched the product again.
        $product = wc_get_product($product_id);

        if (!$product) {
            $this->logger->log_to_woocommerce("Product not found for ID $product_id", 'error');
            wp_send_json_error('Product not found.');
            wp_die();
        }

        $model = Ar_Model_Viewer_For_Woocommerce_Product_Model::resolve($product);
        $product_name = $product->get_name();

        // The preview needs a file to show, so the editor keeps receiving the same message it
        // already handles when the product has none.
        if ('' === trim($model['source'])) {
            $this->logger->log_to_woocommerce("3D model file missing for product: {$product_name} (ID: $product_id)", 'error');
            wp_send_json_error('3D model file missing for product. Try save the product before view a preview.');
            wp_die();
        }

        // Log product retrieval
        $this->logger->log_to_woocommerce("Product retrieved: $product_name (ID: $product_id)", 'info'); // Log info

        // Prepare data for response
        $data = array_merge($settings, [
            'product_name' => $product_name,
            'model_3d_file' => $model['source'],
            'model_alt' => $model['alt'],
            'model_poster' => $model['poster'],
        ]);

        // Send JSON response and log success
        $this->logger->log_to_woocommerce("Successfully prepared 3D model data for product: $product_name (ID: $product_id)", 'info'); // Log success
        wp_send_json_success($data);
        wp_die();
    }

    /**
     * Retrieves AR model viewer settings from the options page.
     *
     * This function gets the AR model viewer settings configured in the options page using CMB2.
     *
     * @since    1.0.0
     * @return   array    An array of AR model viewer settings.
     */
    private function get_ar_model_viewer_settings()
    {
        return Ar_Model_Viewer_For_Woocommerce_Settings::viewer_options();
    }

    /**
     * Handles the creation of a 3D model preview task using the Meshy API.
     *
     * This function is triggered via an AJAX request and interacts with the
     * `createTextTo3DTaskPreview` method to generate a preview of a 3D model.
     *
     * @since 1.0.0
     */
    public function ar_model_viewer_for_woocommerce_createTextTo3DTaskPreview()
    {
        // Check if the request is an AJAX call and if all required parameters are provided.
        if (
            !isset($_POST['prompt']) || // Verify if 'prompt' is present in the request.
            !isset($_POST['art_style']) || // Verify if 'art_style' is present in the request.
            !isset($_POST['topology']) || // Verify if 'topology' is present in the request.
            !isset($_POST['target_polycount']) // Verify if 'target_polycount' is present in the request.
        ) {
            wp_send_json_error('Incomplete data received.', 400); // Respond with an error if required data is missing.
            return; // Exit the function to avoid further processing.
        }

        // Sanitize the received inputs to prevent security vulnerabilities.
        $prompt = sanitize_text_field($_POST['prompt']); // Clean the 'prompt' input.
        $negative_prompt = isset($_POST['negative_prompt']) ? sanitize_text_field($_POST['negative_prompt']) : ''; // Optional field, sanitized if provided.
        $art_style = sanitize_text_field($_POST['art_style']); // Clean the 'art_style' input.
        $topology = sanitize_text_field($_POST['topology']); // Clean the 'topology' input.
        $target_polycount = intval($_POST['target_polycount']); // Convert the 'target_polycount' to an integer.

        // Validate the sanitized input values.
        if (
            empty($prompt) || // Ensure 'prompt' is not empty.
            empty($art_style) || // Ensure 'art_style' is not empty.
            empty($topology) || // Ensure 'topology' is not empty.
            $target_polycount < 10000 || // Check if 'target_polycount' is below the minimum value.
            $target_polycount > 100000// Check if 'target_polycount' exceeds the maximum value.
        ) {
            wp_send_json_error('Invalid data received.', 400); // Respond with an error if the input is invalid.
            return; // Exit the function to avoid unnecessary processing.
        }

        // Instantiate the MeshyApi class to interact with the 3D generation API.
        $meshyAi = new MeshyApi($this->plugin_prefix, $this->plugin_prefix, $this->version);

        try {
            // Call the Meshy API to generate a 3D model based on the input parameters.
            $response = $meshyAi->createTextTo3DTaskPreview(
                $prompt, // The text prompt for the model generation.
                'preview', // The mode is set to 'preview' for this function.
                $art_style, // The desired artistic style for the 3D model.
                $negative_prompt, // An optional negative prompt to refine the model.
                '', // No preview_task_id is required for preview tasks.
                $topology // Use the topology input directly.
            );

            // Check if the API response is valid.
            if ($response && !is_wp_error($response)) {
                wp_send_json_success($response); // Return the API response to the frontend.
            } else {
                wp_send_json_error('Error generating the model.', 500); // Respond with an error if the API did not return a valid response.
            }
        } catch (Exception $e) {
            // Handle any exceptions to avoid server crashes.
            wp_send_json_error('Exception occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Retrieves the current 3D tasks using the Meshy API.
     *
     * This function interacts with the `retrieveTextTo3DTask` method from the MeshyApi class
     * to fetch all ongoing or completed 3D generation tasks. It is triggered via AJAX.
     *
     * @since 1.0.0
     */
    public function ar_model_viewer_for_woocommerce_get_tasks()
    {
        // Instantiate the MeshyApi class to interact with the 3D generation API.
        $meshyAi = new MeshyApi($this->plugin_prefix, $this->plugin_prefix, $this->version);

        try {
            // Call the Meshy API to retrieve all 3D tasks.
            $response = $meshyAi->retrieveTextTo3DTask();

            error_log(print_r($response, true));

            // Check if the API response is valid.
            if ($response && !is_wp_error($response)) {
                wp_send_json_success($response); // Return the API response to the frontend.
            } else {
                // Respond with an error if the API did not return a valid response.
                wp_send_json_error('Error retrieving the 3D tasks.', 500);
            }
        } catch (Exception $e) {
            // Handle any exceptions to avoid server crashes.
            wp_send_json_error('Exception occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Refines a 3D model generation task using the Meshy API.
     *
     * This function is triggered via an AJAX request and uses the `createTextTo3DTaskRefine` method
     * from the MeshyApi class to refine a previously created 3D model task.
     *
     * @since 1.0.0
     */
    public function ar_model_viewer_for_woocommerce_createTextTo3DTaskRefine()
    {
        // Check if the request is an AJAX call and if all required parameters are provided.
        if (!isset($_POST['mode']) || !isset($_POST['preview_task_id'])) {
            wp_send_json_error('Incomplete data received.', 400); // Respond with an error if required data is missing.
            return; // Exit the function.
        }

        // Sanitize the received inputs to prevent security vulnerabilities.
        $mode = sanitize_text_field($_POST['mode']); // Clean the 'mode' input.
        $preview_task_id = sanitize_text_field($_POST['preview_task_id']); // Clean the 'preview_task_id' input.

        // Instantiate the MeshyApi class to interact with the 3D generation API.
        $meshyAi = new MeshyApi($this->plugin_prefix, $this->plugin_prefix, $this->version);

        try {
            // Call the Meshy API to refine the 3D model based on the input parameters.
            $response = $meshyAi->createTextTo3DTaskRefine($preview_task_id, $mode);

            // Check if the API response is valid.
            if ($response && !is_wp_error($response)) {
                // Convert the response array to JSON format.
                $response_json = json_encode($response);

                // Return the API response to the frontend.
                wp_send_json_success($response);
            } else {
                // Respond with an error if the API did not return a valid response.
                wp_send_json_error('Error generating the refined model.', 500);
            }
        } catch (Exception $e) {
            // Handle any exceptions to avoid server crashes.
            wp_send_json_error('Exception occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handles the retrieval of a 3D task, downloads its GLB and thumbnail files,
     * saves them to the WordPress Media Library, updates custom fields in CMB2,
     * and replaces data for an existing WooCommerce product.
     *
     * @since 1.0.0
     */
    public function ar_model_viewer_for_woocommerce_get_task_and_download()
    {
        // Check if the request is an AJAX call and if the required parameters are provided.
        if (!isset($_POST['task_id']) || !isset($_POST['product_id'])) {
            wp_send_json_error('Task ID or Product ID is missing.', 400);
            return;
        }

        // Sanitize the received parameters to prevent security vulnerabilities.
        $task_id = sanitize_text_field($_POST['task_id']);
        $product_id = intval($_POST['product_id']);

        // Validate that the product ID is valid and belongs to a WooCommerce product.
        if (!$product_id) {
            wp_send_json_error('Invalid product ID.', 400);
            return;
        }

        // Instantiate the MeshyApi class to interact with the 3D generation API.
        $meshyAi = new MeshyApi($this->plugin_prefix, $this->plugin_prefix, $this->version);

        try {
            // Call the Meshy API to retrieve the task details based on the provided task ID.
            $response = $meshyAi->retrieveTextTo3DTask($task_id);

            // Check if the API response is valid.
            if ($response && !is_wp_error($response)) {

                error_log(print_r($response, true));

                // Extract necessary details.
                $glb_url = $response['model_urls']['glb'] ?? null;
                $thumbnail_url = $response['thumbnail_url'] ?? null;
                $prompt = $response['prompt'] ?? 'No prompt provided';

                // Validate the URLs.
                if (empty($glb_url) || empty($thumbnail_url)) {
                    wp_send_json_error('GLB or Thumbnail URL is missing.', 400);
                    return;
                }

                // Helper function to download and save files to the Media Library.
                $save_file_to_media_library = function ($url, $mime_type, $file_prefix) {
                    // Fetch the file.
                    $response = wp_remote_get($url, ['timeout' => 120]);

                    // Check if the file download was successful.
                    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                        return new WP_Error('file_download_error', 'Failed to download the file.');
                    }

                    // Get the file content.
                    $file_content = wp_remote_retrieve_body($response);

                    // Generate a unique file name.
                    $file_name = uniqid($file_prefix) . '.' . pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

                    // Get the WordPress uploads directory.
                    $upload_dir = wp_upload_dir();
                    $file_path = trailingslashit($upload_dir['path']) . $file_name;

                    // Save the file to the uploads directory.
                    $file_saved = file_put_contents($file_path, $file_content);
                    if ($file_saved === false) {
                        return new WP_Error('file_save_error', 'Failed to save the file.');
                    }

                    // Insert the file into the WordPress Media Library.
                    $attachment_id = wp_insert_attachment([
                        'guid' => $upload_dir['url'] . '/' . $file_name,
                        'post_mime_type' => $mime_type,
                        'post_title' => basename($file_name, '.' . pathinfo($file_name, PATHINFO_EXTENSION)),
                        'post_content' => '',
                        'post_status' => 'inherit',
                    ], $file_path);

                    if (is_wp_error($attachment_id)) {
                        return new WP_Error('media_insert_error', 'Failed to add the file to the Media Library.');
                    }

                    // Generate and update attachment metadata.
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
                    wp_update_attachment_metadata($attachment_id, $attachment_metadata);

                    return wp_get_attachment_url($attachment_id);
                };

                // Save the GLB file and get its URL.
                $glb_file_url = $save_file_to_media_library($glb_url, 'model/gltf-binary', 'model_');
                if (is_wp_error($glb_file_url)) {
                    wp_send_json_error($glb_file_url->get_error_message(), 500);
                    return;
                }

                // Save the thumbnail file and get its URL.
                $thumbnail_file_url = $save_file_to_media_library($thumbnail_url, 'image/png', 'thumbnail_');
                if (is_wp_error($thumbnail_file_url)) {
                    wp_send_json_error($thumbnail_file_url->get_error_message(), 500);
                    return;
                }

                // Update the custom fields in CMB2 with the local URLs.
                update_post_meta($product_id, 'ar_model_viewer_for_woocommerce_file_object', $glb_file_url);
                update_post_meta($product_id, 'ar_model_viewer_for_woocommerce_file_poster', $thumbnail_file_url);
                update_post_meta($product_id, 'ar_model_viewer_for_woocommerce_file_alt', $prompt);

                // Respond with the updated product information.
                wp_send_json_success([
                    'task_details' => $response,
                    'product_id' => $product_id,
                    'glb_url' => $glb_file_url,
                    'thumbnail_url' => $thumbnail_file_url,
                    'prompt' => $prompt,
                ]);
            } else {
                // Respond with an error if the API did not return a valid response.
                wp_send_json_error('Error retrieving the task details.', 500);
            }
        } catch (Exception $e) {
            // Handle any exceptions to avoid server crashes.
            wp_send_json_error('Exception occurred: ' . $e->getMessage(), 500);
        }
    }
}
