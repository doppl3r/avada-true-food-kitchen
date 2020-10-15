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
            $filter = $atts['filter'];

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
                $acf_group_key = 'Location';
                $acf_meta_key = 'general';
                $posts = TFK_Shortcodes::acf_get_posts($acf_group_key, $acf_meta_key, $atts);

                // Send array to the front end
                wp_localize_script('leaflet-tfk', 'locations', $posts);
                wp_localize_script('leaflet-tfk', 'path', $dir);

                // Render leaflet map HTML
                $output .= '<div id="leaflet-map" ' . $style . '></div>';
            }
            else if ($data == 'list') {
                // Generate array
                $states = array('AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming');
                $list_has_data = false;
                $acf_group_key = 'Location';
                $acf_meta_key = 'general';

                // Get posts by type
                if ($type == 'event') { $acf_group_key = 'Events'; $acf_meta_key = 'event'; }
                else if ($type == 'catering') { $acf_group_key = 'Catering'; $acf_meta_key = 'document'; }
                $posts = TFK_Shortcodes::acf_get_posts($acf_group_key, $acf_meta_key, $atts);

                // Add editor permission variable
                $edit_option = current_user_can('edit_pages');
                $edit_value = '';

                // Populate group_array by list value
                $group_array = [];
                foreach($posts as $group_key => $element) {
                    if (empty($group)) $group_id = $group_key;
                    else $group_id = $group;
                    $group_name = !empty($element[$group_id]) ? $element[$group_id] : "Other";
                    if (isset($states[$group_name])) $group_name = $states[$group_name]; // Replace state abbreviation key with full name (ex: NV => Nevada)
                    $group_array[$group_name][] = $element;
                }

                // Sort group alphabetically
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
                        $group_start = '<li class="group ' . $group . '"><a class="group-title" aria-selected="false" href="#"><span class="icon"></span>' . $group_toggle . '</a><ul class="group-content">';
                        $group_end = '</ul></li>';
                    }

                    // Sort locations alphabetically by title
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
                                $show_links = strtolower($loc['description']) != 'coming soon'; // Removes 'Coming Soon' links
                                $title = ($show_links == true) ? '<a href="' . $loc['link'] . '">' . $loc['title'] . '</a>' : $loc['title'];
                                $description = !empty($loc['description']) ? '<li class="location-description">' . $loc['description'] . '</li>' : '';
                                $telephone = !empty($loc['phone']) ? '<li class="location-phone"><a href="tel:' . $loc['phone'] . '">' . $loc['phone'] . '</a></li>' : '';
                                $view_more = ($show_links == true) ? '<a href="' . $loc['link'] . '">View More</a>' : '';
                                $reservations = !empty($loc['opentable_id']) ? '<a href="https://www.opentable.com/single.aspx?rid=' . $loc['opentable_id'] . '&restref=' . $loc['opentable_id'] . '" class="reservations" target="_blank">Reservations</a>' : '';
                                $order_online = !empty($loc['online_ordering']) ? '<a href="' . $loc['online_ordering'] . '" class="order-online">Order Online</a>' : '';
                                $directions = !empty($loc['directions']) ? $loc['directions'] : 'https://www.google.com/maps/place/' . $loc['address'];
                                $list_has_data = true;
                                $group_has_data = true;
                                $group_output .=
                                    '<li>' .
                                        '<ul class="location">' .
                                            '<li class="location-title">' . $title . '</li>' .
                                            $description .
                                            $telephone .
                                            '<li class="location-address"><a href="' . $directions . '" target="_blank">' . $loc['address'] . '</a></li>' .
                                            '<li class="location-links">' .
                                                $view_more .
                                                $reservations .
                                                $order_online .
                                            '</li>' .
                                        '</ul>' .
                                    '</li>';
                            }
                        }
                        else if ($type == 'catering') {
                            if ($status == $loc['status'] || empty($status)) {
                                $list_has_data = true;
                                $group_has_data = true;
                                $group_output .=
                                    '<li>' .
                                        '<ul class="catering">' .
                                            '<li class="catering-title"><a href="' . $loc['catering']['url'] . '" target="_blank">' . $loc['title'] . '</a></li>' .
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

                                // Conditionally add button html
                                $button_html = '<div class="link"><a href="' . $button_link . '" target="' . $button_target . '">' . $button_text . '</a></div>';
                                if (empty($button_link)) $button_html = '';
                
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
                                                <div class="content">' . $content_text . '</div>'
                                                . $button_html . $edit_value . '
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
                }
                if ($list_has_data == false) {
                    $output = '<p><em>No information available at this time.</em></p>';
                }
                $output = '<div class="tfk-list type-' . $type . '">' .  $output . '</div>';
            }
            else if ($data == 'slider') {
                // Generate array
                $acf_group_key = 'Slider';
                $acf_meta_key = 'slide';
                $posts = TFK_Shortcodes::acf_get_posts($acf_group_key, $acf_meta_key, $atts);

                // Add editor permission variable
                $edit_option = current_user_can('edit_pages');
                $edit_value = '';

                // Enqueue slider libraries
                wp_enqueue_style('slick');
                wp_enqueue_script('slick');

                // Set up slider height with padding
                if (empty($width)) $width = 1920;
                if (empty($height)) $height = 1080;
                $width = floatval(preg_replace("/[^0-9]/", "", $width));
                $height = floatval(preg_replace("/[^0-9]/", "", $height));
                $padding = (($height / $width) * 100) . "%";

                // Loop through each post group
                $group_output = '';
                foreach($posts as $field_group_key => $field_group) {
                    $slides = $posts[$field_group_key]['slider'];

                    // Add edit button for admin users
                    if ($edit_option == true) $edit_value = '<a class="edit" href="/wp-admin/post.php?post=' . $posts[$field_group_key]['post_id'] . '&action=edit">Edit <span class="dashicons dashicons-edit"></span></a>';
                    
                    // Loop through each slide
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
                            $image_alt_string = '';
                            $title_string = '';
                            $subtitle_string = '';
                            $text_string = '';
                            $button_string = '';
                            if (!empty($content['image'])) $image_string = $content['image']['url'];
                            if (!empty($content['image'])) $image_alt_string = $content['image']['alt'];
                            if (!empty($content['title'])) $title_string = '<h1>' . $content['title'] . '</h1>';
                            if (!empty($content['subtitle'])) $subtitle_string = '<h2>' . $content['subtitle'] . '</h2>';
                            if (!empty($content['text'])) $text_string = '<p>' . $content['text'] . '</p>';
                            if (!empty($button['link'])) $button_string = '<a href="' . $button['link'] . '" target="' . $button['target'] . '">' . $button['text'] . '</a>';
                            $group_output .= '
                                <div class="tfk-slide" style="background-image: url(' . $image_string . ')" role="img" aria-label="' . $image_alt_string . '">
                                    <div class="item">
                                        <div class="content">
                                            ' . $title_string . '
                                            ' . $subtitle_string . '
                                            ' . $text_string . '
                                            ' . $button_string . '
                                            ' . $edit_value . '
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                    }
                }

                $output = '<div class="tfk-slider">' .  $group_output . '</div>';
            }
            else if ($data == 'catering') {
                // Get catering PDF for single location
                $acf_group_key = 'Catering';
                $acf_meta_key = 'document';
                $posts = TFK_Shortcodes::acf_get_posts($acf_group_key, $acf_meta_key, $atts);
                $output = $posts[0]['catering']['url'];
            }
            else if ($data == 'achecker') {
                $href = 'https://achecker.ca/checker/index.php';
                $id = get_field('achecker');
                if (!empty($id)) $href .= '?uri=referer&gid=WCAG2-AA&id=' . $id;
                $output = '
                    <a class="achecker" href="' . $href . '" target="_blank">
                        <img src="https://achecker.ca/images/icon_W2_aa.jpg" alt="WCAG 2.0 (Level AA)" height="32" width="102" />
                    </a>
                ';
            }
            else if ($data == 'momentfeed') {
                $location_id = get_field('location_id_v1', $post_id);
                $output = $location_id;
            }
            else if ($data == 'menu-pdf-list') {
                // Get catering PDF for single location
                $acf_group_key = 'Menu PDF List';
                $acf_meta_key = 'menu-location';
                $posts = TFK_Shortcodes::acf_get_posts($acf_group_key, $acf_meta_key, $atts);
                $menu_pdf_list = $posts[0]['menu-pdf-list'];

                // Resolve NULL $type variable if not set in shortcode
                if (is_null($type)) $type = 'menu';

                // Add editor permission variable
                $edit_option = current_user_can('edit_pages');
                $edit_value = '';

                // Loop through ACF menu list items
                foreach($menu_pdf_list as $list_item) {
                    $list_item_pages = $list_item['pages'];
                    $menu_pdf_url = $list_item['file']['pdf']['url'];
                    $menu_pdf_type = $list_item['file']['type'];
                    $menu_text = 'Download Menu';

                    // Only check pages if the shortcode type is matching the field type
                    if ($menu_pdf_type == $type) {
                        // Update menu link text
                        if ($menu_pdf_type == 'catering') $menu_text = 'Catering Menu';

                        // Loop through ACF assigned pages
                        foreach($list_item_pages as $page) {
                            $page_assignment_id = url_to_postid($page['page']);

                            // Check if assignment matches current page ID
                            if ($page_assignment_id == get_the_ID()) {
                                if ($edit_option == true) $edit_value = '<a class="edit" href="/wp-admin/post.php?post=' . $list_item['file']['pdf']['uploaded_to'] . '&action=edit">Edit <span class="dashicons dashicons-edit"></span></a>';
                                $output = '<a class="' . $acf_meta_key . '" href="' . $menu_pdf_url . '" target="_blank">' . $menu_text . '</a>' . $edit_value;
                                break 2; // Break both loops
                            }
                        }
                    }
                }
            }
            else {
                // Get general location information
                if (!empty($data) && !empty($type)) $output = get_field($data, $post_id)[$type];
                else $output = 'shortcode not found';
            }

            // Return output value (default empty)
            return $output;
        }

        public function acf_get_posts($acf_group_key, $acf_meta_key, $atts = null) {
            $filter = $atts['filter'];
            $groups = acf_get_field_groups(array('post_id' => get_the_ID()));
            $acf_group_key_exists = strpos(json_encode($groups), $acf_group_key) > 0;
            $acf_meta_key_exists = !empty(get_field($acf_meta_key));

            // Limit query to a single post if current page has a $acf_meta_key
            if ($acf_group_key_exists == true) $post__in = array(get_the_ID());

            // Allow multiple post id's
            if (isset($atts['id'])) $post__in = preg_split('/, ?/', $atts['id']);

            // Explode filter attribute if not empty
            if (!empty($filter)) $filter = explode(":", $filter);

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
                // Add general location info to array
                if ($acf_meta_key == 'general' || $acf_meta_key == 'event' || $acf_meta_key == 'document') {
                    // Check if post filter value exists (or shortcode filter attribute does not exist)
                    if (!empty(get_field('general', $post->ID)[$filter[0]]) || empty($filter[0])) {
                        // Check if post filter value after ':' is matching shortcode (or shortcode filter attribute does not exist)
                        if ((get_field('general', $post->ID)[$filter[0]]) == $filter[1] || empty($filter[1])) {
                            $city = get_field('general', $post->ID)['city'];
                            $state = get_field('general', $post->ID)['state'];
                            $street = get_field('general', $post->ID)['street'];
                            $zip = get_field('general', $post->ID)['zip'];
                            $address = $street . ', ' . $city . ', ' . $state . ' ' . $zip;
                            $latitude = get_field('general', $post->ID)['latitude'];
                            $longitude = get_field('general', $post->ID)['longitude'];
                            $geo = array($latitude, $longitude);
                            $directions = get_field('general', $post->ID)['directions'];
                            $item['post_id'] = $post->ID;
                            $item['title'] = get_the_title($post->ID);
                            $item['link'] = get_permalink($post->ID);
                            $item['status'] = get_field('general', $post->ID)['status'];
                            $item['description'] = get_field('general', $post->ID)['description'];
                            $item['opentable_id'] = get_field('general', $post->ID)['opentable_id'];
                            $item['online_ordering'] = get_field('general', $post->ID)['online_ordering'];
                            $item['phone'] = get_field('general', $post->ID)['phone'];
                            $item['city'] = $city;
                            $item['state'] = $state;
                            $item['street'] = $street;
                            $item['zip'] = $zip;
                            $item['address'] = $address;
                            $item['geo'] = $geo;
                            $item['directions'] = $directions;
                        }
                    }
                }

                // Add events to location array if defined in shortcode
                if ($acf_meta_key == 'event') {
                    $item['events'] = get_field('event', $post->ID);
                }

                // Add events to location array if defined in shortcode
                if ($acf_meta_key == 'document') {
                    $item['catering'] = get_field('document', $post->ID);
                }

                // Add events to location array if defined in shortcode
                if ($acf_meta_key == 'slide') {
                    $item['post_id'] = $post->ID;
                    $item['slider'] = get_field('slide', $post->ID);
                }
                
                if ($acf_meta_key == 'menu-location') {
                    $item['menu-pdf-list'] = get_field('menu-location', $post->ID);
                }
                
                // Only add item if not empty
                if (!empty($item)) array_push($posts, $item);
            }
            return $posts;
        }
        public function get_date_status($date_start, $date_end) {
            // Get date parameter for override functionality
            date_default_timezone_set('America/Phoenix');
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