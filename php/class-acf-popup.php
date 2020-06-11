<?php
    class TFK_ACF_Popup {
        public function __construct() {
            if (!is_admin()) {
                // Register scripts and stylesheets
                wp_register_script('cookies', get_stylesheet_directory_uri() . '/js/cookies.js');
                wp_register_script('acf-popup', get_stylesheet_directory_uri() . '/js/acf-popup.js', array( 'jquery' ));
                wp_register_style('acf-popup', get_stylesheet_directory_uri() . '/css/acf-popup.css');

                // Add popup
                add_action('wp_footer', 'TFK_ACF_Popup::add_popup');
            }
        }

        public function add_popup() {
            $acf_meta_key = 'popup';
            $groups = acf_get_field_groups(array('post_id' => get_the_ID()));

            // Use page-level popup if group exists, or home page popup if null
            $post_media = get_field($acf_meta_key, get_the_ID())['content']['image']['media']['url'];
            $post_text = get_field($acf_meta_key, get_the_ID())['content']['text'];
            if (!empty($post_media) || !empty($post_text)) $post__in = array(get_the_ID());
            else $post__in = array(get_option('page_on_front'));

            // Query posts if key (ACF) exists
            $acf_posts = get_posts(array(
                'numberposts'	=> -1,
                'post_type'		=> 'page',
                'meta_key'		=> $acf_meta_key,
                'post__in'      => $post__in,
                'orderby'       => 'post__in'
            ));

            // Generate array for JS object
            $posts = array();
            foreach($acf_posts as $index => $post) {
                $item = array();
                // Add popup to page array if it exists
                $item[$acf_meta_key] = get_field($acf_meta_key, $post->ID);
                if (!empty($item)) array_push($posts, $item);
            }
            
            $popup = $posts[0][$acf_meta_key];
            $media = $popup['content']['image']['media']['url'];
            $link = $popup['content']['image']['link'];
            $text = $popup['content']['text'];
            $date_start = $popup['dates']['date_start'];
            $date_end = $popup['dates']['date_end'];
            $date_status = TFK_Shortcodes::get_date_status($date_start, $date_end);
            $cookie = $popup['dates']['cookie'];
            $link = !empty($link) ? $link : '#';
            $title = !empty($media) ? '<div class="title"><a href="' . $link . '"><img alt="" src="' . $media . '"></a></div>' : '';
            $copy = !empty($text) ? '<div class="content">' . $text . '</div>' : '';
            
            // Add editor permission variable
            $edit_option = current_user_can('edit_pages');
            $edit_value = $edit_option == true ? '<a class="edit" href="/wp-admin/post.php?post=' . $post__in[0] . '&action=edit">Edit <span class="dashicons dashicons-edit"></span></a>' : '';

            if ($date_status == true) {
                // Enqueue scripts and stylesheets
                wp_enqueue_script('cookies');
                wp_enqueue_script('acf-popup');
                wp_enqueue_style('acf-popup');

                // Display popup
                echo '
                    <div data-cookie="popup-' . $post__in[0] . '" data-cookie-sleep="' . $cookie . '"></div>
                    <section class="popup-alert compact">
                        <div class="wrapper">
                            ' . $title . '
                            ' . $copy . '
                            ' . $edit_value . '
                            <a href="#" class="close-popup small" aria-label="close popup">x</a>
                        </div>
                    </section>
                ';
            }
        }
    }
?>