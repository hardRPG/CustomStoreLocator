<?php
function jpw_render_marker_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "jpw_markers";

    // ---- Einzelnen Marker löschen ----
    if (isset($_GET['delete_marker']) && check_admin_referer('jpw_delete_marker_' . intval($_GET['delete_marker']))) {
        $wpdb->delete($table_name, array('id' => intval($_GET['delete_marker'])));
        echo '<div class="updated"><p>Marker gelöscht!</p></div>';
    }

    // ---- Alle Marker löschen ----
    if (isset($_POST['jpw_delete_all']) && wp_verify_nonce($_POST['jpw_delete_all_nonce'], 'jpw_delete_all_markers')) {
        $wpdb->query("TRUNCATE TABLE $table_name");
        echo '<div class="updated"><p>Alle Marker wurden gelöscht!</p></div>';
    }

    // ---- Marker speichern ----
    if (isset($_POST['jpw_marker_nonce']) && wp_verify_nonce($_POST['jpw_marker_nonce'], 'jpw_save_marker')) {
        $wpdb->insert(
            $table_name,
            array(
                'title'       => sanitize_text_field($_POST['title']),
                'address'     => sanitize_text_field($_POST['address']),
                'lat'         => floatval($_POST['lat']),
                'lng'         => floatval($_POST['lng']),
                'description' => sanitize_textarea_field($_POST['description']),
            )
        );
        echo '<div class="updated"><p>Marker hinzugefügt!</p></div>';
    }

    // ---- Marker abrufen ----
    $markers = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h1>Marker verwalten</h1>

        <h2>Neuen Marker hinzufügen</h2>
        <form method="post">
            <?php wp_nonce_field('jpw_save_marker', 'jpw_marker_nonce'); ?>
            <table class="form-table">
                <tr><th>Titel</th><td><input type="text" name="title" required></td></tr>
                <tr><th>Adresse</th><td><input type="text" name="address"></td></tr>
                <tr><th>Latitude</th><td><input type="text" name="lat" required></td></tr>
                <tr><th>Longitude</th><td><input type="text" name="lng" required></td></tr>
                <tr><th>Beschreibung</th><td><textarea name="description"></textarea></td></tr>
            </table>
            <?php submit_button('Marker speichern'); ?>
        </form>

        <h2>Bestehende Marker</h2>
        <?php if (!empty($markers)) : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titel</th>
                        <th>Adresse</th>
                        <th>Lat</th>
                        <th>Lng</th>
                        <th>Beschreibung</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($markers as $marker): ?>
                    <tr>
                        <td><?php echo $marker->id; ?></td>
                        <td><?php echo esc_html($marker->title); ?></td>
                        <td><?php echo esc_html($marker->address); ?></td>
                        <td><?php echo esc_html($marker->lat); ?></td>
                        <td><?php echo esc_html($marker->lng); ?></td>
                        <td><?php echo esc_html($marker->description); ?></td>
                        <td>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=jpw-store-locator-markers&delete_marker=' . $marker->id), 'jpw_delete_marker_' . $marker->id); ?>" 
                               onclick="return confirm('Diesen Marker wirklich löschen?');"
                               class="button button-small">Löschen</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <form method="post" style="margin-top:20px;">
                <?php wp_nonce_field('jpw_delete_all_markers', 'jpw_delete_all_nonce'); ?>
                <?php submit_button('Alle Marker löschen', 'delete', 'jpw_delete_all', false, ['onclick' => "return confirm('Wirklich ALLE Marker löschen?')"]); ?>
            </form>
        <?php else: ?>
            <p>Keine Marker vorhanden.</p>
        <?php endif; ?>
    </div>
    <?php
}
