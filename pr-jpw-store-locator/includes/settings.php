<?php
// Render der Settings-Seite
function jpw_render_settings_page() {
    // Speichern, wenn Formular abgeschickt wurde
    if (isset($_POST['jpw_settings_nonce']) && wp_verify_nonce($_POST['jpw_settings_nonce'], 'jpw_save_settings')) {
        
        // API Key speichern
        if (isset($_POST['jpw_api_key'])) {
            update_option('jpw_google_maps_api_key', sanitize_text_field($_POST['jpw_api_key']));
        }

        // Beispiel: Custom Marker Icon
        if (isset($_POST['jpw_marker_icon'])) {
            update_option('jpw_marker_icon', esc_url_raw($_POST['jpw_marker_icon']));
        }

        echo '<div class="updated"><p>Einstellungen gespeichert!</p></div>';
    }

    // Werte laden
    $saved_key   = get_option('jpw_google_maps_api_key', '');
    $marker_icon = get_option('jpw_marker_icon', '');
    ?>
    <div class="wrap">
        <h1>Store Locator Einstellungen</h1>
        <form method="post">
            <?php wp_nonce_field('jpw_save_settings', 'jpw_settings_nonce'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Google Maps API Key</th>
                    <td>
                        <input type="text" name="jpw_api_key" 
                               value="<?php echo esc_attr($saved_key); ?>" size="50" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Marker Icon URL</th>
                    <td>
                        <input type="text" name="jpw_marker_icon" 
                               value="<?php echo esc_attr($marker_icon); ?>" size="50" />
                        <p class="description">Optional: eigene Marker-Grafik als URL (PNG/SVG).</p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

?>