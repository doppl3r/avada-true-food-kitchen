<?php
    class TFK_Shortcodes {
        
        public function __construct() {
            if (!is_admin()) {
                add_shortcode('tfk', 'TFK_Shortcodes::shortcode');
            }
        }

        public function shortcode($atts, $content = null) {
            $id = $atts['id'];
            $data = $atts['data'];
            $type = $atts['type'];
            $width = $atts['width'];
            $height = $atts['height'];
            $group = $atts['group'];

            // Get post data
            if (isset($id)) $post_id = $id;
            else $post_id = get_the_ID();

            // Output custom fields by data type
            if ($data == 'map') {
                // Build style attribute
                if (!empty($width) || !empty($height)) {
                    if (!empty($width)) $width = 'width: ' . $width . '; ';
                    if (!empty($height)) $height = 'height: ' . $height . '; ';
                    $style = 'style="' . $width . $height . '"';
                }

                // Enqueue resources if map exists
                wp_enqueue_script('leaflet');
                wp_enqueue_style('leaflet');
                wp_enqueue_script('leaflet-tfk');
                wp_enqueue_style('leaflet-tfk');

                // Front-end icon URL
                $dir = get_stylesheet_directory_uri();

                // Get posts if ACF key exits
                $meta_key = 'general';
                $field_groups = TFK_Shortcodes::get_field_groups_from_posts($meta_key);

                // Send array to the front end
                wp_localize_script('leaflet-tfk', 'locations', $field_groups);
                wp_localize_script('leaflet-tfk', 'path', $dir);

                // Render leaflet map HTML
                $output .= '<div id="leaflet-map" ' . $style . '></div>';
            }
            else if ($data == 'list') {
                // Generate array
                $meta_key = 'general';
                if ($type == 'event') $meta_key = 'event';
                $field_groups = TFK_Shortcodes::get_field_groups_from_posts($meta_key);

                // Populate group_array by list value
                $group_array = [];
                foreach($field_groups as $group_key => $element) {
                    if (empty($group)) $group_id = $group_key;
                    else $group_id = $group;
                    $group_name = !empty($element[$group_id]) ? $element[$group_id] : "Other";
                    $group_array[$group_name][] = $element;
                }

                // Loop through each list group. Ex: states
                foreach($group_array as $group_key => $group_item) {
                    // Initialize single group start, content, and end
                    $group_toggle = $group_key;
                    $group_has_data = false;
                    $group_output = '';
                    $group_start = '';
                    $group_end = '';

                    if (!empty($group)) {
                        $group_start = '<li><a class="title" aria-selected="false" href="#">' . $group_toggle . '</a><ul class="container">';
                        $group_end = '</ul></li>';
                    }

                    // Loop through each group item
                    foreach($group_item as $loc_key => $loc) {
                        if ($type == 'location' || empty($type)) {
                            $group_has_data = true;
                            $group_output .=
                                '<li>' .
                                    '<ul class="location">' .
                                        '<li class="location-title"><a href="' . $group_item[$loc_key]['link'] . '">' . $group_item[$loc_key]['title'] . '</a></li>' .
                                        '<li class="location-phone"><a href="tel:' . $group_item[$loc_key]['phone'] . '">' . $group_item[$loc_key]['phone'] . '</a></li>' .
                                        '<li class="location-address"><a href="https://www.google.com/maps/place/' . $group_item[$loc_key]['address'] . '" target="_blank">' . $group_item[$loc_key]['address'] . '</a></li>' .
                                    '</ul>' .
                                '</li>';
                        }
                        else if ($type == 'event') {
                            $events = $group_item[$loc_key]['events'];

                            // Loop through each post
                            foreach($events as $event) {
                                $content_image_src = esc_url($event['content']['image']['url']);
                                $content_image_alt = esc_attr($event['content']['image']['alt']);
                                $content_title = $event['content']['title'];
                                $content_text = $event['content']['text'];
                                $button_link = $event['button']['link'];
                                $button_text = $event['button']['text'];
                                $button_target = $event['button']['target'];
                                $date_event = $event['dates']['date_event'];
                
                                // If type attribute is not set, or if type attribute matches custom post type
                                $group_has_data = true;
                                $group_output .= '
                                    <ul>
                                        <li class="image"><img src="' . $content_image_src . '" alt="' . $content_image_alt . '"></li>
                                        <li class="title">' . $content_title . '</li>
                                        <li class="date">' . $date_event . '</li>
                                        <li class="content">' . $content_text . '</li>
                                        <li class="link"><a href="' . $button_link . '" target="' . $button_target . '">' . $button_text . '</a></li>
                                    </ul>
                                ';
                            }
                        }
                    }
                    // Add group to list of groups only if group has posts
                    if ($group_has_data == true) {
                        $output .= $group_start . $group_output . $group_end;
                    }
                    else $output .= '<p>No events available at this time.</p>';
                }
                $output = '<div class="tfk-list">' .  $output . '</div>';
            }
            else if ($data == 'slides') {
                // Generate array
                $meta_key = 'slide';
                $field_groups = TFK_Shortcodes::get_field_groups_from_posts($meta_key);

                wp_enqueue_style('slick');
                wp_enqueue_script('slick');

                // TODO: Populate slides list with global slides page

                // Loop through each slide
                foreach($field_groups as $field_group_key => $field_group) {
                    $slides = $field_groups[$field_group_key]['slides'];
                    foreach($slides as $slide) {
                        $content = $slide['content'];
                        $button = $slide['button'];
                        $dates = $slide['dates'];
                        echo '<pre>';
                        var_dump($content);
                    }
                }
            }

            // Return output value (default empty)
            return $output;
        }

        public function get_field_groups_from_posts($meta_key) {
            // Limit query to a single post if current page has a $meta_key
            if (!empty(get_field($meta_key))) $post__in = array(get_the_ID());

            // Query posts if $meta_key (ACF) exists
            $posts = get_posts(array(
                'numberposts'	=> -1,
                'post_type'		=> 'page',
                'meta_key'		=> $meta_key,
                'post__in'      => $post__in
            ));

            // Generate array for JS object
            $field_groups = array();
            foreach($posts as $index => $post) {
                // Add general location info to array
                if ($meta_key == 'general' || $meta_key == 'event') {
                    $city = get_field('general', $post->ID)['city'];
                    $state = get_field('general', $post->ID)['state'];
                    $street = get_field('general', $post->ID)['street'];
                    $zip = get_field('general', $post->ID)['zip'];
                    $address = $street . ', ' . $city . ', ' . $state . ' ' . $zip;
                    $latitude = get_field('general', $post->ID)['latitude'];
                    $longitude = get_field('general', $post->ID)['longitude'];
                    $geo = array($latitude, $longitude);
                    $field_groups[$index]['title'] = get_the_title($post->ID);
                    $field_groups[$index]['link'] = get_permalink($post->ID);
                    $field_groups[$index]['status'] = get_field('general', $post->ID)['status'];
                    $field_groups[$index]['phone'] = get_field('general', $post->ID)['phone'];
                    $field_groups[$index]['city'] = $city;
                    $field_groups[$index]['state'] = $state;
                    $field_groups[$index]['street'] = $street;
                    $field_groups[$index]['zip'] = $zip;
                    $field_groups[$index]['address'] = $address;
                    $field_groups[$index]['geo'] = $geo;
                }

                // Add events to location array if defined in shortcode
                if ($meta_key == 'event') {
                    $field_groups[$index]['events'] = get_field('event', $post->ID);
                }

                // Add events to location array if defined in shortcode
                if ($meta_key == 'slide') {
                    $field_groups[$index]['slides'] = get_field('slide', $post->ID);
                }
            }
            return $field_groups;
        }
    }
?>