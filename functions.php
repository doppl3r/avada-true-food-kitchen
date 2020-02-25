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