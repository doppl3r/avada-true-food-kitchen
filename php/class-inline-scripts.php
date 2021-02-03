<?php
    class TFK_Inline_Scripts {
        public function __construct() {
            if (!is_admin()) {
                // Add inline script
                add_action('avada_before_header_wrapper', 'TFK_Inline_Scripts::add_header_scripts');
                add_action('wp_footer', 'TFK_Inline_Scripts::add_inline_scripts');
            }
        }

        public function add_header_scripts() {
            echo '<div class="UsableNetAssistive"><a href="#" class="UsableNetAssistive" onclick="return enableUsableNetAssistive()">Enable Accessibility</a></div>';
        }

        public function add_inline_scripts() {
            $content = '';
            $comment = 'Added via: /wp-content/themes/avada-true-food-kitchen/php/class-inline-scripts.php';
            $general = get_field('general');
            $online_ordering = $general['online_ordering'];
            $opentable_id = $general['opentable_id'];
            $opentable_link = 'https://www.opentable.com/single.aspx?rid=' . $opentable_id . '&restref=' . $opentable_id;

            // Online ordering link update
            if (!empty($online_ordering)) {
                $content .= '
                    jQuery(document).ready(function() {
                        //var oldLink = jQuery(\'[href*="order-online"]\');
                        var oldLink = jQuery(\'[href*="order-online"], [href*="order.truefoodkitchen.com"]\');
                        var newLink = \'' . $online_ordering . '\';
                        oldLink.attr(\'href\', newLink);
                        oldLink.attr(\'comment\', \'' . $comment . '\');
                    });
                ';
            }

            // Open table link updates
            if (!empty($opentable_id)) {
                $content .= '
                    jQuery(document).ready(function() {
                        var oldLink = jQuery(\'[href*="reservation"]\');
                        var newLink = \'' . $opentable_link . '\';
                        oldLink.attr(\'href\', newLink);
                        oldLink.attr(\'comment\', \'' . $comment . '\');
                    });
                ';
            }

            // Add online ordering link to about section
            if (!empty($online_ordering)) {
                $content .= '
                    jQuery(document).ready(function() {
                        var links = jQuery(\'.location-about .links\');
                        links.append(\'<li><a href="' . $online_ordering . '" comment="' . $comment . '">Order Now</a></li>\');
                    });
                ';
            }

            // Add online ordering link to about section
            if (!empty($opentable_id)) {
                $content .= '
                    jQuery(document).ready(function() {
                        var links = jQuery(\'.location-about .links\');
                        links.append(\'<li><a href="' . $opentable_link . '" comment="' . $comment . '">Make A Reservation</a></li>\');
                    });
                ';
            }

            // Render inline script with content
            echo '<script>' . $content . '</script>';
        }
    }
?>