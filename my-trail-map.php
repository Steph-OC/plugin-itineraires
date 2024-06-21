<?php
/*
Plugin Name: My Trail Map
Description: Plugin custom pour afficher des cartes GPX.
Version: 1.0
Author: Stéphanie Quibel
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Inclure les fichiers nécessaires
require_once plugin_dir_path(__FILE__) . 'includes/class-my-trail-map.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-my-trail-map-admin.php';

// Autoriser les fichiers GPX
function my_trail_map_mime_types($mimes)
{
    $mimes['gpx'] = 'application/gpx+xml';
    return $mimes;
}
add_filter('upload_mimes', 'my_trail_map_mime_types');

// Initialiser le plugin
function my_trail_map_init()
{
    $plugin = new My_Trail_Map();
    $plugin->run();

    if (is_admin()) {
        $admin = new My_Trail_Map_Admin();
        $admin->run();
    }
}
add_action('plugins_loaded', 'my_trail_map_init');
