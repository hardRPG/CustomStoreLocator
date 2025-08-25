<?php
 
/**
* Plugin Name: PR-JPW-Store-Locator
* Description: Sehr guter Store Locator und besser als WP Go Maps
* Author: Jan Weyrich
* Version: 1.0
*/

define( 'JPW_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );



function jpw_enqueue_google_maps() {
    $api_key = get_option('jpw_google_maps_api_key', '');
    if (!empty($api_key)) {
        wp_enqueue_script(
            'google-maps',
            'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&callback=initMap&libraries=maps,marker',
            array(),
            null,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'jpw_enqueue_google_maps');


//Marker Datenbank
register_activation_hook(__FILE__, 'jpw_create_marker_table');

function jpw_create_marker_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "jpw_markers";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(200) NOT NULL,
        address varchar(255) DEFAULT '' NOT NULL,
        lat decimal(10,6) NOT NULL,
        lng decimal(10,6) NOT NULL,
        description text DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


//_______________________


// Menü laden
require_once JPW_PLUGIN_DIR_PATH . 'includes/menu.php';
require JPW_PLUGIN_DIR_PATH . "includes/main-render.php";

?>