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
                $dir = get_stylesheet_directory_uri() . '/';

                // Get posts if ACF key exits
                $locations_array = TFK_Shortcodes::get_locations();

                // Send array to the front end
                wp_localize_script('leaflet-tfk', 'locations', $locations_array);
                wp_localize_script('leaflet-tfk', 'path', $dir);

                // Render leaflet map HTML
                $output .= '<div id="leaflet-map" ' . $style . '></div>';
            }
            else if ($data == 'lists' || $data == 'list') {

            }

            // Return output value (default empty)
            return $output;
        }

        public function get_locations() {
            // Set $post__in array if current page is a child location
            $row = 'general';
            if (!empty(get_field($row))) $post__in = array(get_the_ID());

            // Get posts if ACF $row key exits
            $locations = get_posts(array(
                'numberposts'	=> -1,
                'post_type'		=> 'page',
                'meta_key'		=> $row,
                'post__in'      => $post__in
            ));

            // Generate JSON for Javascript object
            $locations_array = array();
            foreach($locations as $index => $location) {
                $title = get_the_title($location->ID);
                $link = get_permalink($location->ID);
                $status = get_field($row, $location->ID)['status'];
                $phone = get_field($row, $location->ID)['phone'];
                $city = get_field($row, $location->ID)['city'];
                $state = get_field($row, $location->ID)['state'];
                $street = get_field($row, $location->ID)['street'];
                $zip = get_field($row, $location->ID)['zip'];
                $latitude = get_field($row, $location->ID)['latitude'];
                $longitude = get_field($row, $location->ID)['longitude'];
                $address = $street . ', ' . $city . ', ' . $state . ' ' . $zip;
                $geo = array($latitude, $longitude);

                $locations_array[$index]['title'] = $title;
                $locations_array[$index]['link'] = $link;
                $locations_array[$index]['status'] = $status;
                $locations_array[$index]['phone'] = $status;
                $locations_array[$index]['city'] = $city;
                $locations_array[$index]['state'] = $state;
                $locations_array[$index]['street'] = $street;
                $locations_array[$index]['zip'] = $zip;
                $locations_array[$index]['address'] = $address;
                $locations_array[$index]['geo'] = $geo;
            }
            return $locations_array;
        }
    }
?>