<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Handles the admin-facing functions of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 */

/**
 * Handles interactions with the Meshy API.
 *
 * Defines the plugin name, version, and methods to
 * interact with the Meshy API for text-to-3D tasks.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 * @author     Manuel Ramirez
 */
class MeshyApi
{
    /**
     * The ID of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $plugin_name The unique identifier of the plugin.
     */
    private $plugin_name;

    /**
     * The unique prefix of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $plugin_prefix A string used to uniquely prefix technical functions of this plugin.
     */
    private $plugin_prefix;

    /**
     * The version of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $version The current version of the plugin.
     */
    private $version;

    /**
     * The base URL for the Meshy API.
     *
     * @since 1.0.0
     * @access private
     * @var string $api_base_url The base URL used to make API requests to Meshy.
     */
    private $api_base_url;

    /**
     * The API key for authentication with Meshy API.
     *
     * @since 1.0.0
     * @access private
     * @var string $api_key The API key used for authenticating API requests.
     */
    private $api_key;

    /**
     * WooCommerce logger instance.
     *
     * @since 1.0.0
     * @access private
     * @var object $logger An instance of the WooCommerce logger for logging events.
     */
    private $logger;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $plugin_name   The name of the plugin.
     * @param string $plugin_prefix The unique prefix of the plugin.
     * @param string $version       The version of the plugin.
     */
    public function __construct($plugin_name, $plugin_prefix, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_prefix = $plugin_prefix;
        $this->version = $version;
        $this->api_base_url = 'https://api.meshy.ai';

        // Retrieve the API Key from the CMB2 options
        $this->api_key = CMB2_get_option('ar_model_viewer_for_woocommerce_settings', 'ar_model_viewer_for_woocommerce_api_key_meshy');

        // Initialize the WooCommerce logger
        $this->logger = new Ar_Model_Viewer_For_Woocommerce_Logger($plugin_name, $plugin_prefix, $version);
    }

    /**
     * Creates a Text to 3D Task.
     *
     * Makes a POST request to the `/v2/text-to-3d` endpoint with the provided parameters.
     *
     * @since 1.0.0
     * @param string $prompt          The description of the 3D object to generate.
     * @param string $mode            The mode of the request, 'preview' or 'refine'.
     * @param string $art_style       The artistic style (optional, defaults to 'realistic').
     * @param string $negative_prompt Description of what the model should NOT look like (optional).
     * @param string $preview_task_id The ID of the preview task for refine mode (optional).
     * @param string $texture_richness The desired texture richness for refine mode (optional).
     * @return array|WP_Error         The result of the request or an error in case of failure.
     */
    public function createTextTo3DTaskPreview($prompt, $mode = 'preview', $art_style = 'realistic', $negative_prompt = '', $preview_task_id = '')
    {
        // Check if the API Key is available.
        if (empty($this->api_key)) {
            $this->logger->log_to_woocommerce('API Key not found. Please configure it in the plugin settings.');
            return new WP_Error('api_key_missing', 'Error: API Key is not configured.');
        }

        // Define the endpoint URL for the Meshy API.
        $url = $this->api_base_url . '/v2/text-to-3d';

        // Setup the body for the POST request with required and optional parameters.
        $body = [
            'mode' => $mode, // Specify the mode, 'preview' or 'refine'.
            'prompt' => $prompt, // Text prompt for the 3D model.
            'art_style' => $art_style, // Desired artistic style.
            'negative_prompt' => $negative_prompt, // Description of what the model should not look like.
            'preview_task_id' => $preview_task_id, // Task ID for refine mode (optional).
        ];

        // Make the POST request to the API endpoint.
        $response = $this->make_request('POST', $url, $body);

        // Check if the response is a WP_Error.
        if (is_wp_error($response)) {
            return $response; // Return the error if the request failed.
        }

        // Return the 'result' from the API response or a default message.
        return $response['result'] ?? 'No result available.';
    }

