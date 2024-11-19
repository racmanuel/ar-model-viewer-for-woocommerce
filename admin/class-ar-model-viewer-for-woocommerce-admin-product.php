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
     * Define the metabox and field configurations.
     */
    public function ar_model_viewer_for_woocommerce_cmb2_metaboxes()
    {
        /**
         * Initiate the metabox
         */
        $main_metabox = new_cmb2_box(array(
            'id' => 'ar_model_viewer_for_woocommerce_metaboxes',
            'title' => __('AR Model Viewer for WooCommerce', 'cmb2'),
            'object_types' => array('product'), // Post type
            'context' => 'normal',
            'priority' => 'low',
            'show_names' => true, // Show field names on the left
            'cmb_styles' => true, // false to disable the CMB stylesheet
            'closed' => false, // Keep the metabox closed by default
        ));

        // Regular File field - Android .glb
        $main_metabox->add_field(array(
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-3d-94.png' . '" class="icon-in-field"></img> 3D Object File',
            'desc' => 'Upload or enter an URL to 3D object (with .glb  or .glTF extension).',
            'id' => 'ar_model_viewer_for_woocommerce_file_object',
            'type' => 'file',
            // Optional:
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'text' => array(
                'add_upload_file_text' => 'Add URL or File', // Change upload button text. Default: "Add or Upload File"
            ),
            // query_args are passed to wp.media's library query.
            'query_args' => array(
                'type' => 'model/gltf-binary', // Make library only display .glb files.
            ),
            'before_row' => array(__CLASS__, 'ar_model_viewer_for_woocommerce_before_title_row'),
        ));

        //Regular File Field to Poster
        $main_metabox->add_field(array(
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-photo-gallery-94.png' . '" class="icon-in-field"></img> Poster',
            'desc' => 'Upload an image or enter an URL. If the image field (alt) is left empty, the photo of the product is taken. This field displays an image instead of the model, useful for showing the user something before a model is loaded and ready to render.',
            'id' => 'ar_model_viewer_for_woocommerce_file_poster',
            'type' => 'file',
            // Optional:
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'text' => array(
                'add_upload_file_text' => 'Add Image', // Change upload button text. Default: "Add or Upload File"
            ),
            // query_args are passed to wp.media's library query.
            'query_args' => array(
                // Or only allow gif, jpg, or png images
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ),
            ),
            'preview_size' => 'thumbnail', // Image size to use when previewing in the admin.
        ));

        // Regular Text field - alt for models
        $main_metabox->add_field(array(
            'name' => '<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-info-94.png' . '" class="icon-in-field"></img> alt',
            'desc' => 'Insert a text. if the text field is left empty, the name of the product is taken. Configures the model with custom text that will be used to describe the model to viewers who use a screen reader or otherwise depend on additional semantic context to understand what they are viewing.',
            'id' => 'ar_model_viewer_for_woocommerce_file_alt',
            'type' => 'text',
            'after_row' => array(__CLASS__, 'ar_model_viewer_for_woocommerce_after_title_row'),
        ));

        $meshyAi = cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_api_key_meshy');

        if (!empty($meshyAi)) {
            # code...
            $text_to_3d = new_cmb2_box(array(
            'id' => 'ar_model_viewer_for_woocommerce_metabox_text_to_3d',
            'title' => __('Text to 3D Model', 'cmb2'),
            'object_types' => array('product'), // Post type
            'context' => 'normal',
            'priority' => 'low',
            'show_names' => false, // Show field names on the left
            'cmb_styles' => true, // false to disable the CMB stylesheet
            'closed' => false, // Keep the metabox closed by default
        ));

        $text_to_3d->add_field(array(
            'name' => '3D Model with Text',
            'desc' => '',
            'type' => 'title',
            'id' => 'text_to_3d_title',
            'after' => array(__CLASS__, 'ar_model_viewer_for_woocommerce_text_to_3d_content'),
        ));

        $tasks_ai = new_cmb2_box(array(
            'id' => 'ar_model_viewer_for_woocommerce_metabox_tasks',
            'title' => __('Tasks of Meshy AI', 'cmb2'),
            'object_types' => array('product'), // Post type
            'context' => 'normal',
            'priority' => 'low',
            'show_names' => false, // Show field names on the left
            'cmb_styles' => true, // false to disable the CMB stylesheet
            'closed' => false, // Keep the metabox closed by default
        ));

        $tasks_ai->add_field(array(
            'name' => 'Text to 3D Tasks',
            'desc' => '',
            'type' => 'title',
            'id' => 'text_to_3d_tasks_title',
            'after' => '<div id="table-task-3d"></div>',
        ));
        }
        
    }

    public static function ar_model_viewer_for_woocommerce_before_title_row()
    {
        include_once 'partials/ar-model-viewer-for-woocommerce-admin-display-product-header.php';
    }

    public static function ar_model_viewer_for_woocommerce_after_title_row()
    {
        include_once 'partials/ar-model-viewer-for-woocommerce-admin-display-product-footer.php';
    }

    public static function ar_model_viewer_for_woocommerce_text_to_3d_content()
    {
        include_once 'partials/ar-model-viewer-for-woocommerce-admin-display-product-text-to-3d.php';
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

        // Get the product name
        $product_name = get_the_title($product_id);

        // Log product retrieval
        $this->logger->log_to_woocommerce("Product retrieved: $product_name (ID: $product_id)", 'info'); // Log info

        // Retrieve product metadata
        $model_alt = $this->get_model_alt_or_fallback($product_id);
        $model_poster = $this->get_model_poster_or_fallback($product_id);
        $model_3d_file = $this->get_model_3d_file_or_fallback($product_id);

        // Prepare data for response
        $data = array_merge($settings, [
            'product_name' => $product_name,
            'model_3d_file' => $model_3d_file,
            'model_alt' => $model_alt,
            'model_poster' => $model_poster,
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
        return [
            // Determines how the model loads (e.g., "auto" means it starts loading immediately)
            'loading' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_loading', 'auto'),

            // Controls when the model should be revealed, such as after a specific action or automatically
            'reveal' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_reveal', 'auto'),

            // Indicates whether the model viewer should send credentials such as cookies when making network requests
            'with_credentials' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_with_credentials', 'false'),

            // Sets the color of the poster background for the model viewer (default is transparent white)
            'poster_color' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_poster_color', 'rgba(255,255,255,0)'),

            // Determines if the AR feature should be enabled or not (e.g., "active" enables AR functionality)
            'ar' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar', 'active'),

            // Defines the scaling behavior of the model in AR (e.g., "auto" lets the viewer decide the best scale)
            'scale' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_scale', 'auto'),

            // Sets the placement of the model in AR (e.g., "floor" means the model is placed on the floor)
            'placement' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_placement', 'floor'),

            // Indicates whether an XR (Extended Reality) environment is used (e.g., "deactive" means no XR environment)
            'xr_environment' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_xr_environment', 'deactive'),

            // Defines which AR modes are supported, such as "webxr" or "scene-viewer" (these determine how the AR experience is presented)
            'ar_modes' => cmb2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_ar_modes', ['webxr', 'scene-viewer']),
        ];
    }

    /**
     * Retrieves the alt text for the 3D model or falls back to the product name or short description.
     *
     * This function checks if the 3D model alt text is set. If not, it will try to return the product name.
     * If the product name is also not set, it will return the short description.
     * If none of these fields are available, it will log an error using the WooCommerce logger.
     *
     * @since    1.0.0
     * @param    int    $product_id    The product ID.
     * @return   string The alt text for the 3D model, or the product name, or the short description.
     */
    private function get_model_alt_or_fallback($product_id)
    {
        // Retrieve the WooCommerce product object
        $product = wc_get_product($product_id);

        // If the product doesn't exist, log an error and return an empty string
        if (!$product) {
            $this->logger->log_to_woocommerce('Product not found for ID ' . $product_id, 'error'); // Log error
            return '';
        }

        // Get the 3D model alt text using WooCommerce's get_meta function
        $model_alt = $product->get_meta('ar_model_viewer_for_woocommerce_file_alt', true);

        // If the 3D model alt text is not available, fallback to the product name
        if (empty($model_alt)) {
            $product_name = $product->get_name(); // Use WooCommerce's native function to get the product name

            // If the product name is also not available, fallback to the short description
            if (empty($product_name)) {
                $short_description = $product->get_short_description(); // Use WooCommerce's native function to get the short description

                // If the short description is not available, log an error and return an empty string
                if (empty($short_description)) {
                    $this->logger->log_to_woocommerce('No alt, product name, or short description found for product ID ' . $product_id, 'error'); // Log error
                    return ''; // Return empty string as fallback
                }

                // Return the short description if available
                return $short_description;
            }

            // Return the product name if available
            return $product_name;
        }

        // Return the 3D model alt text if it exists
        return $model_alt;
    }

    /**
     * Retrieves the 3D model poster URL or falls back to the product's main image URL.
     *
     * This function checks if the 3D model poster URL is set. If not, it will try to return the product's main image URL.
     * If the product doesn't have a main image, it will log an error using the WooCommerce logger.
     *
     * @since    1.0.0
     * @param    int    $product_id    The product ID.
     * @return   string The URL of the poster image or the product's main image.
     */
    private function get_model_poster_or_fallback($product_id)
    {
        // Retrieve the WooCommerce product object
        $product = wc_get_product($product_id);

        // If the product doesn't exist, log an error and return an empty string
        if (!$product) {
            $this->logger->log_to_woocommerce('Product not found for ID ' . $product_id, 'error');
            return '';
        }

        // Get the 3D model poster URL using WooCommerce's get_meta function
        $model_poster = $product->get_meta('ar_model_viewer_for_woocommerce_file_poster', true);

        // If the 3D model poster URL is empty or null, try to get the product's main image URL
        if (empty($model_poster)) {
            $main_image_id = $product->get_image_id(); // Get the ID of the main image

            // If the product has no main image, log an error and return empty
            if (empty($main_image_id)) {
                $this->logger->log_to_woocommerce('No poster or main image found for product ID ' . $product_id, 'error');
                return ''; // Return empty string as fallback
            }

            // Get the URL of the main image
            $main_image_url = wp_get_attachment_url($main_image_id);

            // Return the main image URL if available
            if ($main_image_url) {
                return $main_image_url;
            } else {
                $this->logger->log_to_woocommerce('Main image URL could not be retrieved for product ID ' . $product_id, 'error');
                return ''; // Return empty if no URL could be retrieved
            }
        }

        // Return the 3D model poster URL if it exists
        return $model_poster;
    }

    /**
     * Retrieves the 3D model file for the product or sets a fallback file from the plugin's includes directory.
     *
     * This function checks if the 3D model file is set for the product. If not, it will use a default 3D model
     * file located in the includes folder of the plugin. It also logs an error if the model is missing.
     *
     * @since    1.0.0
     * @param    int    $product_id    The product ID.
     * @return   string The URL of the 3D model file.
     */
    public function get_model_3d_file_or_fallback($product_id)
    {
        // Retrieve the WooCommerce product object
        $product = wc_get_product($product_id);

        // If the product doesn't exist, log an error and return an empty string
        if (!$product) {
            $this->logger->log_to_woocommerce("Product not found for ID $product_id", 'error');
            return '';
        }

        // Get the 3D model file using WooCommerce's get_meta function
        $model_3d_file = $product->get_meta('ar_model_viewer_for_woocommerce_file_object', true);

        // Check if the 3D model file exists
        if (!$model_3d_file) {
            $this->logger->log_to_woocommerce("3D model file missing for product: {$product->get_name()} (ID: $product_id)", 'error'); // Log error
            wp_send_json_error('3D model file missing for product. Try save the product before view a preview.');
            wp_die();
        }

        // Log success if the 3D model file is found
        $this->logger->log_to_woocommerce("3D model file found for product: {$product->get_name()} (ID: $product_id)", 'info'); // Log info

        // Return the 3D model file URL
        return $model_3d_file;
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
