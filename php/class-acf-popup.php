<?php
    class TFK_ACF_Popup {
        public function __construct() {
            if (!is_admin()) {
                // Register scripts and stylesheets
                wp_register_script('cookies', get_stylesheet_directory_uri() . '/js/cookies.js');
                wp_register_script('popup', get_stylesheet_directory_uri() . '/js/popup.js', array( 'jquery' ));
                wp_register_style('popup', get_stylesheet_directory_uri() . '/css/popup.css');

                // Add popup
                add_action('wp_footer', 'TFK_ACF_Popup::add_popup');
            }
        }

        public function add_popup() {
            $acf_group_key = 'Popup';
            $acf_meta_key = 'popup';
            $groups = acf_get_field_groups(array('post_id' => get_the_ID()));
            $acf_group_key_exists = strpos(json_encode($groups), $acf_group_key) > 0;
            $acf_meta_key_exists = !empty(get_field($acf_meta_key));

            // Use page-level popup if group exists, or home page popup if null
            if ($acf_group_key_exists == true && $acf_meta_key_exists == true) $post__in = array(get_the_ID());
            else $post__in = array(get_option('page_on_front'));

            // Query posts if $acf_meta_key (ACF) exists
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
                if ($acf_meta_key == 'popup') $item['popup'] = get_field('popup', $post->ID);
                if (!empty($item)) array_push($posts, $item);
            }
            
            $popup = $posts[0]['popup'];
            $media = $popup['content']['image']['media']['url'];
            $link = $popup['content']['image']['link'];
            $text = $popup['content']['text'];
            $date_start = $popup['dates']['date_start'];
            $date_end = $popup['dates']['date_end'];
            $date_status = TFK_Shortcodes::get_date_status($date_start, $date_end);
            $cookie = $popup['dates']['cookie'];
            $copy = !empty($text) ? '<div class="content">' . $text . '</div>' : '';

            if ($date_status == true) {
                // Enqueue scripts and stylesheets
                wp_enqueue_script('cookies');
                wp_enqueue_script('popup');
                wp_enqueue_style('popup');

                // Display popup
                echo '
                    <div data-cookie="popup-' . $post__in[0] . '" data-cookie-sleep="' . $cookie . '"></div>
                    <div class="popup-alert compact">
                        <div class="wrapper">
                            <div class="title">
                                <a href="' . $link . '">
                                    <img alt="" src="' . $media . '">
                                </a>
                            </div>
                            ' . $copy . '
                            <a href="#" class="close-popup small" aria-label="close popup">x</a>
                        </div>
                    </div>
                ';
            }
        }
    }
?>