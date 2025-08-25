<?php
function jpw_render_import_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "jpw_markers";

    $logs = []; // Logs für Debugging
    $log_file_url = ''; // Pfad für spätere Ausgabe

    if (isset($_POST['jpw_import_nonce']) && wp_verify_nonce($_POST['jpw_import_nonce'], 'jpw_import_csv')) {

        $field_map = array(
            'title'       => strtolower(trim(sanitize_text_field($_POST['field_title']))),
            'address'     => strtolower(trim(sanitize_text_field($_POST['field_address']))),
            'lat'         => strtolower(trim(sanitize_text_field($_POST['field_lat']))),
            'lng'         => strtolower(trim(sanitize_text_field($_POST['field_lng']))),
            'description' => strtolower(trim(sanitize_text_field($_POST['field_description']))),
        );

        $delimiter = sanitize_text_field($_POST['csv_delimiter']);
        $decimal = sanitize_text_field($_POST['decimal_separator']);

        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            if ($handle !== false) {
                $header = fgetcsv($handle, 0, $delimiter);

                // Header bereinigen (Leerzeichen, BOM, Kleinbuchstaben)
                $header = array_map(function ($h) {
                    $h = preg_replace('/[\x00-\x1F\x7F]/u', '', $h); // Steuerzeichen entfernen
                    return strtolower(trim($h));
                }, $header);
                
                $logs[] = "Gefundene Spaltenüberschriften nach Bereinigung: " . implode(", ", $header);

                if ($header === false) {
                    $logs[] = "❌ Fehler: Kopfzeile konnte nicht gelesen werden.";
                } else {
                    $header_map = array_flip($header);
                    $count = 0;
                    $rowNum = 1;

                    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                        $rowNum++;

                        $title = isset($header_map[$field_map['title']]) ? $row[$header_map[$field_map['title']]] : '';
                        $address = isset($header_map[$field_map['address']]) ? $row[$header_map[$field_map['address']]] : '';
                        $lat = isset($header_map[$field_map['lat']]) ? $row[$header_map[$field_map['lat']]] : '';
                        $lng = isset($header_map[$field_map['lng']]) ? $row[$header_map[$field_map['lng']]] : '';
                        $description = isset($header_map[$field_map['description']]) ? $row[$header_map[$field_map['description']]] : '';

                        if ($decimal === ',') {
                            $lat = str_replace(',', '.', $lat);
                            $lng = str_replace(',', '.', $lng);
                        }

                        if (empty($title)) {
                            $logs[] = "⚠️ Zeile $rowNum übersprungen: Kein Titel gefunden.";
                            continue;
                        }
                        if (!is_numeric($lat) || !is_numeric($lng)) {
                            $logs[] = "⚠️ Zeile $rowNum übersprungen: Ungültige Koordinaten (lat=$lat, lng=$lng).";
                            continue;
                        }

                        $inserted = $wpdb->insert(
                            $table_name,
                            array(
                                'title' => sanitize_text_field($title),
                                'address' => sanitize_text_field($address),
                                'lat' => floatval($lat),
                                'lng' => floatval($lng),
                                'description' => sanitize_textarea_field($description),
                            )
                        );

                        if ($inserted) {
                            $logs[] = "✅ Zeile $rowNum importiert: $title ($lat, $lng)";
                            $count++;
                        } else {
                            $logs[] = "❌ Zeile $rowNum konnte nicht gespeichert werden (DB Fehler).";
                        }
                    }

                    $logs[] = "<strong>Fertig: $count Marker importiert.</strong>";
                }
                fclose($handle);
            } else {
                $logs[] = "❌ Fehler: Datei konnte nicht geöffnet werden.";
            }
        } else {
            $logs[] = "❌ Fehler: Keine CSV-Datei hochgeladen.";
        }

        // ---- LOG DATEI SCHREIBEN ----
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/jpw-import-log.txt';
        $log_file_url = $upload_dir['baseurl'] . '/jpw-import-log.txt';

        $log_content = "Import-Protokoll (" . date("Y-m-d H:i:s") . ")\n\n" . implode("\n", $logs);
        file_put_contents($log_file, $log_content);

        // ---- Automatisches Öffnen in neuem Tab ----
        echo "<script>window.open('" . esc_url($log_file_url) . "', '_blank');</script>";
    }
    ?>
    <div class="wrap">
        <h1>CSV Import</h1>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('jpw_import_csv', 'jpw_import_nonce'); ?>

            <h2>CSV Datei hochladen</h2>
            <input type="file" name="csv_file" accept=".csv" required><br><br>

            <h2>Feld-Mapping</h2>
            <table class="form-table">
                <tr>
                    <th>Titel-Feld</th>
                    <td><input type="text" name="field_title" value="title"></td>
                </tr>
                <tr>
                    <th>Adresse-Feld</th>
                    <td><input type="text" name="field_address" value="address"></td>
                </tr>
                <tr>
                    <th>Latitude-Feld</th>
                    <td><input type="text" name="field_lat" value="lat"></td>
                </tr>
                <tr>
                    <th>Longitude-Feld</th>
                    <td><input type="text" name="field_lng" value="lng"></td>
                </tr>
                <tr>
                    <th>Beschreibung-Feld</th>
                    <td><input type="text" name="field_description" value="description"></td>
                </tr>
            </table>

            <h2>Einstellungen</h2>
            <table class="form-table">
                <tr>
                    <th>CSV Trennzeichen</th>
                    <td>
                        <input type="text" name="csv_delimiter" value=";" size="2">
                        <p class="description">Standard: ; (Semikolon). Kann auch , oder | sein.</p>
                    </td>
                </tr>
                <tr>
                    <th>Dezimaltrennzeichen</th>
                    <td>
                        <select name="decimal_separator">
                            <option value="." selected>Punkt (z.B. 54.893232)</option>
                            <option value=",">Komma (z.B. 54,893232)</option>
                        </select>
                    </td>
                </tr>
            </table>

            <?php submit_button('CSV importieren'); ?>
        </form>

        <?php if (!empty($logs)): ?>
            <h2>Import-Protokoll (Kurzfassung)</h2>
            <div style="background:#fff; padding:10px; border:1px solid #ccc; max-height:300px; overflow:auto;">
                <ul>
                    <?php foreach ($logs as $log): ?>
                        <li><?php echo $log; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php if ($log_file_url): ?>
                <p><a href="<?php echo esc_url($log_file_url); ?>" target="_blank">📄 Komplettes Log anzeigen</a></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}
