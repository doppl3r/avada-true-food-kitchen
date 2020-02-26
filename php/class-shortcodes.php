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
                $page_array = TFK_Shortcodes::get_pages($atts);

                // Send array to the front end
                wp_localize_script('leaflet-tfk', 'locations', $page_array);
                wp_localize_script('leaflet-tfk', 'path', $dir);

                // Render leaflet map HTML
                $output .= '<div id="leaflet-map" ' . $style . '></div>';
            }
            else if ($data == 'list') {
                // Generate array using local function 'get_location'
                $page_array = TFK_Shortcodes::get_pages($atts);

                // Populate group_array by list value
                $group_array = [];
                foreach($page_array as $group_key => $element) {
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
                // Generate array using local function 'get_location'
                $page_array = TFK_Shortcodes::get_pages($atts);
            }

            // Return output value (default empty)
            return $output;
        }

        public function get_pages($atts) {
            // Query a single location result if the 'general' group field is present
            if (!empty(get_field('general'))) $post__in = array(get_the_ID());
            $page_query = get_posts(array(
                'numberposts'	=> -1,
                'post_type'		=> 'page',
                'meta_key'		=> 'general',
                'post__in'      => $post__in
            ));

            // Generate array for JS object
            $page_array = array();
            foreach($page_query as $index => $page) {
                // Add general location info to array
                $city = get_field('general', $page->ID)['city'];
                $state = get_field('general', $page->ID)['state'];
                $street = get_field('general', $page->ID)['street'];
                $zip = get_field('general', $page->ID)['zip'];
                $address = $street . ', ' . $city . ', ' . $state . ' ' . $zip;
                $latitude = get_field('general', $page->ID)['latitude'];
                $longitude = get_field('general', $page->ID)['longitude'];
                $geo = array($latitude, $longitude);
                $page_array[$index]['title'] = get_the_title($page->ID);
                $page_array[$index]['link'] = get_permalink($page->ID);
                $page_array[$index]['status'] = get_field('general', $page->ID)['status'];
                $page_array[$index]['phone'] = get_field('general', $page->ID)['phone'];
                $page_array[$index]['city'] = $city;
                $page_array[$index]['state'] = $state;
                $page_array[$index]['street'] = $street;
                $page_array[$index]['zip'] = $zip;
                $page_array[$index]['address'] = $address;
                $page_array[$index]['geo'] = $geo;

                // Add events to location array if defined in shortcode
                if ($atts['type'] == 'event' || $atts['type'] == 'events') {
                    $page_array[$index]['events'] = get_field('event', $page->ID);
                }
            }
            return $page_array;
        }

        public function get_slides($atts) {
            // Query a single location result if the 'slides' group field is present
            if (!empty(get_field('slides'))) $post__in = array(get_the_ID());
            $page_query = get_posts(array(
                'numberposts'	=> -1,
                'post_type'		=> 'page',
                'meta_key'		=> 'slides',
                'post__in'      => $post__in
            ));

            // Generate array for JS object
            $page_array = array();
            foreach($page_query as $index => $page) {
                // Add general location info to array
                $page_array[$index]['slides'] = get_field('slide', $page->ID);
            }
            return $page_array;
        }
    }
?>