    public function createTextTo3DTaskRefine($preview_task_id, $mode = 'refine')
    {
        // Check if the API Key is available
        if (empty($this->api_key)) {
            $this->logger->log_to_woocommerce('API Key not found. Please configure it in the plugin settings.');
            return new WP_Error('api_key_missing', 'Error: API Key is not configured.');
        }

        // Define the endpoint URL
        $url = $this->api_base_url . '/v2/text-to-3d';

        // Setup the body for the POST request
        $body = [
            'mode' => $mode,
            'preview_task_id' => $preview_task_id,
        ];

        // Make the POST request
        $response = $this->make_request('POST', $url, $body);

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['result'] ?? 'No result available.';
    }

    /**
     * Retrieves a Text to 3D Task.
     *
     * Makes a GET request to the `/v2/text-to-3d/:id` endpoint to retrieve details of a Text to 3D task.
     * If no task ID is provided, it returns a default response.
     *
     * @since 1.0.0
     * @param string|null $task_id The unique ID of the Text to 3D task to retrieve.
     * @return array|WP_Error The details of the Text to 3D task or a default response if no task_id is provided.
     */
    public function retrieveTextTo3DTask($task_id = null)
    {
        // Verifica si la clave API está disponible
        if (empty($this->api_key)) {
            $this->logger->log_to_woocommerce('API Key no encontrada. Por favor, configúrala en los ajustes del plugin.');
            return new WP_Error('api_key_missing', 'Error: API Key no está configurada.');
        }

        // Define la URL del endpoint con o sin el ID de la tarea
        if (empty($task_id)) {
            $this->logger->log_to_woocommerce('ID de tarea no proporcionado. Usando URL base.');
            $url = $this->api_base_url . '/v2/text-to-3d/';
        } else {
            $url = $this->api_base_url . '/v2/text-to-3d/' . $task_id;
        }

        // Realiza la solicitud GET
        $response = $this->make_request('GET', $url);

        if (is_wp_error($response)) {
            $this->logger->log_to_woocommerce('Error en la respuesta: ' . print_r($response, true));
            return $response;
        }

        // Verifica si la respuesta es un array vacío y retorna un array vacío si no hay registros
        if (is_array($response) && empty($response)) {
            $this->logger->log_to_woocommerce('No se encontraron tareas en la respuesta.');
            return []; // Retorna un array vacío en lugar de un mensaje de error
        }

        // Retorna los datos sin el campo 'data' adicional
        $this->logger->log_to_woocommerce('Respuesta obtenida: ' . print_r($response, true));
        return $response;
    }

    /**
     * Makes an HTTP request to the Meshy API.
     *
     * @since 1.0.0
     * @access private
     * @param string $method  The HTTP method ('GET' or 'POST').
     * @param string $url     The full URL for the request.
     * @param array  $body    The request body (for POST requests).
     * @return array|WP_Error The decoded JSON response or WP_Error on failure.
     */
    private function make_request($method, $url, $body = [])
    {
        // Define the headers, including the API Key for authentication
        $headers = [
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type' => 'application/json',
        ];

        $args = [
            'headers' => $headers,
            'timeout' => 60,
        ];

        if ($method === 'POST') {
            $args['body'] = wp_json_encode($body);
        }

        // Make the HTTP request using WordPress HTTP API
        $response = ($method === 'POST') ? wp_remote_post($url, $args) : wp_remote_get($url, $args);

        // Check for errors in the request
        if (is_wp_error($response)) {
            $this->logger->log_to_woocommerce('Error in the request to Meshy API: ' . $response->get_error_message());
            return $response;
        }

        // Get the status code and body from the response
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Decode the JSON response
        $decoded_response = json_decode($body, true);

        // Handle the response based on status code
        if ($status_code >= 200 && $status_code < 300) {
            $this->logger->log_to_woocommerce('Successful request to Meshy API: ' . $status_code);
            return $decoded_response;
        } else {
            // Log and return WP_Error with appropriate message
            $error_message = $decoded_response['message'] ?? 'Unknown error.';
            $this->logger->log_to_woocommerce("Meshy API Error ({$status_code}): {$error_message}");
            return new WP_Error("mesh_api_error_{$status_code}", $error_message, $decoded_response);
        }
    }
}
