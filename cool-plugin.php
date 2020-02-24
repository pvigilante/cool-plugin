<?php
/*
Plugin Name: Cool Plugin
Plugin URI: https://digitalartschool.com
Description: This is just the coolest plugin on the planet.
Author: Peter Singh-Vigilante
Author URI: https://digitalag.net
Version: 1.0
*/


/*
Cool_Recent_Widget
Add widget for displaying Cool Posts
*/
class Cool_Recent_Widget extends WP_Widget {
    
    function __construct() {
        
        parent::__construct('cool_recent_posts', 'Cool Recent Posts');
        
        add_action('widgets_init', function(){

            register_widget('Cool_Recent_Widget');
        });
    }

    
    public $args = array(
        'before_title' => '<h4 class="widgettitle">',
        'after_title' => '</h4>',
        'before_widget' => '<div class="widget-wrap">',
        'after_widget' => '</div>',
    );
    
    
    public function widget($args, $instance) {
        // Build the Front End Widget

        echo $args['before_widget'];

        if( !empty( $instance['title'] ) ) {
            echo $args['before_title'];
            echo $instance['title'];
            echo $args['after_title'];
        }
        if( !empty( $instance['text'] ) ) {
            echo '<p>';
            echo $instance['text'];
            echo '</p>';
        }

        $cool_query = array(
            'post_type' => 'cool-posts',
            'posts_per_page' => 5
        );
        $cool_posts = new WP_Query($cool_query);

        if($cool_posts->have_posts()):
            echo '<ul class="cool-post-list">';
            while($cool_posts->have_posts()):
                $cool_posts->the_post();

                echo '<li><a href="'.get_the_permalink().'">'.get_the_title().'</a></li>';

            endwhile;
            echo '</ul>';
        endif;


        echo $args['after_widget'];
    }

    public function form($instance) {
        // Form settings for the widget
        $title = ( !empty( $instance['title'] ) ) ? $instance['title'] : '';
        $text = ( !empty( $instance['text'] ) ) ? $instance['text'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title') ?>"><?php _e('Title', 'cool-widget'); ?></label>
            <input 
                class="widefat" 
                type="text" 
                id="<?php echo $this->get_field_id('title') ?>"
                name="<?php echo $this->get_field_name('title') ?>"
                value="<?php echo $title; ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('text') ?>"><?php _e('Text', 'cool-widget'); ?></label>
            <textarea 
                class="widefat" 
                id="<?php echo $this->get_field_id('text') ?>"
                name="<?php echo $this->get_field_name('text') ?>"
                rows="5"
                ><?php echo $text; ?></textarea>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        // Save widget settings

        $instance = array();

        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['text'] = ( !empty( $new_instance['text'] ) ) ? $new_instance['text'] : '';


        return $instance;
    }

}

$cool_recent_widget = new Cool_Recent_Widget();



/*
Regiister Cool Post Type
*/

add_action('init', 'register_cool_post_type');

function register_cool_post_type(){
    
    // Register the Cool Post Type
    register_post_type('cool-posts', array(
        'label' => __('Cool Posts', 'cool-widget'),
        'public' => true,
        'menu_icon' => 'dashicons-welcome-view-site',
        'has_archive' => true,
        'labels' => array(
            'add_new_item' => 'Add Something Cool'
        ),
        'supports' => array(
            'title',
            'excerpt',
            'author',
            'thumbnail'
        )
    ));

    // Register the Cool Post Taxonamy
    register_taxonomy( 'cool-tags', array('cool-posts'), array(
        'label' =>  __('Cool Tags', 'cool-widget'),
        'hierarchical' => false
    ) );
    register_taxonomy( 'cool-cats', array('cool-posts'), array(
        'label' =>  __('Cool Cats', 'cool-widget'),
        'hierarchical' => true,
        'labels' => array(
            'add_new_item' => 'Add Somewhere Cool to Put'
        )
    ) );

}


/*
Add Custom Post Box 
Include a area to add a link
*/
add_action('add_meta_boxes', 'cool_post_add_meta_box');
function cool_post_add_meta_box(){

    add_meta_box(
        'cool_box_id', // Uniqie ID
        'Cool Post Settings', // Title
        'cool_post_box_html', // Callback function to output form
        'cool-posts',    // Post Type
        'normal',
        'high'
    );

}


function cool_post_box_html($post){

    $cool_link = get_post_meta($post->ID, 'cool_link', true);
    $cool_link_title = get_post_meta($post->ID, 'cool_link_title', true);

    ?>
    <p>
        <label for="cool_link">Link</label>
        <input type="url" name="cool_link" class="postbox widefat" value="<?=$cool_link?>">
    </p>
    <p>
        <label for="cool_link_title">Link Title</label>
        <input type="text" name="cool_link_title" class="postbox widefat" value="<?=$cool_link_title?>">
    </p>
    <?php
}


add_action('save_post', 'cool_save_postdata');
function cool_save_postdata( $post_id ){
    // Check is cool_link is set
    if( array_key_exists('cool_link', $_POST) ){
        // Update the post meta with cool_link
        update_post_meta($post_id, 'cool_link', $_POST['cool_link']);
    }
    if( array_key_exists('cool_link_title', $_POST) ){
        // Update the post meta with cool_link
        update_post_meta($post_id, 'cool_link_title', $_POST['cool_link_title']);
    }
}






// Only done at plugin activation
register_activation_hook( __FILE__, function(){

    register_cool_post_type();

    flush_rewrite_rules();
});



