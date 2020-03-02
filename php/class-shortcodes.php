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
            $status = $atts['status'];

            // Enqueue child theme scripts
            wp_enqueue_script('scripts-tfk');
            wp_enqueue_style('styles-tfk');

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
                $list_has_data = false;

                // Add editor permission variable
                $edit_option = current_user_can('edit_pages');
                $edit_value = '';

                // Populate group_array by list value
                $group_array = [];
                foreach($field_groups as $group_key => $element) {
                    if (empty($group)) $group_id = $group_key;
                    else $group_id = $group;
                    $group_name = !empty($element[$group_id]) ? $element[$group_id] : "Other";
                    $group_array[$group_name][] = $element;
                }

                // Sort group array alphabetically
                ksort($group_array);

                // Loop through each list group. Ex: states
                $group_index = -1;
                foreach($group_array as $group_key => $group_item) {
                    // Initialize single group start, content, and end
                    $group_toggle = $group_key;
                    $group_has_data = false;
                    $group_output = '';
                    $group_start = '';
                    $group_end = '';
                    $group_index++;
                    
                    // Define group wrapper
                    if (!empty($group)) {
                        $group_start = '<li class="group"><a class="group-title" aria-selected="false" href="#"><span class="icon"></span>' . $group_toggle . '</a><ul class="group-content">';
                        $group_end = '</ul></li>';
                    }

                    // Sort group item array alphabetically
                    usort($group_item, function ($a, $b) {
                        return strcmp($a["title"], $b["title"]);
                    });

                    // Loop through each group item
                    foreach($group_item as $loc_key => $loc) {
                        // Add edit button for admin users
                        if ($edit_option == true) $edit_value = '<a class="edit" href="/wp-admin/post.php?post=' . $loc['post_id'] . '&action=edit"><span class="dashicons dashicons-edit"></span></a>';
                        
                        // Build HTML by type
                        if ($type == 'location' || empty($type)) {
                            if ($status == $loc['status'] || empty($status)) {
                                $coming_soon = ($loc['status'] != 'open') ? '<li class="location-status">' . $loc['status'] . '</li>' : '';
                                $list_has_data = true;
                                $group_has_data = true;
                                $group_output .=
                                    '<li>' .
                                        '<ul class="location">' .
                                            '<li class="location-title"><a href="' . $loc['link'] . '">' . $loc['title'] . '</a></li>' . $coming_soon .
                                            '<li class="location-phone"><a href="tel:' . $loc['phone'] . '">' . $loc['phone'] . '</a></li>' .
                                            '<li class="location-address"><a href="https://www.google.com/maps/place/' . $loc['address'] . '" target="_blank">' . $loc['address'] . '</a></li>' .
                                        '</ul>' .
                                    '</li>';
                            }
                        }
                        else if ($type == 'event') {
                            $events = $loc['events'];

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
                                $date_start = $event['dates']['date_start'];
                                $date_end = $event['dates']['date_end'];
                                $date_status = TFK_Shortcodes::get_date_status($date_start, $date_end);
                
                                // If type attribute is not set, or if type attribute matches custom post type
                                if ($date_status == true) {
                                    $list_has_data = true;
                                    $group_has_data = true;
                                    $group_output .= '
                                        <div class="event">
                                            <div class="left">
                                                <div class="image"><img src="' . $content_image_src . '" alt="' . $content_image_alt . '"></div>
                                            </div>
                                            <div class="right">
                                                <div class="title">' . $content_title . '</div>
                                                <div class="date">' . $date_event . '</div>
                                                <div class="content">' . $content_text . '</div>
                                                <div class="link"><a href="' . $button_link . '" target="' . $button_target . '">' . $button_text . '</a></div>'
                                                . $edit_value . '
                                            </div>
                                        </div>
                                    ';
                                }
                            }
                        }
                    }
                    // Add group to list of groups only if group has posts
                    if ($group_has_data == true) {
                        $output .= $group_start . $group_output . $group_end;
                    }
                    else {
                        if ($list_has_data == false) {
                            $output .= '<p><em>No information available at this time.</em></p>';
                        }
                    }
                }
                $output = '<div class="tfk-list">' .  $output . '</div>';
            }
            else if ($data == 'slider') {
                // Generate array
                $meta_key = 'slide';
                $field_groups = TFK_Shortcodes::get_field_groups_from_posts($meta_key);

                // Enqueue slider libraries
                wp_enqueue_style('slick');
                wp_enqueue_script('slick');

                // Set up slider height with padding
                if (empty($width)) $width = 1920;
                if (empty($height)) $height = 1080;
                $width = floatval(preg_replace("/[^0-9]/", "", $width));
                $height = floatval(preg_replace("/[^0-9]/", "", $height));
                $padding = (($height / $width) * 100) . "%";

                // TODO: Populate slider with global slider page

                // Loop through each slide
                $group_output = '';
                foreach($field_groups as $field_group_key => $field_group) {
                    $slides = $field_groups[$field_group_key]['slider'];
                    foreach($slides as $slide) {
                        $content = $slide['content'];
                        $button = $slide['button'];
                        $date_event = $slide['dates']['date_event'];
                        $date_start = $slide['dates']['date_start'];
                        $date_end = $slide['dates']['date_end'];
                        $date_status = TFK_Shortcodes::get_date_status($date_start, $date_end);

                        // Conditionally add slide content
                        if ($date_status == true) {
                            $image_string = '';
                            $title_string = '';
                            $subtitle_string = '';
                            $text_string = '';
                            $button_string = '';
                            if (!empty($content['image'])) $image_string = $content['image']['url'];
                            if (!empty($content['title'])) $title_string = '<h1>' . $content['title'] . '</h1>';
                            if (!empty($content['subtitle'])) $subtitle_string = '<h2>' . $content['subtitle'] . '</h2>';
                            if (!empty($content['text'])) $text_string = '<p>' . $content['text'] . '</p>';
                            if (!empty($button['link'])) $button_string = '<a href="' . $button['link'] . '" target="' . $button['target'] . '">' . $button['text'] . '</a>';
                            $group_output .= '
                                <div class="tfk-slide" style="background-image: url(' . $image_string . ')">
                                    <div class="item">
                                        <div class="content">
                                            ' . $title_string . '
                                            ' . $subtitle_string . '
                                            ' . $text_string . '
                                            ' . $button_string . '
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                    }
                }

                $output = '<div class="tfk-slider" style="padding-bottom: ' . $padding . ';">' .  $group_output . '</div>';
            }
            else {
                // Get general location information
                if (!empty($data) && !empty($type)) $output = get_field($data, $post_id)[$type];
                else $output = 'shortcode not found';
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
                    $field_groups[$index]['post_id'] = $post->ID;
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
                    $field_groups[$index]['slider'] = get_field('slide', $post->ID);
                }
            }
            return $field_groups;
        }
        public function get_date_status ($date_start, $date_end) {
            // Get date parameter for override functionality
            if (isset($_GET['date'])) { $today = str_replace("-", "/", $_GET['date']); }
            else { $today = date('Y-m-d'); }

            // Format dates
            if (empty($date_start)) $date_start = '01-01-2000';
            if (empty($date_end)) $date_end = '01-01-9999';
            $today = date('Y-m-d', strtotime($today));
            $date_start = date('Y-m-d', strtotime($date_start));
            $date_end = date('Y-m-d', strtotime($date_end));
            return (($date_start <= $today) && ($today < $date_end));
        }
    }
?>