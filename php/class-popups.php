<?php
    class TFK_Popups {
        public function __construct() {
            if (!is_admin()) {
                // Register scripts and stylesheets
                wp_register_script('cookies', get_stylesheet_directory_uri() . '/js/cookies.js');
                wp_register_script('popup', get_stylesheet_directory_uri() . '/js/popup.js', array( 'jquery' ));
                wp_register_style('popup', get_stylesheet_directory_uri() . '/css/popup.css');

                // Enqueue scripts and stylesheets
                wp_enqueue_script('cookies');
                wp_enqueue_script('popup');
                wp_enqueue_style('popup');

                // Add popup
                add_action('wp_footer', 'TFK_Popups::add_popup');
            }
        }

        public function add_popup() {
            $url = rtrim($_SERVER[REQUEST_URI], '/');
            $url_basename = substr($url, strrpos($url, '/') + 1);
            $dir = get_stylesheet_directory_uri();
            $path = get_stylesheet_directory();
            $content = '';

            // Search folder for popups that match the URL
            $popups = glob($path . '/html/popup-*');
            foreach ($popups as $item) {
                $name = pathinfo($item)['filename'];
                $name = substr($name, strpos($name, '-') + 1);
                if ($name == $url_basename) $content = file_get_contents($item);
            }

            // Set content to global popup if url does not match
            if ($content == '') $content = file_get_contents($dir . '/html/popup-global.html');

            // Render popup with content
            echo '
                <div class="popup-alert">
                    <!-- Edit popup: /wp-content/themes/avada-true-food-kitchen/html/ -->
                    <div class="wrapper">
                        <div class="title">
                            <img src="' . $dir . '/img/tfk-logo.png" alt="True Food Kitchen logo">
                        </div>
                        <div class="content">' . $content . '</div>
                    </div>
                </div>
            ';
        }
    }
?>