<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class AR_Model_Viewer_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'ar_model_viewer';
    }

    public function get_title()
    {
        return __('AR Model Viewer', 'my-elementor-widgets');
    }

    public function get_icon()
    {
        return 'eicon-code'; // Puedes elegir un icono de Elementor
    }

    public function get_categories()
    {
        return ['general']; // Puedes crear tu propia categoría si lo deseas
    }

    protected function _register_controls()
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
                'label' => __('Select a Post', 'my-elementor-widgets'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_all_posts(),
                'default' => '',
            ]
        );

        $this->end_controls_section();
    }

    protected function get_all_posts()
    {
        $posts = get_posts(['post_type' => 'ar_model', 'numberposts' => -1]);
        $options = [];
        foreach ($posts as $post) {
            $options[$post->ID] = $post->post_title;
        }
        return $options;
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $post_id = $settings['post_select'];
        $post = get_post($post_id);

        if ($post) {
            //Get the file url for android
            $get_android_file = get_post_meta($post->ID, 'ar_model_viewer_for_woocommerce_file_android', true);
            //Get the fiel url for IOS
            $get_ios_file = get_post_meta($post->ID, 'ar_model_viewer_for_woocommerce_file_ios', true);
            //Get the alt for web accessibility
            $get_alt = get_post_meta($post->ID, 'ar_model_viewer_for_woocommerce_file_alt', true);
            //Get the Poster
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

    protected function _content_template()
    {
        ?>
<# var post_select=settings.post_select; var post=post_select ? { id: post_select, title:
    elementor.helpers.getPostTitle(post_select) } : false; #>
    <div class="ar-model-viewer-widget">
        <h2>{{{ post ? post.title : 'No post selected' }}}</h2>
    </div>
    <?php
}
}