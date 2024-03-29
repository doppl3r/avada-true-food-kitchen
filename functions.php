<?php
	// Queue child theme information
	function theme_enqueue_styles() { wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('avada-stylesheet')); }
	function avada_lang_setup() { $lang = get_stylesheet_directory() . '/languages'; load_child_theme_textdomain( 'Avada', $lang ); }
	add_action('wp_enqueue_scripts', 'theme_enqueue_styles');
	add_action('after_setup_theme', 'avada_lang_setup');

	// Initialize shortcode object
	require_once 'php/class-shortcodes.php';
	$tfk_shortcodes = new TFK_Shortcodes();

	// Initialize resources object
	require_once 'php/class-resources.php';
	$tfk_resources = new TFK_Resources();

	// Add ACF popup library
	require_once 'php/class-acf-popup.php';
	$tfk_popups = new TFK_ACF_Popup();

	// Update navigation with online ordering and reservations
	require_once 'php/class-inline-scripts.php';
	$tfk_scripts = new TFK_Inline_Scripts();

	// Disable Yoast schema for Pelorus SEO team - 07/02/2020
	function disable_yoast_schema_data($data){ $data = array(); return $data; }
	add_filter('wpseo_json_ld_output', 'disable_yoast_schema_data', 10, 1);