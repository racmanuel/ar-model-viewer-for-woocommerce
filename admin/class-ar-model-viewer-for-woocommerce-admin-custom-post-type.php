<?php
/**
 * The admin-specific functionality of custom post type of the plugin.
 *
 * @link       https://racmanuel.dev
 * @since      1.0.0
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of custom post type of the plugin.
 *
 * This file include functions related with the custom post type for
 * make models with 3D and AR using a shortcode of the plugin.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 * @author     Manuel Ramirez Coronel <ra_cm@outlook.com>
 */
class Ar_Model_Viewer_For_Woocommerce_Admin_Custom_Post_Type
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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name       The name of this plugin.
     * @param      string $plugin_prefix    The unique prefix of this plugin.
     * @param      string $version    The version of this plugin.
     */
    public function __construct($plugin_name, $plugin_prefix, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->plugin_prefix = $plugin_prefix;
        $this->version = $version;

    }

    public function ar_model_viewer_for_woocommerce_add_custom_post_type__premium_only()
    {
        $labels = [
            "name" => esc_html__("Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "singular_name" => esc_html__("Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "menu_name" => esc_html__("Mis Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "all_items" => esc_html__("Todos los Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "add_new" => esc_html__("Añadir nuevo", "ar-model-viewer-for-woocommerce"),
            "add_new_item" => esc_html__("Añadir nuevo Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "edit_item" => esc_html__("Editar Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "new_item" => esc_html__("Nuevo Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "view_item" => esc_html__("Ver Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "view_items" => esc_html__("Ver Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "search_items" => esc_html__("Buscar Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "not_found" => esc_html__("No se ha encontrado Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "not_found_in_trash" => esc_html__("No se han encontrado Modelos 3D en la papelera", "ar-model-viewer-for-woocommerce"),
            "parent" => esc_html__("Modelo 3D superior:", "ar-model-viewer-for-woocommerce"),
            "featured_image" => esc_html__("Imagen destacada para Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "set_featured_image" => esc_html__("Establece una imagen destacada para Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "remove_featured_image" => esc_html__("Eliminar la imagen destacada de Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "use_featured_image" => esc_html__("Usar como imagen destacada de Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "archives" => esc_html__("Archivos de Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "insert_into_item" => esc_html__("Insertar en Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "uploaded_to_this_item" => esc_html__("Subir a Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "filter_items_list" => esc_html__("Filtrar la lista de Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "items_list_navigation" => esc_html__("Navegación de la lista de Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "items_list" => esc_html__("Lista de Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "attributes" => esc_html__("Atributos de Modelos 3D", "ar-model-viewer-for-woocommerce"),
            "name_admin_bar" => esc_html__("Modelo 3D", "ar-model-viewer-for-woocommerce"),
            "item_published" => esc_html__("Modelo 3D publicado", "ar-model-viewer-for-woocommerce"),
            "item_published_privately" => esc_html__("Modelo 3D publicado como privado.", "ar-model-viewer-for-woocommerce"),
            "item_reverted_to_draft" => esc_html__("Modelo 3D devuelto a borrador.", "ar-model-viewer-for-woocommerce"),
            "item_trashed" => esc_html__("Modelo 3D trashed.", "ar-model-viewer-for-woocommerce"),
            "item_scheduled" => esc_html__("Modelo 3D programado", "ar-model-viewer-for-woocommerce"),
            "item_updated" => esc_html__("Modelo 3D actualizado.", "ar-model-viewer-for-woocommerce"),
            "parent_item_colon" => esc_html__("Modelo 3D superior:", "ar-model-viewer-for-woocommerce"),
        ];

        $args = [
            'label' => esc_html__('Modelos 3D', 'ar-model-viewer-for-woocommerce'),
            'labels' => $labels,
            'description' => '',
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_rest' => false,
            'has_archive' => false,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => plugin_dir_url(__FILE__) . 'images/icons8-3d-24.png', // Asegúrate de que la ruta sea correcta
            'show_in_nav_menus' => false,
            'delete_with_user' => false,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'hierarchical' => false,
            'can_export' => true,
            'rewrite' => false,
            'query_var' => true,
            'supports' => ['title'],
        ];
        register_post_type("ar_model", $args);

        $labels_taxonomy = [
            "name" => esc_html__("Categories", "ar-model-viewer-for-woocommerce"),
            "singular_name" => esc_html__("Category", "ar-model-viewer-for-woocommerce"),
            "menu_name" => esc_html__("Categories", "ar-model-viewer-for-woocommerce"),
            "all_items" => esc_html__("Todos los Categories", "ar-model-viewer-for-woocommerce"),
            "edit_item" => esc_html__("Editar Category", "ar-model-viewer-for-woocommerce"),
            "view_item" => esc_html__("Ver Category", "ar-model-viewer-for-woocommerce"),
            "update_item" => esc_html__("Actualizar el nombre de Category", "ar-model-viewer-for-woocommerce"),
            "add_new_item" => esc_html__("Añadir nuevo Category", "ar-model-viewer-for-woocommerce"),
            "new_item_name" => esc_html__("Nombre del nuevo Category", "ar-model-viewer-for-woocommerce"),
            "parent_item" => esc_html__("Category superior", "ar-model-viewer-for-woocommerce"),
            "parent_item_colon" => esc_html__("Category superior:", "ar-model-viewer-for-woocommerce"),
            "search_items" => esc_html__("Buscar Categories", "ar-model-viewer-for-woocommerce"),
            "popular_items" => esc_html__("Categories populares", "ar-model-viewer-for-woocommerce"),
            "separate_items_with_commas" => esc_html__("Separar Categories con comas", "ar-model-viewer-for-woocommerce"),
            "add_or_remove_items" => esc_html__("Añadir o eliminar Categories", "ar-model-viewer-for-woocommerce"),
            "choose_from_most_used" => esc_html__("Escoger entre los Categories más usandos", "ar-model-viewer-for-woocommerce"),
            "not_found" => esc_html__("No se ha encontrado Categories", "ar-model-viewer-for-woocommerce"),
            "no_terms" => esc_html__("Ningún Categories", "ar-model-viewer-for-woocommerce"),
            "items_list_navigation" => esc_html__("Navegación de la lista de Categories", "ar-model-viewer-for-woocommerce"),
            "items_list" => esc_html__("Lista de Categories", "ar-model-viewer-for-woocommerce"),
            "back_to_items" => esc_html__("Volver a Categories", "ar-model-viewer-for-woocommerce"),
            "name_field_description" => esc_html__("El nombre es cómo aparecerá en tu sitio.", "ar-model-viewer-for-woocommerce"),
            "parent_field_description" => esc_html__("Asigna un término superior para crear una jerarquía. El término jazz, por ejemplo, sería el superior de bebop y big band.", "ar-model-viewer-for-woocommerce"),
            "slug_field_description" => esc_html__("El «slug» es la versión apta para URLs del nombre. Suele estar en minúsculas y sólo contiene letras, números y guiones.", "ar-model-viewer-for-woocommerce"),
            "desc_field_description" => esc_html__("La descripción no suele mostrarse por defecto, sin embargo hay algunos temas que puede que la muestren.", "ar-model-viewer-for-woocommerce"),
        ];

        $args = [
            "label" => esc_html__("Categories", "ar-model-viewer-for-woocommerce"),
            "labels" => $labels_taxonomy,
            "public" => false,
            "publicly_queryable" => false,
            "hierarchical" => false,
            "show_ui" => true,
            "show_in_menu" => true,
            "show_in_nav_menus" => false,
            "query_var" => true,
            "rewrite" => ['slug' => 'ar_model_category', 'with_front' => true],
            "show_admin_column" => false,
            "show_in_rest" => false,
            "show_tagcloud" => false,
            "rest_base" => "ar_model_category",
            "rest_controller_class" => "WP_REST_Terms_Controller",
            "rest_namespace" => "wp/v2",
            "show_in_quick_edit" => false,
            "sort" => false,
            "show_in_graphql" => false,
        ];
        register_taxonomy("ar_model_category", ["ar_model"], $args);
    }

    public function ar_model_viewer_for_woocommerce_add_custom_columns_in_custom_post_type__premium_only($columns)
    {
        unset($columns['title']); // Eliminar la columna de título por defecto
        if (!wp_is_mobile()) {
            // Definir las nuevas columnas y su orden
            $new_columns = array(
                'cb' => $columns['cb'], // checkbox para selección masiva
                'title_for_model' => __('<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-text-24.png' . '" width="14"> Title', 'ar-model-viewer-for-woocommerce'),
                'file_for_android' => __('<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-android-24.png' . '" width="14"> File for Android', 'ar-model-viewer-for-woocommerce'),
                'file_for_ios' => __('<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-apple-logo-24.png' . '" width="14"> File for IOS', 'ar-model-viewer-for-woocommerce'),
                'poster' => __('Poster <img src="' . plugin_dir_url(__FILE__) . 'images/icons8-img-24.png' . '" width="14">', 'ar-model-viewer-for-woocommerce'),
                'alt' => __('<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-web-accessibility-24.png' . '" width="14"> Alt', 'ar-model-viewer-for-woocommerce'),
                'category' => __('<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-category-24.png' . '" width="14"> Category', 'ar-model-viewer-for-woocommerce'),
                'shortcode' => __('<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-code-24.png' . '" width="14"> Shortcode', 'ar-model-viewer-for-woocommerce'),
                'actions' => __('<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-button-24.png' . '" width="14"> Actions', 'ar-model-viewer-for-woocommerce'),
            );
        } else {
            // Definir las nuevas columnas y su orden
            $new_columns = array(
                'cb' => $columns['cb'], // checkbox para selección masiva
                'title_for_model' => __('<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-text-24.png' . '" width="14"> Title', 'ar-model-viewer-for-woocommerce'),
                'actions' => __('<img src="' . plugin_dir_url(__FILE__) . 'images/icons8-button-24.png' . '" width="14"> Actions', 'ar-model-viewer-for-woocommerce'),
            );
        }

        return $new_columns;
    }

    public function ar_model_viewer_for_woocommerce_add_content_to_custom_columns_in_custom_post_type__premium_only($column, $post_id)
    {
        if (!wp_is_mobile()) {
            switch ($column) {
                case 'title_for_model':
                    $post = get_post($post_id);
                    echo '<p><strong>' . esc_html($post->post_title) . '</strong></p>';
                    break;

                case 'file_for_android':
                    $file_android = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_android', true);
                    if ($file_android) {
                        echo '<a href="' . esc_url($file_android) . '" target="_blank" class="button"><img src=" ' . plugin_dir_url(__FILE__) . 'images/icons8-download-24.png' . ' " width="12"> ' . __('Download', 'ar-model-viewer-for-woocommerce') . '</a>';
                    } else {
                        echo __('No file', 'ar-model-viewer-for-woocommerce');
                    }
                    break;

                case 'file_for_ios':
                    $file_ios = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_ios', true);
                    if ($file_ios) {
                        echo '<a href="' . esc_url($file_ios) . '" target="_blank" class="button"><img src=" ' . plugin_dir_url(__FILE__) . 'images/icons8-download-24.png' . ' " width="12"> ' . __('Download', 'ar-model-viewer-for-woocommerce') . '</a>';
                    } else {
                        echo __('No file', 'ar-model-viewer-for-woocommerce');
                    }
                    break;

                case 'poster':
                    $poster_url = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_poster', true);
                    if ($poster_url) {
                        echo '
                    <a data-src="' . esc_url($poster_url) . '" data-fancybox>
                        <img src="' . esc_url($poster_url) . '" alt="" width="80" height="80" >
                    </a>';
                    } else {
                        echo __('No poster', 'ar-model-viewer-for-woocommerce');
                    }
                    break;

                case 'alt':
                    $alt_text = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_alt', true);
                    echo esc_html($alt_text);
                    break;

                case 'category':
                    // Obtener las taxonomías del post
                    $taxonomies = get_object_taxonomies('ar_model'); // Cambiar 'product' si es necesario
                    foreach ($taxonomies as $taxonomy) {
                        $terms = get_the_terms($post_id, $taxonomy);
                        if ($terms && !is_wp_error($terms)) {
                            $term_names = array();
                            foreach ($terms as $term) {
                                $term_names[] = $term->name;
                            }
                            echo esc_html(implode(', ', $term_names));
                        }
                    }
                    break;

                case 'shortcode':
                    $shortcode = '[ar-model-viewer id="' . $post_id . '"]';
                    echo '<input type="text" readonly value="' . esc_attr($shortcode) . '" style="width:100%;" />';
                    break;

                case 'actions':
                    echo '<a class="view-ar-model button button-primary" data-id="' . $post_id . '"><img src=" ' . plugin_dir_url(__FILE__) . 'images/icons8-view-24.png' . ' " width="12"> ' . __('Preview', 'ar-model-viewer-for-woocommerce') . '</a>';
                    echo ' | ';
                    echo '<a href="' . get_edit_post_link($post_id) . '" class="button button-primary"><img src=" ' . plugin_dir_url(__FILE__) . 'images/icons8-edit-24.png' . ' " width="12"> ' . __('Edit', 'ar-model-viewer-for-woocommerce') . '</a>';
                    echo ' | ';
                    echo '<a href="' . get_delete_post_link($post_id) . '" class="button button-primary" onclick="return confirm(\'Are you sure you want to delete this item?\');"><img src=" ' . plugin_dir_url(__FILE__) . 'images/icons8-delete-24.png' . ' " width="12"> ' . __('Delete', 'ar-model-viewer-for-woocommerce') . '</a>';
                    break;
            }
        } else {
            switch ($column) {
                case 'title_for_model':
                    $post = get_post($post_id);
                    echo '<p><strong>' . esc_html($post->post_title) . '</strong></p>';
                    break;
                case 'actions':
                    echo '<a class="view-ar-model button button-primary" data-id="' . $post_id . '"><img src=" ' . plugin_dir_url(__FILE__) . 'images/icons8-view-24.png' . ' " width="12"> ' . __('Preview', 'ar-model-viewer-for-woocommerce') . '</a>';
                    echo ' | ';
                    echo '<a href="' . get_edit_post_link($post_id) . '" class="button button-primary"><img src=" ' . plugin_dir_url(__FILE__) . 'images/icons8-edit-24.png' . ' " width="12"> ' . __('Edit', 'ar-model-viewer-for-woocommerce') . '</a>';
                    echo ' | ';
                    echo '<a href="' . get_delete_post_link($post_id) . '" class="button button-primary" onclick="return confirm(\'Are you sure you want to delete this item?\');"><img src=" ' . plugin_dir_url(__FILE__) . 'images/icons8-delete-24.png' . ' " width="12"> ' . __('Delete', 'ar-model-viewer-for-woocommerce') . '</a>';
                    break;
            }
        }
    }

    public function ar_model_viewer_for_woocommerce_get_model_data__premium_only()
    {
        // Verificar el nonce
        check_ajax_referer('ar-model-viewer-ajax-nonce', 'nonce');

        // Ahora puedes manejar la solicitud AJAX de forma segura
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        // Realiza las operaciones necesarias con $post_id

        //File url for android
        $android_file = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_android', true);
        //File url for IOS
        $ios_file = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_ios', true);
        //Text alt for web accessibility
        $alt = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_alt', true);
        //File for Poster
        $poster = get_post_meta($post_id, 'ar_model_viewer_for_woocommerce_file_poster', true);

        // Devuelve una respuesta
        wp_send_json_success(
            array(
                'message' => 'Operación exitosa',
                'post_id' => $post_id,
                'android_file' => $android_file,
                'ios_file' => $ios_file,
                'alt' => $alt,
                'poster' => $poster,
            ));
    }

}
