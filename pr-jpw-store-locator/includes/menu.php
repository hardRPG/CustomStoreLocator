<?php
// Admin-Menü hinzufügen
add_action('admin_menu', 'jpw_admin_menu');

function jpw_admin_menu() {
    add_menu_page(
        'Store Locator Einstellungen',
        'Store Locator',
        'manage_options',
        'jpw-store-locator-settings',
        'jpw_render_settings_page',
        'dashicons-location-alt'
    );

    add_submenu_page(
        'jpw-store-locator-settings',
        'Marker verwalten',
        'Marker',
        'manage_options',
        'jpw-store-locator-markers',
        'jpw_render_marker_page'
    );

    add_submenu_page(
    'jpw-store-locator-settings',
    'CSV Import',
    'CSV Import',
    'manage_options',
    'jpw-store-locator-import',
    'jpw_render_import_page'
);
}

// Settings-Page Render-Funktion auslagern
require_once plugin_dir_path(__FILE__) . 'import.php';
require_once plugin_dir_path(__FILE__) . 'settings.php';
require_once plugin_dir_path(__FILE__) . 'markers.php';
?>