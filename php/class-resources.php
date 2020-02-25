<?php
    class TFK_Resources {
        public function __construct() {
            if (!is_admin()) {
                // Register scripts and stylesheets
                wp_register_script('leaflet', get_stylesheet_directory_uri() . '/js/leaflet.js');
                wp_register_script('leaflet-tfk', get_stylesheet_directory_uri() . '/js/leaflet-tfk.js');
                wp_register_style('leaflet', get_stylesheet_directory_uri() . '/css/leaflet.css');
		        wp_register_style('leaflet-tfk', get_stylesheet_directory_uri() . '/css/leaflet-tfk.css');
            }
        }
    }
?>