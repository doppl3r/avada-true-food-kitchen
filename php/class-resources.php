<?php
    class TFK_Resources {
        public function __construct() {
            if (!is_admin()) {
                // Register scripts and stylesheets
                wp_register_script('slick', get_stylesheet_directory_uri() . '/js/slick.js', array(), time());
                wp_register_script('leaflet', get_stylesheet_directory_uri() . '/js/leaflet.js', array(), time());
                wp_register_script('leaflet-tfk', get_stylesheet_directory_uri() . '/js/leaflet-tfk.js', array(), time());
                wp_register_script('scripts-tfk', get_stylesheet_directory_uri() . '/js/scripts-tfk.js', array(), time());
                wp_register_style('slick', get_stylesheet_directory_uri() . '/css/slick.css', array(), time());
                wp_register_style('leaflet', get_stylesheet_directory_uri() . '/css/leaflet.css', array(), time());
		        wp_register_style('leaflet-tfk', get_stylesheet_directory_uri() . '/css/leaflet-tfk.css', array(), time());
		        wp_register_style('styles-tfk', get_stylesheet_directory_uri() . '/css/styles-tfk.css', array(), time());
            }
        }
    }
?>