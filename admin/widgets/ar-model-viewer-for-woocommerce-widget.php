<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

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
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the admin-facing stylesheet and JavaScript.
 * As you add hooks and methods, update this description.
 *
 * @package    Ar_Model_Viewer_For_Woocommerce
 * @subpackage Ar_Model_Viewer_For_Woocommerce/admin
 * @author     Manuel Ramirez Coronel <ra_cm@outlook.com>
 */
class Ar_Model_Viewer_For_Woocommerce_Widget extends \Elementor\Widget_Base

{
    /**
     * Get widget name.
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'ar_model_viewer';
    }

    /**
     * Get widget title.
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('AR Model Viewer', 'my-elementor-widgets');
    }

    /**
     * Get widget icon.
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'eicon-code'; // Puedes elegir un icono de Elementor
    }

    /**
     * Get widget categories.
     *
     * @return array Widget categories.
     */
    public function get_categories()
    {
        return ['general']; // Puedes crear tu propia categoría si lo deseas
    }

    /**
     * Register controls for the widget.
     *
     * Adds input fields and other controls to the widget settings.
     *
     * @return void
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'my-elementor-widgets'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'post_select',
            [
                'label' => __('Select a 3D Model', 'my-elementor-widgets'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_all_posts(),
                'default' => '',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Retrieves all posts of the 'ar_model' custom post type.
     *
     * @return array An array of post titles indexed by post ID.
     */

    protected function get_all_posts()
    {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'ar_model_viewer_for_woocommerce_file_android',
                    'value' => '',
                    'compare' => '!=',
                ],
                [
                    'key' => 'ar_model_viewer_for_woocommerce_file_ios',
                    'value' => '',
                    'compare' => '!=',
                ],
            ],
        ];

        $query = new WP_Query($args);
        $options = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $options[get_the_ID()] = get_the_title();
            }
            wp_reset_postdata();
        } else {
            $options = ['' => __('No products found', 'my-elementor-widgets')];
        }

        return $options;
    }

    /**
     * Render the widget output in the front-end.
     *
     * Generates the HTML output for the widget.
     *
     * @return void
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $post_id = $settings['post_select'];
        $post = get_post($post_id);

        if ($post) {
            // Get the file URL for Android
            $get_android_file = get_post_meta($post->ID, 'ar_model_viewer_for_woocommerce_file_android', true);
            // Get the file URL for iOS
            $get_ios_file = get_post_meta($post->ID, 'ar_model_viewer_for_woocommerce_file_ios', true);
            // Get the alt text for web accessibility
            $get_alt = get_post_meta($post->ID, 'ar_model_viewer_for_woocommerce_file_alt', true);
            // Get the poster image URL
            $get_poster = get_post_meta($post->ID, 'ar_model_viewer_for_woocommerce_file_poster', true);

            echo '<div class="elementor-widget-ar-model-viewer">';
            ?>
            <model-viewer id="reveal" loading="lazy" camera-controls auto-rotate
                poster="<?php echo $get_poster; ?>" src="<?php echo $get_android_file; ?>" shadow-intensity="1"
                alt="<?php echo $get_alt; ?>"></model-viewer>
            <?php
echo '</div>';
        } else {
            echo '<p>No post selected.</p>';
        }
    }

    /**
     * Render the widget template in the Elementor editor.
     *
     * Provides a live preview of the widget in the Elementor editor.
     *
     * @return void
     */
    protected function content_template()
    {
        ?>
        <# if (settings.post_select) { #>
    <div class="ar-model-viewer-widget">
        <h2>{{{ settings.post_select }}}</h2>
    </div>
<# } else { #>
    <div class="ar-model-viewer-widget">
        <h2><?php esc_html_e('No post selected', 'my-elementor-widgets');?></h2>
    </div>
<# } #>

        <?php
}
}
?>
