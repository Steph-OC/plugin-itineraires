<?php
// Si le fichier est appelé directement, quitter
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Supprimer les options de la base de données
delete_option('trail_map_settings');
delete_option('trail_map_button_color');
delete_option('trail_map_button_text_color');
delete_option('trail_map_button_hover_color');
delete_option('trail_map_button_focus_color');
delete_option('trail_map_gpx_files');
delete_option('trail_map_show_all_button');
